import React, { useState } from 'react';
import {
    faBoxOpen,
    // faCloudDownloadAlt,
    faEllipsisH,
    faLock,
    faTrashAlt,
    faUnlock,
} from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import DropdownMenu, { DropdownButtonRow } from '@/components/elements/DropdownMenu';
// import getSnapshotDownloadUrl from '@/api/server/snapshots/getSnapshotDownloadUrl';
import useFlash from '@/plugins/useFlash';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import deleteSnapshot from '@/api/server/snapshots/deleteSnapshot';
import Can from '@/components/elements/Can';
import tw from 'twin.macro';
import getServerSnapshots from '@/api/swr/getServerSnapshots';
import { ServerSnapshot } from '@/api/server/types';
import { ServerContext } from '@/state/server';
// import Input from '@/components/elements/Input';
import { restoreServerSnapshot } from '@/api/server/snapshots';
import http, { httpErrorToHuman } from '@/api/http';
import { Dialog } from '@/components/elements/dialog';

interface Props {
    snapshot: ServerSnapshot;
}

export default ({ snapshot }: Props) => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const setServerFromState = ServerContext.useStoreActions((actions) => actions.server.setServerFromState);
    const [modal, setModal] = useState('');
    const [loading, setLoading] = useState(false);

    // const [truncate, setTruncate] = useState(false);
    const [truncate] = useState(false);

    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const { mutate } = getServerSnapshots();

    // const doDownload = () => {
    //     setLoading(true);
    //     clearFlashes('snapshots');
    //     getSnapshotDownloadUrl(uuid, snapshot.uuid)
    //         .then((url) => {
    //             // @ts-expect-error this is valid
    //             window.location = url;
    //         })
    //         .catch((error) => {
    //             console.error(error);
    //             clearAndAddHttpError({ key: 'snapshots', error });
    //         })
    //         .then(() => setLoading(false));
    // };

    const doDeletion = () => {
        setLoading(true);
        clearFlashes('snapshots');
        deleteSnapshot(uuid, snapshot.uuid)
            .then(() =>
                mutate(
                    (data) => ({
                        ...data,
                        items: data.items.filter((b) => b.uuid !== snapshot.uuid),
                        snapshotCount: data.snapshotCount - 1,
                    }),
                    false
                )
            )
            .catch((error) => {
                console.error(error);
                clearAndAddHttpError({ key: 'snapshots', error });
                setLoading(false);
                setModal('');
            });
    };

    const doRestorationAction = () => {
        setLoading(true);
        clearFlashes('snapshots');
        restoreServerSnapshot(uuid, snapshot.uuid, truncate)
            .then(() =>
                setServerFromState((s) => ({
                    ...s,
                    status: 'restoring_snapshot',
                }))
            )
            .catch((error) => {
                console.error(error);
                clearAndAddHttpError({ key: 'snapshots', error });
            })
            .then(() => setLoading(false))
            .then(() => setModal(''));
    };

    const onLockToggle = () => {
        if (snapshot.isLocked && modal !== 'unlock') {
            return setModal('unlock');
        }

        http.post(`/api/client/servers/${uuid}/snapshots/${snapshot.uuid}/lock`)
            .then(() =>
                mutate(
                    (data) => ({
                        ...data,
                        items: data.items.map((b) =>
                            b.uuid !== snapshot.uuid
                                ? b
                                : {
                                      ...b,
                                      isLocked: !b.isLocked,
                                  }
                        ),
                    }),
                    false
                )
            )
            .catch((error) => alert(httpErrorToHuman(error)))
            .then(() => setModal(''));
    };

    return (
        <>
            <Dialog.Confirm
                open={modal === 'unlock'}
                onClose={() => setModal('')}
                title={`Unlock "${snapshot.name}"`}
                onConfirmed={onLockToggle}
            >
                This snapshot will no longer be protected from automated or accidental deletions.
            </Dialog.Confirm>
            <Dialog.Confirm
                open={modal === 'restore'}
                onClose={() => setModal('')}
                confirm={'Restore'}
                title={`Restore "${snapshot.name}"`}
                onConfirmed={() => doRestorationAction()}
            >
                <p>
                    Your server will be stopped. You will not be able to control the power state, access the file
                    manager, or create additional snapshots until completed.
                </p>
                {/* <p css={tw`mt-4 -mb-2 bg-gray-700 p-3 rounded`}>
                    <label htmlFor={'restore_truncate'} css={tw`text-base flex items-center cursor-pointer`}>
                        <Input
                            type={'checkbox'}
                            css={tw`text-red-500! w-5! h-5! mr-2`}
                            id={'restore_truncate'}
                            value={'true'}
                            checked={truncate}
                            onChange={() => setTruncate((s) => !s)}
                        />
                        Delete all files before restoring snapshot.
                    </label>
                </p> */}
            </Dialog.Confirm>
            <Dialog.Confirm
                title={`Delete "${snapshot.name}"`}
                confirm={'Continue'}
                open={modal === 'delete'}
                onClose={() => setModal('')}
                onConfirmed={doDeletion}
            >
                This is a permanent operation. The snapshot cannot be recovered once deleted.
            </Dialog.Confirm>
            <SpinnerOverlay visible={loading} fixed />
            {snapshot.isSuccessful ? (
                <DropdownMenu
                    renderToggle={(onClick) => (
                        <button
                            onClick={onClick}
                            css={tw`text-gray-200 transition-colors duration-150 hover:text-gray-100 p-2`}
                        >
                            <FontAwesomeIcon icon={faEllipsisH} />
                        </button>
                    )}
                >
                    <div css={tw`text-sm`}>
                        {/* <Can action={'snapshot.download'}>
                            <DropdownButtonRow onClick={doDownload}>
                                <FontAwesomeIcon fixedWidth icon={faCloudDownloadAlt} css={tw`text-xs`} />
                                <span css={tw`ml-2`}>Download</span>
                            </DropdownButtonRow>
                        </Can> */}
                        <Can action={'snapshot.restore'}>
                            <DropdownButtonRow onClick={() => setModal('restore')}>
                                <FontAwesomeIcon fixedWidth icon={faBoxOpen} css={tw`text-xs`} />
                                <span css={tw`ml-2`}>Restore</span>
                            </DropdownButtonRow>
                        </Can>
                        <Can action={'snapshot.delete'}>
                            <>
                                <DropdownButtonRow onClick={onLockToggle}>
                                    <FontAwesomeIcon
                                        fixedWidth
                                        icon={snapshot.isLocked ? faUnlock : faLock}
                                        css={tw`text-xs mr-2`}
                                    />
                                    {snapshot.isLocked ? 'Unlock' : 'Lock'}
                                </DropdownButtonRow>
                                {!snapshot.isLocked && (
                                    <DropdownButtonRow danger onClick={() => setModal('delete')}>
                                        <FontAwesomeIcon fixedWidth icon={faTrashAlt} css={tw`text-xs`} />
                                        <span css={tw`ml-2`}>Delete</span>
                                    </DropdownButtonRow>
                                )}
                            </>
                        </Can>
                    </div>
                </DropdownMenu>
            ) : (
                <button
                    onClick={() => setModal('delete')}
                    css={tw`text-gray-200 transition-colors duration-150 hover:text-gray-100 p-2`}
                >
                    <FontAwesomeIcon icon={faTrashAlt} />
                </button>
            )}
        </>
    );
};
