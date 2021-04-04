<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class GroupGame
 * @property int $id
 * @property int $group_id
 * @property int $game_id
 * @property BelongsTo|Group $group
 * @property BelongsTo|Game $game
 * @package App\Models
 */
class GroupGame extends Model
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
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
