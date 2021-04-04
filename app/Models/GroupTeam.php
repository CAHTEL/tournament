<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class GroupTeam
 * @property int $id
 * @property int $group_id
 * @property int $team_id
 * @property BelongsTo|Group $group
 * @property BelongsTo|Team $team
 * @package App\Models
 */
class GroupTeam extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * @return BelongsTo
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
