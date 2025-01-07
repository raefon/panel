import React from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faHdd, faEllipsisH, faLock } from '@fortawesome/free-solid-svg-icons';
import { format, formatDistanceToNow } from 'date-fns';
import Spinner from '@/components/elements/Spinner';
import { bytesToString } from '@/lib/formatters';
import Can from '@/components/elements/Can';
import useWebsocketEvent from '@/plugins/useWebsocketEvent';
import SnapshotContextMenu from '@/components/server/snapshots/SnapshotContextMenu';
import tw from 'twin.macro';
import GreyRowBox from '@/components/elements/GreyRowBox';
import getServerSnapshots from '@/api/swr/getServerSnapshots';
import { ServerSnapshot } from '@/api/server/types';
import { SocketEvent } from '@/components/server/events';

interface Props {
    snapshot: ServerSnapshot;
    className?: string;
}

export default ({ snapshot, className }: Props) => {
    const { mutate } = getServerSnapshots();

    useWebsocketEvent(`${SocketEvent.SNAPSHOT_COMPLETED}:${snapshot.uuid}` as SocketEvent, (data) => {
        try {
            const parsed = JSON.parse(data);

            mutate(
                (data) => ({
                    ...data,
                    items: data.items.map((b) =>
                        b.uuid !== snapshot.uuid
                            ? b
                            : {
                                  ...b,
                                  isSuccessful: parsed.is_successful || true,
                                  snapcontent: parsed.snapcontent || '',
                                  bytes: parsed.file_size || 0,
                                  completedAt: new Date(),
                              }
                    ),
                }),
                false
            );
        } catch (e) {
            console.warn(e);
        }
    });

    return (
        <GreyRowBox css={tw`flex-wrap md:flex-nowrap items-center`} className={className}>
            <div css={tw`flex items-center truncate w-full md:flex-1`}>
                <div css={tw`mr-4`}>
                    {snapshot.completedAt !== null ? (
                        snapshot.isLocked ? (
                            <FontAwesomeIcon icon={faLock} css={tw`text-yellow-500`} />
                        ) : (
                            <FontAwesomeIcon icon={faHdd} css={tw`text-neutral-300`} />
                        )
                    ) : (
                        <Spinner size={'small'} />
                    )}
                </div>
                <div css={tw`flex flex-col truncate`}>
                    <div css={tw`flex items-center text-sm mb-1`}>
                        {snapshot.completedAt !== null && !snapshot.isSuccessful && (
                            <span
                                css={tw`bg-red-500 py-px px-2 rounded-full text-white text-xs uppercase border border-red-600 mr-2`}
                            >
                                Failed
                            </span>
                        )}
                        <p css={tw`break-words truncate`}>{snapshot.name}</p>
                        {snapshot.completedAt !== null && snapshot.isSuccessful && (
                            <span css={tw`ml-3 text-neutral-300 text-xs font-extralight hidden sm:inline`}>
                                {bytesToString(snapshot.bytes)}
                            </span>
                        )}
                    </div>
                    <p css={tw`mt-1 md:mt-0 text-xs text-neutral-400 font-mono truncate`}>{snapshot.snapcontent}</p>
                </div>
            </div>
            <div css={tw`flex-1 md:flex-none md:w-48 mt-4 md:mt-0 md:ml-8 md:text-center`}>
                <p title={format(snapshot.createdAt, 'ddd, MMMM do, yyyy HH:mm:ss')} css={tw`text-sm`}>
                    {formatDistanceToNow(snapshot.createdAt, { includeSeconds: true, addSuffix: true })}
                </p>
                <p css={tw`text-2xs text-neutral-500 uppercase mt-1`}>Created</p>
            </div>
            <Can action={['snapshot.download', 'snapshot.restore', 'snapshot.delete']} matchAny>
                <div css={tw`mt-4 md:mt-0 ml-6`} style={{ marginRight: '-0.5rem' }}>
                    {!snapshot.completedAt ? (
                        <div css={tw`p-2 invisible`}>
                            <FontAwesomeIcon icon={faEllipsisH} />
                        </div>
                    ) : (
                        <SnapshotContextMenu snapshot={snapshot} />
                    )}
                </div>
            </Can>
        </GreyRowBox>
    );
};
