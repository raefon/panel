<?php

use Kubectyl\Models\Snapshot;

return [
    // The snapshot driver to use for this Panel instance. All client generated server snapshots
    // will be stored in this location by default. It is possible to change this once snapshots
    // have been made, without losing data.
    'default' => env('APP_SNAPSHOT_DRIVER', Snapshot::ADAPTER_KUBER),

    // This value is used to determine the lifespan of UploadPart presigned urls that kuber
    // uses to upload snapshots to S3 storage.  Value is in minutes, so this would default to an hour.
    'presigned_url_lifespan' => env('SNAPSHOT_PRESIGNED_URL_LIFESPAN', 60),

    // This value defines the maximal size of a single part for the S3 multipart upload during snapshots
    // The maximal part size must be given in bytes. The default value is 5GB.
    // Note that 5GB is the maximum for a single part when using AWS S3.
    'max_part_size' => env('SNAPSHOT_MAX_PART_SIZE', 5 * 1024 * 1024 * 1024),

    // The time to wait before automatically failing a snapshot, time is in minutes and defaults
    // to 6 hours.  To disable this feature, set the value to `0`.
    'prune_age' => env('SNAPSHOT_PRUNE_AGE', 360),

    // Defines the snapshot creation throttle limits for users. In this default example, we allow
    // a user to create two (successful or pending) snapshots per 10 minutes. Even if they delete
    // a snapshot it will be included in the throttle count.
    //
    // Set the period to "0" to disable this throttle. The period is defined in seconds.
    'throttles' => [
        'limit' => env('SNAPSHOT_THROTTLE_LIMIT', 2),
        'period' => env('SNAPSHOT_THROTTLE_PERIOD', 600),
    ],

    'disks' => [
        // There is no configuration for the local disk for Wings. That configuration
        // is determined by the Daemon configuration, and not the Panel.
        'kuber' => [
            'adapter' => Snapshot::ADAPTER_KUBER,
        ],

        // Configuration for storing snapshots in Amazon S3. This uses the same credentials
        // specified in filesystems.php but does include some more specific settings for
        // snapshots, notably bucket, location, and use_accelerate_endpoint.
        's3' => [
            'adapter' => Snapshot::ADAPTER_AWS_S3,

            'region' => env('AWS_DEFAULT_REGION'),
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),

            // The S3 bucket to use for snapshots.
            'bucket' => env('AWS_SNAPSHOTS_BUCKET'),

            // The location within the S3 bucket where snapshots will be stored. Snapshots
            // are stored within a folder using the server's UUID as the name. Each
            // snapshot for that server lives within that folder.
            'prefix' => env('AWS_SNAPSHOTS_BUCKET') ?? '',

            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'use_accelerate_endpoint' => env('AWS_SNAPSHOTS_USE_ACCELERATE', false),

            'storage_class' => env('AWS_SNAPSHOTS_STORAGE_CLASS'),
        ],
    ],
];
