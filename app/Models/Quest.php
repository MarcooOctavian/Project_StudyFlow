<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quest extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'goal_value',
        'points_reward',
    ];

    public function userProgress(): HasMany
    {
        return $this->hasMany(UserQuestProgress::class);
    }
}
