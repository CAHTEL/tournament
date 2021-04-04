<?php

namespace App\Models;

use App\Logic\TournamentLogic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class Tournament
 * @property int $id
 * @property Collection|Group[] $groups
 * @package App\Models
 */
class Tournament extends Model
{
    use HasFactory;
    public $config = TournamentLogic::DEFAULT_CONFIG;

    /**
     * @return HasMany
     */
    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }
}
