<?php

namespace Kubectyl\Http\Controllers\Api\Remote\Snapshots;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Kubectyl\Models\Snapshot;
use Kubectyl\Facades\Activity;
use Illuminate\Http\JsonResponse;
use Kubectyl\Exceptions\DisplayException;
use Kubectyl\Http\Controllers\Controller;
use Kubectyl\Extensions\Filesystem\S3Filesystem;
use Kubectyl\Extensions\Snapshots\SnapshotManager;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Kubectyl\Http\Requests\Api\Remote\ReportSnapshotCompleteRequest;

class SnapshotStatusController extends Controller
{
    /**
     * SnapshotStatusController constructor.
     */
    public function __construct(private SnapshotManager $snapshotManager)
    {
    }

    /**
     * Handles updating the state of a snapshot.
     *
     * @throws \Throwable
     */
    public function index(ReportSnapshotCompleteRequest $request, string $snapshot): JsonResponse
    {
        /** @var \Kubectyl\Models\Snapshot $model */
        $model = Snapshot::query()->where('uuid', $snapshot)->firstOrFail();

        if ($model->is_successful) {
            throw new BadRequestHttpException('Cannot update the status of a snapshot that is already marked as completed.');
        }

        $action = $request->boolean('successful') ? 'server:snapshot.complete' : 'server:snapshot.fail';
        $log = Activity::event($action)->subject($model, $model->server)->property('name', $model->name);

        $log->transaction(function () use ($model, $request) {
            $successful = $request->boolean('successful');

            $model->fill([
                'is_successful' => $successful,
                // Change the lock state to unlocked if this was a failed snapshot so that it can be
                // deleted easily. Also does not make sense to have a locked snapshot on the system
                // that is failed.
                'is_locked' => $successful ? $model->is_locked : false,
                'snapcontent' => $successful ? $request->input('snapcontent') : null,
                'bytes' => $successful ? $request->input('size') : 0,
                'completed_at' => CarbonImmutable::now(),
            ])->save();

            // Check if we are using the s3 snapshot adapter. If so, make sure we mark the snapshot as
            // being completed in S3 correctly.
            $adapter = $this->snapshotManager->adapter();
            if ($adapter instanceof S3Filesystem) {
                $this->completeMultipartUpload($model, $adapter, $successful, $request->input('parts'));
            }
        });

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Handles toggling the restoration status of a server. The server status field should be
     * set back to null, even if the restoration failed. This is not an unsolvable state for
     * the server, and the user can keep trying to restore, or just use the reinstall button.
     *
     * The only thing the successful field does is update the entry value for the audit logs
     * table tracking for this restoration.
     *
     * @throws \Throwable
     */
    public function restore(Request $request, string $snapshot): JsonResponse
    {
        /** @var \Kubectyl\Models\Snapshot $model */
        $model = Snapshot::query()->where('uuid', $snapshot)->firstOrFail();

        $model->server->update(['status' => null]);

        Activity::event($request->boolean('successful') ? 'server:snapshot.restore-complete' : 'server.snapshot.restore-failed')
            ->subject($model, $model->server)
            ->property('name', $model->name)
            ->log();

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Marks a multipart upload in a given S3-compatible instance as failed or successful for
     * the given snapshot.
     *
     * @throws \Exception
     * @throws \Kubectyl\Exceptions\DisplayException
     */
    protected function completeMultipartUpload(Snapshot $snapshot, S3Filesystem $adapter, bool $successful, ?array $parts): void
    {
        // This should never really happen, but if it does don't let us fall victim to Amazon's
        // wildly fun error messaging. Just stop the process right here.
        if (empty($snapshot->upload_id)) {
            // A failed snapshot doesn't need to error here, this can happen if the snapshot encounters
            // an error before we even start the upload. AWS gives you tooling to clear these failed
            // multipart uploads as needed too.
            if (!$successful) {
                return;
            }

            throw new DisplayException('Cannot complete snapshot request: no upload_id present on model.');
        }

        $params = [
            'Bucket' => $adapter->getBucket(),
            'Key' => sprintf('%s/%s.tar.gz', $snapshot->server->uuid, $snapshot->uuid),
            'UploadId' => $snapshot->upload_id,
        ];

        $client = $adapter->getClient();
        if (!$successful) {
            $client->execute($client->getCommand('AbortMultipartUpload', $params));

            return;
        }

        // Otherwise send a CompleteMultipartUpload request.
        $params['MultipartUpload'] = [
            'Parts' => [],
        ];

        if (is_null($parts)) {
            $params['MultipartUpload']['Parts'] = $client->execute($client->getCommand('ListParts', $params))['Parts'];
        } else {
            foreach ($parts as $part) {
                $params['MultipartUpload']['Parts'][] = [
                    'ETag' => $part['etag'],
                    'PartNumber' => $part['part_number'],
                ];
            }
        }

        $client->execute($client->getCommand('CompleteMultipartUpload', $params));
    }
}
