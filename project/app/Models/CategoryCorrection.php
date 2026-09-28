<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'type',
        'normalized_description',
        'description',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
