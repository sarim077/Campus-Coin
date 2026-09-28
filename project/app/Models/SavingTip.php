<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingTip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'tip_title',
        'tip_body',
        'potential_savings',
        'status',
        'is_bookmarked',
        'is_ai_generated',
        'user_note',
        'source_template_id',
    ];

    protected $casts = [
        'potential_savings' => 'decimal:2',
        'is_bookmarked' => 'boolean',
        'is_ai_generated' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function sourceTemplate()
    {
        return $this->belongsTo(SavingTipTemplate::class, 'source_template_id');
    }
}
