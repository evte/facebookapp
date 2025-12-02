<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Page extends Model
{
    protected $fillable = [
        'facebook_id',
        'name',
        'category',
        'about',
        'access_token',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the Facebook connections that own this page.
     */
    public function facebookConnections(): BelongsToMany
    {
        return $this->belongsToMany(FacebookConnection::class, 'facebook_pages')
            ->withTimestamps();
    }
}
