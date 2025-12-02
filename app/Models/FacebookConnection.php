<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FacebookConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'facebook_id',
        'name',
        'email',
        'avatar',
        'access_token',
    ];

    /**
     * Get the business managers for the Facebook connection.
     */
    public function businessManagers(): BelongsToMany
    {
        return $this->belongsToMany(BusinessManager::class, 'facebook_business_managers')
            ->withTimestamps();
    }

    /**
     * Get the pages for the Facebook connection.
     */
    public function pages(): BelongsToMany
    {
        return $this->belongsToMany(Page::class, 'facebook_pages')
            ->withTimestamps();
    }

    /**
     * Get the ad accounts for the Facebook connection.
     */
    public function adAccounts(): BelongsToMany
    {
        return $this->belongsToMany(AdAccount::class, 'facebook_ad_accounts')
            ->withTimestamps();
    }

    /**
     * Get the pixels for the Facebook connection.
     */
    public function pixels(): BelongsToMany
    {
        return $this->belongsToMany(Pixel::class, 'facebook_pixels')
            ->withTimestamps();
    }
}
