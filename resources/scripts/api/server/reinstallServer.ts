import http from '@/api/http';

export default (uuid: string, deleteFiles: boolean): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.post(`/api/client/servers/${uuid}/settings/reinstall`, { delete_files: deleteFiles })
            .then(() => resolve())
            .catch(reject);
    });
};
