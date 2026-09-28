<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insight extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'month',
        'summary_text',
        'tip_text',
        'is_bookmarked',
        'generated_at',
        'user_note',
    ];

    protected $casts = [
        'is_bookmarked' => 'boolean',
        'generated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
