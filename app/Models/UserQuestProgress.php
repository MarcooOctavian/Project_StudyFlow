<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserQuestProgress extends Model
{
    use HasFactory;

    protected $table = 'user_quest_progress';

    protected $fillable = [
        'user_id',
        'quest_id',
        'current_value',
        'completed',
        'reset_date',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'reset_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class);
    }
}
