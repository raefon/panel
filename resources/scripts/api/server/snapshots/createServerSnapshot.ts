import http from '@/api/http';
import { ServerSnapshot } from '@/api/server/types';
import { rawDataToServerSnapshot } from '@/api/transformers';

interface RequestParameters {
    name?: string;
    ignored?: string;
    isLocked: boolean;
}

export default async (uuid: string, params: RequestParameters): Promise<ServerSnapshot> => {
    const { data } = await http.post(`/api/client/servers/${uuid}/snapshots`, {
        name: params.name,
        ignored: params.ignored,
        is_locked: params.isLocked,
    });

    return rawDataToServerSnapshot(data);
};
