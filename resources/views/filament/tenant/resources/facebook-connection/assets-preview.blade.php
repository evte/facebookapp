<div class="space-y-4">
    @php
        $state = $getRecord()->assets ?? [];
    @endphp

    @if(empty($state))
        <p class="text-gray-500">No assets data available.</p>
    @else
        
        {{-- Business Managers --}}
        <div class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
            <h3 class="font-bold text-lg mb-2">Business Managers ({{ count($state['businesses'] ?? []) }})</h3>
            <ul class="list-disc list-inside space-y-1">
                @forelse($state['businesses'] ?? [] as $bm)
                    <li>
                        <span class="font-medium">{{ $bm['name'] }}</span> (ID: {{ $bm['id'] }})
                        @if(!empty($bm['pixels']))
                             <div class="ml-4 mt-1 text-sm text-gray-600">
                                <strong>Pixels:</strong>
                                @foreach($bm['pixels'] as $pixel)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $pixel['name'] }} ({{ $pixel['id'] }})
                                    </span>
                                @endforeach
                             </div>
                        @endif
                    </li>
                @empty
                    <li class="text-gray-400">None found</li>
                @endforelse
            </ul>
        </div>

        {{-- Pages --}}
        <div class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
            <h3 class="font-bold text-lg mb-2">Pages ({{ count($state['pages'] ?? []) }})</h3>
            <ul class="list-disc list-inside space-y-1">
                @forelse($state['pages'] ?? [] as $page)
                    <li>
                        <span class="font-medium">{{ $page['name'] }}</span> (ID: {{ $page['id'] }}) - {{ $page['category'] ?? 'No Category' }}
                    </li>
                @empty
                    <li class="text-gray-400">None found</li>
                @endforelse
            </ul>
        </div>

        {{-- Ad Accounts --}}
        <div class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
            <h3 class="font-bold text-lg mb-2">Ad Accounts ({{ count($state['ad_accounts'] ?? []) }})</h3>
            <ul class="list-disc list-inside space-y-1">
                @forelse($state['ad_accounts'] ?? [] as $adAccount)
                    <li>
                        <span class="font-medium">{{ $adAccount['name'] ?? 'Unnamed' }}</span> 
                        (ID: {{ $adAccount['account_id'] }}) - 
                        Status: {{ $adAccount['account_status'] ?? 'Unknown' }} -
                        Currency: {{ $adAccount['currency'] ?? 'USD' }}
                    </li>
                @empty
                    <li class="text-gray-400">None found</li>
                @endforelse
            </ul>
        </div>

    @endif
</div>
