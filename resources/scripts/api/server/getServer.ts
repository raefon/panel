import http, { FractalResponseData, FractalResponseList } from '@/api/http';
import { rawDataToServerAllocation, rawDataToServerRocketVariable } from '@/api/transformers';
import { ServerRocketVariable, ServerStatus } from '@/api/server/types';

export interface Allocation {
    id: number;
    ip: string;
    alias?: string | null;
    port: number;
    notes?: string | null;
    isDefault?: boolean;
}

export interface Server {
    id: string;
    internalId: number | string;
    uuid: string;
    name: string;
    cluster: string;
    isClusterUnderMaintenance: boolean;
    status: ServerStatus;
    sftpDetails: {
        ip: string;
        port: number;
    };
    service: {
        ip: string;
        port: number;
        additional_ports: string[];
    };
    default_port: number;
    additional_ports: string[];
    invocation: string;
    dockerImage: string;
    description: string;
    limits: {
        memory_limit: number;
        disk: number;
        cpu_limit: number;
    };
    rocketFeatures: string[];
    featureLimits: {
        databases: number;
        allocations: number;
        snapshots: number;
    };
    isTransferring: boolean;
    variables: ServerRocketVariable[];
    allocations: Allocation[];
}

export const rawDataToServerObject = ({ attributes: data }: FractalResponseData): Server => ({
    id: data.identifier,
    internalId: data.internal_id,
    uuid: data.uuid,
    name: data.name,
    cluster: data.cluster,
    isClusterUnderMaintenance: data.is_cluster_under_maintenance,
    status: data.status,
    invocation: data.invocation,
    dockerImage: data.docker_image,
    sftpDetails: {
        ip: data.sftp_details.ip,
        port: data.sftp_details.port,
    },
    service: {
        ip: data.service.ip,
        port: data.service.port,
        additional_ports: data.service.additional_ports,
    },
    default_port: data.service.port,
    additional_ports: data.service.additional_ports,
    description: data.description ? (data.description.length > 0 ? data.description : null) : null,
    limits: { ...data.limits },
    rocketFeatures: data.rocket_features || [],
    featureLimits: { ...data.feature_limits },
    isTransferring: data.is_transferring,
    variables: ((data.relationships?.variables as FractalResponseList | undefined)?.data || []).map(
        rawDataToServerRocketVariable
    ),
    allocations: ((data.relationships?.allocations as FractalResponseList | undefined)?.data || []).map(
        rawDataToServerAllocation
    ),
});

export default (uuid: string): Promise<[Server, string[]]> => {
    return new Promise((resolve, reject) => {
        http.get(`/api/client/servers/${uuid}`)
            .then(({ data }) =>
                resolve([
                    rawDataToServerObject(data),
                    // eslint-disable-next-line camelcase
                    data.meta?.is_server_owner ? ['*'] : data.meta?.user_permissions || [],
                ])
            )
            .catch(reject);
    });
};
