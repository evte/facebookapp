<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pixel extends Model
{
    protected $fillable = [
        'facebook_id',
        'name',
        'code',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the Facebook connections that own this pixel.
     */
    public function facebookConnections(): BelongsToMany
    {
        return $this->belongsToMany(FacebookConnection::class, 'facebook_pixels')
            ->withTimestamps();
    }
}
