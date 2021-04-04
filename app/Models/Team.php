<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Team
 * @property int $id
 * @property string $name
 * @property Collection|GameResult[] $gamesResult
 * @package App\Models
 */
class Team extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = ['name'];

    /**
     * @return HasMany
     */
    public function gamesResult(): HasMany
    {
        return $this->hasMany(GameResult::class);
    }

    public function game(): HasMany
    {

    }
}
