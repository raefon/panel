<?php

namespace Kubectyl\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $server_id
 * @property string $uuid
 * @property bool $is_successful
 * @property bool $is_locked
 * @property string $name
 * @property string $disk
 * @property string|null $snapcontent
 * @property int $bytes
 * @property string|null $upload_id
 * @property \Carbon\CarbonImmutable|null $completed_at
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property \Kubectyl\Models\Server $server
 * @property \Kubectyl\Models\AuditLog[] $audits
 */
class Snapshot extends Model
{
    use SoftDeletes;

    public const RESOURCE_NAME = 'snapshot';

    public const ADAPTER_KUBER = 'kuber';
    public const ADAPTER_AWS_S3 = 's3';

    protected $table = 'snapshots';

    protected bool $immutableDates = true;

    protected $casts = [
        'id' => 'int',
        'is_successful' => 'bool',
        'is_locked' => 'bool',
        'bytes' => 'int',
    ];

    protected $dates = [
        'completed_at',
    ];

    protected $attributes = [
        'is_successful' => false,
        'is_locked' => false,
        'snapcontent' => null,
        'bytes' => 0,
        'upload_id' => null,
    ];

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    public static array $validationRules = [
        'server_id' => 'bail|required|numeric|exists:servers,id',
        'uuid' => 'required|uuid',
        'is_successful' => 'boolean',
        'is_locked' => 'boolean',
        'name' => 'required|string',
        'disk' => 'required|string',
        'snapcontent' => 'nullable|string',
        'bytes' => 'numeric',
        'upload_id' => 'nullable|string',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
