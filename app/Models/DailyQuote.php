<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyQuote extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_text',
        'author',
        'date_displayed',
        'is_active',
    ];

    /**
     * Get the users who currently have this as their daily quote.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'current_quote_id');
    }
}
