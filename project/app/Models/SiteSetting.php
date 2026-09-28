<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'currency_symbol',
        'facebook_url',
        'instagram_url',
        'x_url',
        'linkedin_url',
        'youtube_url',
        'whatsapp_url',
    ];
}
