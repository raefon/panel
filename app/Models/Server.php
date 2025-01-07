<?php

namespace Kubectyl\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Query\JoinClause;
use Znck\Eloquent\Traits\BelongsToThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Kubectyl\Exceptions\Http\Server\ServerStateConflictException;

/**
 * \Kubectyl\Models\Server.
 *
 * @property int $id
 * @property string|null $external_id
 * @property string $uuid
 * @property string $uuidShort
 * @property int $cluster_id
 * @property string $name
 * @property string $description
 * @property string|null $status
 * @property bool $skip_scripts
 * @property int $owner_id
 * @property int $memory_request
 * @property int $memory_limit
 * @property int $disk
 * @property int $cpu_request
 * @property int $cpu_limit
 * @property int $allocation_id
 * @property int $default_port
 * @property array $additional_ports
 * @property int $launchpad_id
 * @property int $rocket_id
 * @property string $startup
 * @property string $image
 * @property int|null $allocation_limit
 * @property int|null $database_limit
 * @property int $snapshot_limit
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $installed_at
 * @property \Illuminate\Database\Eloquent\Collection|\Kubectyl\Models\ActivityLog[] $activity
 * @property int|null $activity_count
 * @property \Kubectyl\Models\Allocation|null $allocation
 * @property \Illuminate\Database\Eloquent\Collection|\Kubectyl\Models\Allocation[] $allocations
 * @property int|null $allocations_count
 * @property \Illuminate\Database\Eloquent\Collection|\Kubectyl\Models\Snapshot[] $snapshots
 * @property int|null $snapshots_count
 * @property \Illuminate\Database\Eloquent\Collection|\Kubectyl\Models\Database[] $databases
 * @property int|null $databases_count
 * @property \Kubectyl\Models\Rocket|null $rocket
 * @property \Illuminate\Database\Eloquent\Collection|\Kubectyl\Models\Mount[] $mounts
 * @property int|null $mounts_count
 * @property \Kubectyl\Models\Launchpad $launchpad
 * @property \Kubectyl\Models\Cluster $cluster
 * @property \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property int|null $notifications_count
 * @property \Illuminate\Database\Eloquent\Collection|\Kubectyl\Models\Schedule[] $schedules
 * @property int|null $schedules_count
 * @property \Illuminate\Database\Eloquent\Collection|\Kubectyl\Models\Subuser[] $subusers
 * @property int|null $subusers_count
 * @property \Kubectyl\Models\ServerTransfer|null $transfer
 * @property \Kubectyl\Models\User $user
 * @property \Illuminate\Database\Eloquent\Collection|\Kubectyl\Models\RocketVariable[] $variables
 * @property int|null $variables_count
 *
 * @method static \Database\Factories\ServerFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Server newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Server newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Server query()
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereAllocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereAllocationLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereSnapshotLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereCpu($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereDatabaseLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereRocketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereIo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereMemory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereLaunchpadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereClusterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereOomDisabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereSkipScripts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereStartup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereSwap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereThreads($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Server whereUuidShort($value)
 *
 * @mixin \Eloquent
 */
class Server extends Model
{
    use BelongsToThrough;
    use Notifiable;

    /**
     * The resource name for this model when it is transformed into an
     * API representation using fractal.
     */
    public const RESOURCE_NAME = 'server';

    public const STATUS_INSTALLING = 'installing';
    public const STATUS_INSTALL_FAILED = 'install_failed';
    public const STATUS_REINSTALL_FAILED = 'reinstall_failed';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_RESTORING_SNAPSHOT = 'restoring_snapshot';

    /**
     * The table associated with the model.
     */
    protected $table = 'servers';

    /**
     * Default values when creating the model. We want to switch to disabling OOM killer
     * on server instances unless the user specifies otherwise in the request.
     */
    protected $attributes = [
        'status' => self::STATUS_INSTALLING,
        'installed_at' => null,
        'node_selectors' => null,
    ];

    /**
     * The default relationships to load for all server models.
     */
    protected $with = ['allocation'];

    /**
     * The attributes that should be mutated to dates.
     */
    protected $dates = [self::CREATED_AT, self::UPDATED_AT, 'deleted_at', 'installed_at'];

    /**
     * Fields that are not mass assignable.
     */
    protected $guarded = ['id', self::CREATED_AT, self::UPDATED_AT, 'deleted_at', 'installed_at'];

    public static array $validationRules = [
        'external_id' => 'sometimes|nullable|string|between:1,191|unique:servers',
        'owner_id' => 'required|integer|exists:users,id',
        'name' => 'required|string|min:1|max:191',
        'cluster_id' => 'required|exists:clusters,id',
        'description' => 'string',
        'status' => 'nullable|string',
        'memory_request' => 'required|numeric|min:128',
        'memory_limit' => 'required|numeric|min:128',
        'cpu_request' => 'required|numeric|min:0',
        'cpu_limit' => 'required|numeric|min:0',
        'disk' => 'required|numeric|min:0',
        'storage_class' => 'nullable|string',
        'allocation_id' => 'required_without:default_port|nullable|bail|unique:servers|exists:allocations,id',
        'default_port' => 'required_without:allocation_id|nullable|numeric|between:1,65535',
        'additional_ports' => 'nullable|array',
        'launchpad_id' => 'required|exists:launchpads,id',
        'rocket_id' => 'required|exists:rockets,id',
        'node_selectors' => 'array|nullable',
        'node_selectors.*' => 'string',
        'startup' => 'required|string',
        'skip_scripts' => 'sometimes|boolean',
        'image' => 'required|string|max:191',
        'database_limit' => 'present|nullable|integer|min:0',
        'allocation_limit' => 'sometimes|nullable|integer|min:0',
        'snapshot_limit' => 'present|nullable|integer|min:0',
    ];

