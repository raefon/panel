import React from 'react';
import { ServerContext } from '@/state/server';
import ScreenBlock from '@/components/elements/ScreenBlock';
import ServerInstallSvg from '@/assets/images/server_installing.svg';
import ServerErrorSvg from '@/assets/images/server_error.svg';
import ServerRestoreSvg from '@/assets/images/server_restore.svg';

export default () => {
    const status = ServerContext.useStoreState((state) => state.server.data?.status || null);
    const isTransferring = ServerContext.useStoreState((state) => state.server.data?.isTransferring || false);
    const isClusterUnderMaintenance = ServerContext.useStoreState(
        (state) => state.server.data?.isClusterUnderMaintenance || false
    );

    return status === 'installing' || status === 'install_failed' ? (
        <ScreenBlock
            title={'Running Installer'}
            image={ServerInstallSvg}
            message={'Your server should be ready soon, please try again in a few minutes.'}
        />
    ) : status === 'suspended' ? (
        <ScreenBlock
            title={'Server Suspended'}
            image={ServerErrorSvg}
            message={'This server is suspended and cannot be accessed.'}
        />
    ) : isClusterUnderMaintenance ? (
        <ScreenBlock
            title={'Cluster under Maintenance'}
            image={ServerErrorSvg}
            message={'The cluster of this server is currently under maintenance.'}
        />
    ) : (
        <ScreenBlock
            title={isTransferring ? 'Transferring' : 'Restoring from Snapshot'}
            image={ServerRestoreSvg}
            message={
                isTransferring
                    ? 'Your server is being transfered to a new cluster, please check back later.'
                    : 'Your server is currently being restored from a snapshot, please check back in a few minutes.'
            }
        />
    );
};
