<?php

namespace Kubectyl\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $uuid
 * @property string $author
 * @property string $name
 * @property string|null $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Illuminate\Database\Eloquent\Collection|\Kubectyl\Models\Server[] $servers
 * @property \Illuminate\Database\Eloquent\Collection|\Kubectyl\Models\Rocket[] $rockets
 */
class Launchpad extends Model
{
    /**
     * The resource name for this model when it is transformed into an
     * API representation using fractal.
     */
    public const RESOURCE_NAME = 'launchpad';

    /**
     * The table associated with the model.
     */
    protected $table = 'launchpads';

    /**
     * Fields that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
    ];

    public static array $validationRules = [
        'author' => 'required|string|email',
        'name' => 'required|string|max:191',
        'description' => 'nullable|string',
    ];

    /**
     * Gets all rockets associated with this service.
     */
    public function rockets(): HasMany
    {
        return $this->hasMany(Rocket::class);
    }

    /**
     * Gets all servers associated with this launchpad.
     */
    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }
}
