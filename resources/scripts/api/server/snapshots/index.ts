import http from '@/api/http';

export const restoreServerSnapshot = async (uuid: string, snapshot: string, truncate?: boolean): Promise<void> => {
    await http.post(`/api/client/servers/${uuid}/snapshots/${snapshot}/restore`, {
        truncate,
    });
};
