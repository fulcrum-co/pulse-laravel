<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Billing & Credits</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Manage your Pulse Credits, auto top-up settings, and feature access.
        </p>
    </div>

    {{-- Grace Period Warning --}}
    @if($inGracePeriod)
        <div class="mb-6 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 p-4 border border-yellow-200 dark:border-yellow-800">
            <div class="flex">
                <x-icon name="exclamation-triangle" class="h-5 w-5 text-yellow-400" />
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Grace Period Active</h3>
                    <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                        Your last payment failed. Please update your payment method to avoid service interruption.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Tab Navigation --}}
    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            @foreach(['overview' => 'Overview', 'purchase' => 'Purchase Credits', 'auto-topup' => 'Auto Top-Up', 'transactions' => 'Transactions', 'valves' => 'Feature Valves', 'rates' => 'Rate Card'] as $tab => $label)
                <button
                    wire:click="setTab('{{ $tab }}')"
                    class="{{ $activeTab === $tab
                        ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300' }}
                        whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                >
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- Overview Tab --}}
    @if($activeTab === 'overview')
        <div class="space-y-6">
            {{-- Balance Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                {{-- Current Balance --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Balance</p>
                            <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($balance, 0) }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">credits</p>
                        </div>
                        <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                            <x-icon name="credit-card" class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize
                            {{ $pricingTier === 'strategic' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200' :
                               ($pricingTier === 'enterprise' ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-200' :
                               ($pricingTier === 'growth' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200' :
                                'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200')) }}">
                            {{ $pricingTier }} tier
                        </span>
                    </div>
                </div>

                {{-- Burn Rate --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Daily Burn Rate</p>
                            <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($dailyBurnRate, 0) }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">credits/day</p>
                        </div>
                        <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-full">
                            <x-icon name="fire" class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                        </div>
                    </div>
                </div>

                {{-- Forecast --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Depletion Forecast</p>
                            <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">
                                {{ $depletionForecast ?? 'N/A' }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">estimated</p>
                        </div>
                        <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-full">
                            <x-icon name="calendar" class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                </div>

                {{-- Lifetime Used --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Lifetime Used</p>
                            <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($lifetimeUsed, 0) }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">credits</p>
                        </div>
                        <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-full">
                            <x-icon name="chart-bar" class="h-6 w-6 text-gray-600 dark:text-gray-400" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                <div class="flex flex-wrap gap-4">
                    <button
                        wire:click="setTab('purchase')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                    >
                        <x-icon name="plus" class="h-5 w-5 mr-2" />
                        Purchase Credits
                    </button>
                    @if(!$autoTopUpEnabled)
                        <button
                            wire:click="setTab('auto-topup')"
                            class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors"
                        >
                            <x-icon name="arrow-path" class="h-5 w-5 mr-2" />
                            Enable Auto Top-Up
                        </button>
                    @endif
                    <button
                        wire:click="managePaymentMethods"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors"
                    >
                        <x-icon name="credit-card" class="h-5 w-5 mr-2" />
                        Manage Payment Methods
                    </button>
                </div>
            </div>

            {{-- Usage Breakdown --}}
            @if(!empty($usageBreakdown))
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Usage by Category (Last 30 Days)</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($usageBreakdown as $action => $total)
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ str_replace('_', ' ', ucfirst($action)) }}
                                </p>
                                <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">
                                    {{ number_format($total, 0) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Purchase Credits Tab --}}
    @if($activeTab === 'purchase')
        <div class="max-w-2xl">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Purchase Pulse Credits</h3>

                {{-- Amount Slider --}}
                <div class="mb-8">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Purchase Amount
                    </label>
                    <input
                        type="range"
                        wire:model.live="purchaseAmount"
                        min="10"
                        max="100000"
                        step="10"
                        class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer"
                    >
                    <div class="mt-2 flex justify-between text-sm text-gray-500 dark:text-gray-400">
                        <span>$10</span>
                        <span>${{ number_format($purchaseAmount, 0) }}</span>
                        <span>$100,000</span>
                    </div>
                </div>

                {{-- Quick Amount Buttons --}}
                <div class="flex flex-wrap gap-2 mb-8">
                    @foreach([100, 500, 1000, 5000, 15000, 50000] as $amount)
                        <button
                            wire:click="$set('purchaseAmount', {{ $amount }})"
                            class="{{ $purchaseAmount == $amount
                                ? 'bg-blue-600 text-white'
                                : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}
                                px-4 py-2 rounded-lg font-medium text-sm transition-colors"
                        >
                            ${{ number_format($amount, 0) }}
                        </button>
                    @endforeach
                </div>

                {{-- Credits Preview --}}
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-blue-800 dark:text-blue-200">You'll Receive</p>
                            <p class="mt-1 text-4xl font-bold text-blue-900 dark:text-blue-100">
                                {{ number_format($purchaseCredits, 0) }}
                            </p>
                            <p class="text-sm text-blue-700 dark:text-blue-300">Pulse Credits</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium capitalize
                                {{ $purchaseTier === 'strategic' ? 'bg-purple-100 dark:bg-purple-800 text-purple-800 dark:text-purple-100' :
                                   ($purchaseTier === 'enterprise' ? 'bg-indigo-100 dark:bg-indigo-800 text-indigo-800 dark:text-indigo-100' :
                                   ($purchaseTier === 'growth' ? 'bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-100' :
                                    'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100')) }}">
                                {{ $purchaseTier }} tier
                            </span>
                            <p class="mt-2 text-sm text-blue-600 dark:text-blue-400">
                                {{ $tierConfig[$purchaseTier]['yield'] }} credits/$1
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Tier Breakdown --}}
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Pricing Tiers</h4>
                    <div class="space-y-2">
                        @foreach($tierConfig as $tier => $config)
                            <div class="flex items-center justify-between py-2 px-3 rounded-lg {{ $purchaseTier === $tier ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                                <span class="text-sm capitalize {{ $purchaseTier === $tier ? 'font-medium text-blue-900 dark:text-blue-100' : 'text-gray-600 dark:text-gray-400' }}">
                                    {{ $tier }}
                                    @if($config['min_deposit'] > 0)
                                        <span class="text-xs ml-1">(>${{ number_format($config['min_deposit'], 0) }})</span>
                                    @endif
                                </span>
                                <span class="text-sm {{ $purchaseTier === $tier ? 'font-medium text-blue-900 dark:text-blue-100' : 'text-gray-600 dark:text-gray-400' }}">
                                    {{ number_format($config['yield'], 0) }} credits/$1
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Purchase Button --}}
                <button
                    wire:click="startPurchase"
                    wire:loading.attr="disabled"
                    class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-medium rounded-lg transition-colors"
                >
                    <span wire:loading.remove wire:target="startPurchase">
                        Continue to Payment - ${{ number_format($purchaseAmount, 2) }}
                    </span>
                    <span wire:loading wire:target="startPurchase">
                        Redirecting to Stripe...
                    </span>
                </button>

                <p class="mt-4 text-xs text-center text-gray-500 dark:text-gray-400">
                    Secure payment powered by Stripe. Credits are non-refundable.
                </p>
            </div>
        </div>
    @endif

    {{-- Auto Top-Up Tab --}}
    @if($activeTab === 'auto-topup')
        <div class="max-w-2xl">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Auto Top-Up</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    Automatically purchase credits when your balance falls below a threshold.
                </p>

                @if(!$paymentMethod)
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <x-icon name="exclamation-triangle" class="h-5 w-5 text-yellow-400" />
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                    You need to add a payment method before enabling auto top-up.
                                </p>
                                <button
                                    wire:click="managePaymentMethods"
                                    class="mt-2 text-sm font-medium text-yellow-800 dark:text-yellow-200 hover:underline"
                                >
                                    Add Payment Method &rarr;
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <form wire:submit="saveAutoTopUp" class="space-y-6">
                    {{-- Enable Toggle --}}
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Enable Auto Top-Up
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Automatically add credits when balance is low
                            </p>
                        </div>
                        <button
                            type="button"
                            wire:click="$toggle('autoTopUpEnabled')"
                            class="{{ $autoTopUpEnabled ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700' }} relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                        >
                            <span class="{{ $autoTopUpEnabled ? 'translate-x-6' : 'translate-x-1' }} inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                        </button>
                    </div>

                    @if($autoTopUpEnabled)
                        {{-- Threshold --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Trigger Threshold (credits)
                            </label>
                            <input
                                type="number"
                                wire:model="autoTopUpThreshold"
                                min="100"
                                step="100"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                                placeholder="1000"
                            >
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Auto top-up triggers when balance falls below this amount
                            </p>
                        </div>

                        {{-- Amount --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Top-Up Amount ($)
                            </label>
                            <input
                                type="number"
                                wire:model="autoTopUpAmount"
                                min="10"
                                step="10"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                                placeholder="100"
                            >
                        </div>

                        {{-- Monthly Limit --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Monthly Hard Cap
                            </label>
                            <select
                                wire:model="autoTopUpMonthlyLimit"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                            >
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}">{{ $i }} charge{{ $i > 1 ? 's' : '' }} per month</option>
                                @endfor
                            </select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ $autoTopUpCountThisMonth }} of {{ $autoTopUpMonthlyLimit }} charges used this month
                            </p>
                        </div>
                    @endif

                    {{-- Payment Method Info --}}
                    @if($paymentMethod)
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <x-icon name="credit-card" class="h-6 w-6 text-gray-400 mr-3" />
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white capitalize">
                                            {{ $paymentMethod['brand'] }} **** {{ $paymentMethod['last4'] }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Expires {{ $paymentMethod['exp_month'] }}/{{ $paymentMethod['exp_year'] }}
                                        </p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    wire:click="managePaymentMethods"
                                    class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
                                >
                                    Change
                                </button>
                            </div>
                        </div>
                    @endif

                    <button
                        type="submit"
                        class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                    >
                        Save Settings
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- Transactions Tab --}}
    @if($activeTab === 'transactions')
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Transaction History</h3>
                    <select
                        wire:model.live="transactionFilter"
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm"
                    >
                        <option value="all">All Transactions</option>
                        <option value="purchase">Purchases</option>
                        <option value="usage">Usage</option>
                        <option value="refund">Refunds</option>
                        <option value="adjustment">Adjustments</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Description
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Amount
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Balance
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($transactions as $transaction)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $transaction->created_at->format('M j, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        bg-{{ $transaction->type_badge_color }}-100 dark:bg-{{ $transaction->type_badge_color }}-900/30
                                        text-{{ $transaction->type_badge_color }}-800 dark:text-{{ $transaction->type_badge_color }}-200">
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                    {{ $transaction->description }}
                                    @if($transaction->action_type)
                                        <span class="text-gray-500 dark:text-gray-400">({{ $transaction->action_display }})</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium {{ $transaction->amount >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $transaction->formatted_amount }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">
                                    {{ number_format($transaction->balance_after, 0) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    No transactions found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transactions->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- Feature Valves Tab --}}
    @if($activeTab === 'valves')
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Feature Controls</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    Toggle features on or off for your organization. Disabled features will not consume credits.
                </p>

                <div class="space-y-4">
                    @foreach($featureValves as $featureKey => $valve)
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $valve['name'] }}
                                    </h4>
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ $valve['is_enabled'] ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200' }}">
                                        {{ $valve['is_enabled'] ? 'Active' : 'Disabled' }}
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $valve['description'] }}
                                </p>
                                @if($valve['daily_limit'])
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Daily limit: {{ $valve['daily_usage'] }} / {{ $valve['daily_limit'] }}
                                    </p>
                                @endif
                                @if($valve['disabled_reason'])
                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">
                                        {{ str_replace('_', ' ', ucfirst($valve['disabled_reason'])) }}
                                    </p>
                                @endif
                            </div>
                            <button
                                wire:click="toggleFeatureValve('{{ $featureKey }}')"
                                class="{{ $valve['is_valve_active'] ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-600' }} relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                            >
                                <span class="{{ $valve['is_valve_active'] ? 'translate-x-6' : 'translate-x-1' }} inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Rate Card Tab --}}
    @if($activeTab === 'rates')
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Credit Rate Card</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    How credits are consumed for different actions.
                </p>
            </div>

            @foreach($rateCards as $category => $rates)
                <div class="p-6 {{ !$loop->last ? 'border-b border-gray-200 dark:border-gray-700' : '' }}">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">
                        {{ ucfirst($category) }}
                    </h4>
                    <div class="space-y-3">
                        @foreach($rates as $rate)
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $rate['display_name'] }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        per {{ str_replace(['per_', '_'], ['', ' '], $rate['vendor_unit']) }}
                                    </p>
                                </div>
                                <span class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                    {{ number_format($rate['credit_cost'], 0) }} credits
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
