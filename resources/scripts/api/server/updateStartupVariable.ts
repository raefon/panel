import http from '@/api/http';
import { ServerRocketVariable } from '@/api/server/types';
import { rawDataToServerRocketVariable } from '@/api/transformers';

export default async (uuid: string, key: string, value: string): Promise<[ServerRocketVariable, string]> => {
    const { data } = await http.put(`/api/client/servers/${uuid}/startup/variable`, { key, value });

    return [rawDataToServerRocketVariable(data), data.meta.startup_command];
};
