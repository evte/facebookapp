<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BusinessManager extends Model
{
    protected $fillable = [
        'facebook_id',
        'name',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the Facebook connections that own this business manager.
     */
    public function facebookConnections(): BelongsToMany
    {
        return $this->belongsToMany(FacebookConnection::class, 'facebook_business_managers')
            ->withTimestamps();
    }
}
