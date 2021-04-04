<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Type
 * @property int $id
 * @property string $name
 * @package App\Models
 */
class Type extends Model
{
    use HasFactory;

    public const PLAY_OFF = 'play_off';
    public const BEST_BY_SCORE = 'best_by_score';
}
