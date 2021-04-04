<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class GameResult
 * @property int $id
 * @property int $game_id
 * @property int $team_id
 * @property int $score
 * @property BelongsTo|Game $game
 * @property BelongsTo|Team $team
 * @package App\Models
 */
class GameResult extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'team_id',
        'score',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
