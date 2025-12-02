<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AdAccount;
use App\Models\BusinessManager;
use App\Models\FacebookConnection;
use App\Models\Page;
use App\Models\Pixel;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;

class FacebookAuthController extends Controller
{
    private function configureSocialite()
    {
        $tenant = tenant();

        if (! $tenant->app_id || ! $tenant->app_secret) {
            abort(403, 'Facebook App ID and Secret are not configured for this tenant.');
        }

        config([
            'services.facebook.client_id' => $tenant->app_id,
            'services.facebook.client_secret' => $tenant->app_secret,
            'services.facebook.redirect' => route('tenant.facebook.callback'),
        ]);
    }

    public function redirect()
    {
        $this->configureSocialite();

        $permissions = [
            'email',
            'public_profile',
            // Pages permissions
            'Pages_Messaging',
            'pages_manage_engagement',
            'pages_manage_ads',
            'pages_read_engagement',
            'pages_show_list',
            'pages_read_user_content',
            'pages_manage_posts',
            'pages_manage_metadata',
            // Business & Ads permissions
            'business_management',
            'ads_management',
            'ads_read',
            // Other permissions
            'publish_video',
            'leads_retrieval',
        ];

        return Socialite::driver('facebook')
            ->scopes($permissions)
            ->redirect();
    }

    public function callback()
    {
        $this->configureSocialite();

        try {
            $user = Socialite::driver('facebook')->user();
        } catch (\Exception $e) {
            return redirect()->route('filament.tenant.pages.dashboard')
                ->with('error', 'Facebook authentication failed: ' . $e->getMessage());
        }

        $accessToken = $user->token;

        // Fetch Assets (BM, Pages, Ad Accounts, Pixels)
        $assets = $this->fetchFacebookAssets($accessToken);

        // Update or Create Connection
        $connection = FacebookConnection::updateOrCreate(
            ['facebook_id' => $user->getId()],
            [
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'avatar' => $user->getAvatar(),
                'access_token' => $accessToken,
            ]
        );

        // Store assets in separate tables with many-to-many relationships
        $this->syncAssets($connection, $assets);

        return redirect()->route('filament.tenant.resources.facebook-connections.index')
            ->with('success', 'Facebook account connected and assets fetched successfully.');
    }

    private function fetchFacebookAssets($accessToken)
    {
        $graphUrl = 'https://graph.facebook.com/v18.0';
        $assets = [
            'businesses' => [],
            'pages' => [],
            'ad_accounts' => [],
        ];

        // 1. Fetch User's Businesses (Business Managers)
        // permissions: business_management
        $response = Http::get("$graphUrl/me/businesses", [
            'access_token' => $accessToken,
            'fields' => 'id,name,link',
            'limit' => 100,
        ]);

        if ($response->successful()) {
            $assets['businesses'] = $response->json()['data'] ?? [];
            
            // Fetch details for each business if needed (like pixels, owned ad accounts)
            foreach ($assets['businesses'] as &$business) {
                 // Fetch Pixels owned by this business
                 $pixelsResponse = Http::get("$graphUrl/{$business['id']}/adspixels", [
                     'access_token' => $accessToken,
                     'fields' => 'id,name',
                 ]);
                 if ($pixelsResponse->successful()) {
                     $business['pixels'] = $pixelsResponse->json()['data'] ?? [];
                 }
            }
        }

        // 2. Fetch Pages
        // permissions: pages_show_list
        $response = Http::get("$graphUrl/me/accounts", [
            'access_token' => $accessToken,
            'fields' => 'id,name,access_token,category,link',
            'limit' => 100,
        ]);

        if ($response->successful()) {
            $assets['pages'] = $response->json()['data'] ?? [];
        }

        // 3. Fetch Ad Accounts
        // permissions: ads_read
        $response = Http::get("$graphUrl/me/adaccounts", [
            'access_token' => $accessToken,
            'fields' => 'id,name,account_id,currency,account_status',
            'limit' => 100,
        ]);

        if ($response->successful()) {
            $assets['ad_accounts'] = $response->json()['data'] ?? [];
        }

        return $assets;
    }

    private function syncAssets(FacebookConnection $connection, array $assets)
    {
        // Sync Business Managers and their Pixels
        $businessManagerIds = [];
        foreach ($assets['businesses'] ?? [] as $business) {
            $bm = BusinessManager::updateOrCreate(
                ['facebook_id' => $business['id']],
                [
                    'name' => $business['name'] ?? '',
                    'description' => $business['link'] ?? null,
                    'metadata' => $business,
                ]
            );
            $businessManagerIds[] = $bm->id;

            // Sync Pixels for this Business Manager
            if (!empty($business['pixels'])) {
                foreach ($business['pixels'] as $pixelData) {
                    $pixel = Pixel::updateOrCreate(
                        ['facebook_id' => $pixelData['id']],
                        [
                            'name' => $pixelData['name'] ?? '',
                            'code' => $pixelData['id'],
                            'metadata' => $pixelData,
                        ]
                    );
                    // Associate pixel with connection if not already associated
                    if (!$connection->pixels()->where('pixel_id', $pixel->id)->exists()) {
                        $connection->pixels()->attach($pixel->id);
                    }
                }
            }
        }
        $connection->businessManagers()->sync($businessManagerIds);

        // Sync Pages
        $pageIds = [];
        foreach ($assets['pages'] ?? [] as $pageData) {
            $page = Page::updateOrCreate(
                ['facebook_id' => $pageData['id']],
                [
                    'name' => $pageData['name'] ?? '',
                    'category' => $pageData['category'] ?? null,
                    'about' => $pageData['link'] ?? null,
                    'access_token' => $pageData['access_token'] ?? null,
                    'metadata' => $pageData,
                ]
            );
            $pageIds[] = $page->id;
        }
        $connection->pages()->sync($pageIds);

        // Sync Ad Accounts
        $adAccountIds = [];
        foreach ($assets['ad_accounts'] ?? [] as $adAccountData) {
            $adAccount = AdAccount::updateOrCreate(
                ['facebook_id' => $adAccountData['id']],
                [
                    'name' => $adAccountData['name'] ?? '',
                    'account_status' => $adAccountData['account_status'] ?? null,
                    'currency' => $adAccountData['currency'] ?? null,
                    'metadata' => $adAccountData,
                ]
            );
            $adAccountIds[] = $adAccount->id;
        }
        $connection->adAccounts()->sync($adAccountIds);
    }
}
