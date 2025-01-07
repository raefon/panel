import useSWR, { ConfigInterface } from 'swr';
import http, { FractalResponseList } from '@/api/http';
import { rawDataToServerRocketVariable } from '@/api/transformers';
import { ServerRocketVariable } from '@/api/server/types';

interface Response {
    invocation: string;
    variables: ServerRocketVariable[];
    dockerImages: Record<string, string>;
}

export default (uuid: string, initialData?: Response | null, config?: ConfigInterface<Response>) =>
    useSWR(
        [uuid, '/startup'],
        async (): Promise<Response> => {
            const { data } = await http.get(`/api/client/servers/${uuid}/startup`);

            const variables = ((data as FractalResponseList).data || []).map(rawDataToServerRocketVariable);

            return {
                variables,
                invocation: data.meta.startup_command,
                dockerImages: data.meta.docker_images || {},
            };
        },
        { initialData: initialData || undefined, errorRetryCount: 3, ...(config || {}) }
    );
