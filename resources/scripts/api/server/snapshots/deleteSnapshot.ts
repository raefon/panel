import http from '@/api/http';

export default (uuid: string, snapshot: string): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.delete(`/api/client/servers/${uuid}/snapshots/${snapshot}`)
            .then(() => resolve())
            .catch(reject);
    });
};
