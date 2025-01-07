import React, { useContext, useEffect, useState } from 'react';
import Spinner from '@/components/elements/Spinner';
import useFlash from '@/plugins/useFlash';
import Can from '@/components/elements/Can';
import CreateSnapshotButton from '@/components/server/snapshots/CreateSnapshotButton';
import FlashMessageRender from '@/components/FlashMessageRender';
import SnapshotRow from '@/components/server/snapshots/SnapshotRow';
import tw from 'twin.macro';
import getServerSnapshots, { Context as ServerSnapshotContext } from '@/api/swr/getServerSnapshots';
import { ServerContext } from '@/state/server';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import Pagination from '@/components/elements/Pagination';

const SnapshotContainer = () => {
    const { page, setPage } = useContext(ServerSnapshotContext);
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const { data: snapshots, error, isValidating } = getServerSnapshots();

    const snapshotLimit = ServerContext.useStoreState((state) => state.server.data!.featureLimits.snapshots);

    useEffect(() => {
        if (!error) {
            clearFlashes('snapshots');

            return;
        }

        clearAndAddHttpError({ error, key: 'snapshots' });
    }, [error]);

    if (!snapshots || (error && isValidating)) {
        return <Spinner size={'large'} centered />;
    }

    return (
        <ServerContentBlock title={'Snapshots'}>
            <FlashMessageRender byKey={'snapshots'} css={tw`mb-4`} />
            <Pagination data={snapshots} onPageSelect={setPage}>
                {({ items }) =>
                    !items.length ? (
                        // Don't show any error messages if the server has no snapshots and the user cannot
                        // create additional ones for the server.
                        !snapshotLimit ? null : (
                            <p css={tw`text-center text-sm text-neutral-300`}>
                                {page > 1
                                    ? "Looks like we've run out of snapshots to show you, try going back a page."
                                    : 'It looks like there are no snapshots currently stored for this server.'}
                            </p>
                        )
                    ) : (
                        items.map((snapshot, index) => (
                            <SnapshotRow
                                key={snapshot.uuid}
                                snapshot={snapshot}
                                css={index > 0 ? tw`mt-2` : undefined}
                            />
                        ))
                    )
                }
            </Pagination>
            {snapshotLimit === 0 && (
                <p css={tw`text-center text-sm text-neutral-300`}>
                    Snapshots cannot be created for this server because the snapshot limit is set to 0.
                </p>
            )}
            <Can action={'snapshot.create'}>
                <div css={tw`mt-6 sm:flex items-center justify-end`}>
                    {snapshotLimit > 0 && snapshots.snapshotCount > 0 && (
                        <p css={tw`text-sm text-neutral-300 mb-4 sm:mr-6 sm:mb-0`}>
                            {snapshots.snapshotCount} of {snapshotLimit} snapshots have been created for this server.
                        </p>
                    )}
                    {snapshotLimit > 0 && snapshotLimit > snapshots.snapshotCount && (
                        <CreateSnapshotButton css={tw`w-full sm:w-auto`} />
                    )}
                </div>
            </Can>
        </ServerContentBlock>
    );
};

export default () => {
    const [page, setPage] = useState<number>(1);
    return (
        <ServerSnapshotContext.Provider value={{ page, setPage }}>
            <SnapshotContainer />
        </ServerSnapshotContext.Provider>
    );
};
