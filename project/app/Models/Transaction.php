<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'type',
        'description',
        'date',
        'is_recurring',
        'recurrence_frequency',
        'ai_suggested_category_id',
        'next_occurrence_at',
        'recurrence_parent_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'is_recurring' => 'boolean',
        'next_occurrence_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function aiSuggestedCategory()
    {
        return $this->belongsTo(Category::class, 'ai_suggested_category_id');
    }

    public function recurrenceParent()
    {
        return $this->belongsTo(self::class, 'recurrence_parent_id');
    }

    public function recurringEntries()
    {
        return $this->hasMany(self::class, 'recurrence_parent_id');
    }
}
