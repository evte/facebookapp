<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AdAccount extends Model
{
    protected $fillable = [
        'facebook_id',
        'name',
        'account_status',
        'currency',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the Facebook connections that own this ad account.
     */
    public function facebookConnections(): BelongsToMany
    {
        return $this->belongsToMany(FacebookConnection::class, 'facebook_ad_accounts')
            ->withTimestamps();
    }
}
