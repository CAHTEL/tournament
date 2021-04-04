<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Game
 * @property int $id
 * @property Collection|GameResult[] $gameResults
 * @package App\Models
 */
class Game extends Model
{
    use HasFactory;

    public function gameResults(): HasMany
    {
        return $this->hasMany(GameResult::class);
    }
}
