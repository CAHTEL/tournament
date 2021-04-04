<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class Group
 * @property int $id
 * @property int $tournament_id
 * @property int $type_id
 * @property int $priority
 * @property BelongsTo|Tournament $tournament
 * @property Collection|GroupTeam[] $groupTeams
 * @property Collection|GroupGame[] $groupGames
 * @property BelongsTo|Type $type
 * @package App\Models
 */
class Group extends Model
{
    use HasFactory;

    protected $fillable = ['tournament_id', 'type_id', 'priority'];

    /**
     * @return BelongsTo
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * @return HasMany
     */
    public function groupTeams(): HasMany
    {
        return $this->hasMany(GroupTeam::class);
    }

    /**
     * @return HasMany
     */
    public function groupGames(): HasMany
    {
        return $this->hasMany(GroupGame::class);
    }

    /**
     * @return BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }
}
