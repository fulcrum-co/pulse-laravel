<x-layouts.app title="Pulse Demo Access">
    <div class="min-h-screen bg-gradient-to-b from-white via-gray-50 to-white">
        <div class="mx-auto max-w-6xl px-6 py-16">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-widest text-pulse-orange-500 mb-4">Pulse</p>
                    <h1 class="text-4xl md:text-5xl font-semibold text-gray-900 leading-tight">
                        A modern operating system for learning, coaching, and performance support.
                    </h1>
                    <p class="mt-4 text-lg text-gray-600">
                        Explore the full platform with a guided, view‑only experience. Switch roles, navigate every surface, and see exactly how Pulse works end‑to‑end.
                    </p>
                    <div class="mt-6 flex items-center gap-3">
                        <button
                            x-data
                            @click="$dispatch('open-demo-access')"
                            class="inline-flex items-center justify-center px-6 py-3 rounded-lg bg-pulse-orange-500 text-white font-medium hover:bg-pulse-orange-600 transition-colors"
                        >
                            Get Access
                        </button>
                        <a href="/login" class="text-sm text-gray-500 hover:text-gray-700">Admin login</a>
                    </div>
                    <div class="mt-6 text-sm text-gray-500">
                        This demo is view‑only. No data changes are saved.
                    </div>
                </div>
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900">What you’ll see</h3>
                    <ul class="mt-4 space-y-3 text-sm text-gray-600">
                        <li>• Role‑based dashboards and insights</li>
                        <li>• Collection workflows and moderation</li>
                        <li>• Resource and program libraries</li>
                        <li>• Live notifications and alerts</li>
                    </ul>
                </div>
            </div>
        </div>

        <div
            x-data="{ open: false }"
            @open-demo-access.window="open = true"
            @keydown.escape.window="open = false"
        >
            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
                <div class="absolute inset-0 bg-black/40" @click="open = false"></div>
                <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-900">Get Access</h2>
                        <button class="text-gray-400 hover:text-gray-600" @click="open = false">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('demo.access') }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">First name</label>
                                <input name="first_name" required class="w-full rounded-lg border-gray-300 focus:ring-pulse-orange-500 focus:border-pulse-orange-500" />
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">Last name</label>
                                <input name="last_name" required class="w-full rounded-lg border-gray-300 focus:ring-pulse-orange-500 focus:border-pulse-orange-500" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Email address</label>
                            <input type="email" name="email" required class="w-full rounded-lg border-gray-300 focus:ring-pulse-orange-500 focus:border-pulse-orange-500" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Phone number</label>
                            <input name="phone" class="w-full rounded-lg border-gray-300 focus:ring-pulse-orange-500 focus:border-pulse-orange-500" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Organization name</label>
                            <input name="org_name" class="w-full rounded-lg border-gray-300 focus:ring-pulse-orange-500 focus:border-pulse-orange-500" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Organization size (all people served)</label>
                            <input name="org_size" class="w-full rounded-lg border-gray-300 focus:ring-pulse-orange-500 focus:border-pulse-orange-500" />
                            <p class="mt-1 text-xs text-gray-500">
                                Includes everyone in the organization’s scope—administrators, staff, participants, and families/guardians. If applicable, include after‑school volunteers/support.
                            </p>
                        </div>
                        <button
                            type="submit"
                            class="w-full bg-pulse-orange-500 text-white py-2.5 rounded-lg font-medium hover:bg-pulse-orange-600"
                        >
                            Get Access
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
