import React, { useEffect, useState } from 'react';
import Spinner from '@/components/elements/Spinner';
import { useFlashKey } from '@/plugins/useFlash';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import { ServerContext } from '@/state/server';
import AllocationRow from '@/components/server/network/AllocationRow';
import { Allocation } from '@/api/server/getServer';
import Button from '@/components/elements/Button';
import createServerAllocation from '@/api/server/network/createServerAllocation';
import tw from 'twin.macro';
import Can from '@/components/elements/Can';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import getServerAllocations from '@/api/swr/getServerAllocations';
import isEqual from 'react-fast-compare';
import { useDeepCompareEffect } from '@/plugins/useDeepCompareEffect';

const NetworkContainer = () => {
    const [loading, setLoading] = useState(false);
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const allocationLimit = ServerContext.useStoreState((state) => state.server.data!.featureLimits.allocations);
    const allocations = ServerContext.useStoreState((state) => state.server.data!.allocations, isEqual);
    const setServerFromState = ServerContext.useStoreActions((actions) => actions.server.setServerFromState);

    const server = ServerContext.useStoreState((state) => state.server.data!);
    const additional_ports = ServerContext.useStoreState((state) => state.server.data!.additional_ports);

    const { clearFlashes, clearAndAddHttpError } = useFlashKey('server:network');
    const { data, error, mutate } = getServerAllocations();

    useEffect(() => {
        mutate(allocations);
    }, []);

    useEffect(() => {
        clearAndAddHttpError(error);
    }, [error]);

    useDeepCompareEffect(() => {
        if (!data) return;

        setServerFromState((state) => ({ ...state, allocations: data }));
    }, [data]);

    const onCreateAllocation = () => {
        clearFlashes();

        setLoading(true);
        createServerAllocation(uuid)
            .then((allocation) => {
                setServerFromState((s) => ({
                    ...s,
                    allocations: s.allocations.concat(allocation),
                }));
                return mutate(data?.concat(allocation), false);
            })
            .catch((error) => clearAndAddHttpError(error))
            .then(() => setLoading(false));
    };

    const service: Allocation = {
        id: 0,
        alias: '',
        notes: '',
        isDefault: true,
        ip: server.service.ip || 'not available',
        port: server.service.port,
    };

    return (
        <ServerContentBlock showFlashKey={'server:network'} title={'Network'}>
            {!data || (!additional_ports && data.length === 0) ? (
                <Spinner size={'large'} centered />
            ) : (
                <>
                    {data.length === 0 ? (
                        <>
                            {additional_ports?.map((port) => (
                                <AllocationRow
                                    key={`${server.service.ip}:${port}`}
                                    allocation={{
                                        id: 0,
                                        ip: server.service.ip || 'not available',
                                        port: parseInt(port),
                                    }}
                                    isAllocation={false}
                                />
                            ))}
                            <AllocationRow
                                key={`${server.service.ip}:${server.service.port}`}
                                allocation={service}
                                isAllocation={false}
                            />
                        </>
                    ) : (
                        <>
                            {data.map((allocation) => (
                                <AllocationRow
                                    key={`${allocation.ip}:${allocation.port}`}
                                    allocation={allocation}
                                    isAllocation={true}
                                />
                            ))}
                            {allocationLimit > 0 && (
                                <Can action={'allocation.create'}>
                                    <SpinnerOverlay visible={loading} />
                                    <div css={tw`mt-6 sm:flex items-center justify-end`}>
                                        <p css={tw`text-sm text-neutral-300 mb-4 sm:mr-6 sm:mb-0`}>
                                            You are currently using {data.length} of {allocationLimit} allowed
                                            allocations for this server.
                                        </p>
                                        {allocationLimit > data.length && (
                                            <Button
                                                css={tw`w-full sm:w-auto`}
                                                color={'primary'}
                                                onClick={onCreateAllocation}
                                            >
                                                Create Allocation
                                            </Button>
                                        )}
                                    </div>
                                </Can>
                            )}
                        </>
                    )}
                </>
            )}
        </ServerContentBlock>
    );
};

export default NetworkContainer;