    /**
     * Cast values to correct type.
     */
    protected $casts = [
        'cluster_id' => 'integer',
        'skip_scripts' => 'boolean',
        'owner_id' => 'integer',
        'memory_request' => 'integer',
        'memory_limit' => 'integer',
        'disk' => 'integer',
        'cpu_request' => 'integer',
        'cpu_limit' => 'integer',
        'allocation_id' => 'integer',
        'default_port' => 'integer',
        'additional_ports' => 'array',
        'launchpad_id' => 'integer',
        'rocket_id' => 'integer',
        'node_selectors' => 'array',
        'database_limit' => 'integer',
        'allocation_limit' => 'integer',
        'snapshot_limit' => 'integer',
    ];

    /**
     * Returns the format for server allocations when communicating with the Daemon.
     */
    public function getAllocationMappings(): array
    {
        return $this->allocations->where('cluster_id', $this->cluster_id)->groupBy('ip')->map(function ($item) {
            return $item->pluck('port');
        })->toArray();
    }

    public function isInstalled(): bool
    {
        return $this->status !== self::STATUS_INSTALLING && $this->status !== self::STATUS_INSTALL_FAILED;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * Gets the user who owns the server.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Gets the subusers associated with a server.
     */
    public function subusers(): HasMany
    {
        return $this->hasMany(Subuser::class, 'server_id', 'id');
    }

    /**
     * Gets the default allocation for a server.
     */
    public function allocation(): HasOne
    {
        return $this->hasOne(Allocation::class, 'id', 'allocation_id');
    }

    /**
     * Gets all allocations associated with this server.
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class, 'server_id');
    }

    /**
     * Gets information for the launchpad associated with this server.
     */
    public function launchpad(): BelongsTo
    {
        return $this->belongsTo(Launchpad::class);
    }

    /**
     * Gets information for the rocket associated with this server.
     */
    public function rocket(): HasOne
    {
        return $this->hasOne(Rocket::class, 'id', 'rocket_id');
    }

    /**
     * Gets information for the service variables associated with this server.
     */
    public function variables(): HasMany
    {
        return $this->hasMany(RocketVariable::class, 'rocket_id', 'rocket_id')
            ->select(['rocket_variables.*', 'server_variables.variable_value as server_value'])
            ->leftJoin('server_variables', function (JoinClause $join) {
                // Don't forget to join against the server ID as well since the way we're using this relationship
                // would actually return all the variables and their values for _all_ servers using that rocket,
                // rather than only the server for this model.
                //
                // @see https://github.com/pterodactyl/panel/issues/2250
                $join->on('server_variables.variable_id', 'rocket_variables.id')
                    ->where('server_variables.server_id', $this->id);
            });
    }

    /**
     * Gets information for the cluster associated with this server.
     */
    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class);
    }

    /**
     * Gets information for the tasks associated with this server.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Gets all databases associated with a server.
     */
    public function databases(): HasMany
    {
        return $this->hasMany(Database::class);
    }

    /**
     * Returns the location that a server belongs to.
     *
     * @throws \Exception
     */
    public function location(): \Znck\Eloquent\Relations\BelongsToThrough
    {
        return $this->belongsToThrough(Location::class, Cluster::class);
    }

    /**
     * Returns the associated server transfer.
     */
    public function transfer(): HasOne
    {
        return $this->hasOne(ServerTransfer::class)->whereNull('successful')->orderByDesc('id');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(Snapshot::class);
    }

    /**
     * Returns all mounts that have this server has mounted.
     */
    public function mounts(): HasManyThrough
    {
        return $this->hasManyThrough(Mount::class, MountServer::class, 'server_id', 'id', 'id', 'mount_id');
    }

    /**
     * Returns all of the activity log entries where the server is the subject.
     */
    public function activity(): MorphToMany
    {
        return $this->morphToMany(ActivityLog::class, 'subject', 'activity_log_subjects');
    }

    /**
     * Checks if the server is currently in a user-accessible state. If not, an
     * exception is raised. This should be called whenever something needs to make
     * sure the server is not in a weird state that should block user access.
     *
     * @throws \Kubectyl\Exceptions\Http\Server\ServerStateConflictException
     */
    public function validateCurrentState()
    {
        if (
            $this->isSuspended() ||
            $this->cluster->isUnderMaintenance() ||
            !$this->isInstalled() ||
            $this->status === self::STATUS_RESTORING_SNAPSHOT ||
            !is_null($this->transfer)
        ) {
            throw new ServerStateConflictException($this);
        }
    }

    /**
     * Checks if the server is currently in a transferable state. If not, an
     * exception is raised. This should be called whenever something needs to make
     * sure the server is able to be transferred and is not currently being transferred
     * or installed.
     */
    public function validateTransferState()
    {
        if (
            !$this->isInstalled() ||
            $this->status === self::STATUS_RESTORING_SNAPSHOT ||
            !is_null($this->transfer)
        ) {
            throw new ServerStateConflictException($this);
        }
    }
}
