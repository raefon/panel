import http from '@/api/http';

export default (uuid: string, snapshot: string): Promise<string> => {
    return new Promise((resolve, reject) => {
        http.get(`/api/client/servers/${uuid}/snapshots/${snapshot}/download`)
            .then(({ data }) => resolve(data.attributes.url))
            .catch(reject);
    });
};
