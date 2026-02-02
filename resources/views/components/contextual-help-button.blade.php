{{-- Contextual Help Button - Dropdown with page help options --}}
<div
    x-data="{
        open: false,
        hintsEnabled: false,
        init() {
            // Check if hints were enabled for this page
            const pageKey = 'helpHints:' + window.location.pathname;
            this.hintsEnabled = sessionStorage.getItem(pageKey) === 'true';
            if (this.hintsEnabled) {
                this.$nextTick(() => {
                    window.dispatchEvent(new CustomEvent('enable-help-hints'));
                });
            }
        }
    }"
    @help-hints-disabled.window="hintsEnabled = false"
    class="relative"
>
    <!-- Help Button -->
    <button
        @click="open = !open"
        class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg transition-colors"
        :class="hintsEnabled ? 'text-purple-600 bg-purple-50' : 'text-gray-600 hover:text-purple-600 hover:bg-purple-50'"
        title="Help options"
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
        </svg>
        Help
        <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <!-- Dropdown Menu -->
    <div
        x-show="open"
        @click.outside="open = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 z-50 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-1 overflow-hidden"
    >
        <!-- Tooltips Toggle -->
        <button
            @click="
                hintsEnabled = !hintsEnabled;
                const pageKey = 'helpHints:' + window.location.pathname;
                if (hintsEnabled) {
                    sessionStorage.setItem(pageKey, 'true');
                    window.dispatchEvent(new CustomEvent('enable-help-hints'));
                } else {
                    sessionStorage.removeItem(pageKey);
                    window.dispatchEvent(new CustomEvent('disable-help-hints'));
                }
                open = false;
            "
            class="w-full flex items-center gap-3 px-4 py-2.5 text-left hover:bg-gray-50 transition-colors"
        >
            <span class="flex items-center justify-center w-8 h-8 rounded-lg" :class="hintsEnabled ? 'bg-purple-100' : 'bg-gray-100'">
                <svg class="w-4 h-4" :class="hintsEnabled ? 'text-purple-600' : 'text-gray-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
            </span>
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-900" x-text="hintsEnabled ? 'Hide Tooltips' : 'Show Tooltips'"></p>
                <p class="text-xs text-gray-500">Interactive help dots on this page</p>
            </div>
            <span
                class="w-9 h-5 rounded-full transition-colors relative"
                :class="hintsEnabled ? 'bg-purple-500' : 'bg-gray-200'"
            >
                <span
                    class="absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform"
                    :class="hintsEnabled ? 'translate-x-4' : 'translate-x-0.5'"
                ></span>
            </span>
        </button>

        <div class="border-t border-gray-100 my-1"></div>

        <!-- Start Page Tour -->
        <button
            @click="
                open = false;
                window.dispatchEvent(new CustomEvent('start-page-help'));
            "
            class="w-full flex items-center gap-3 px-4 py-2.5 text-left hover:bg-gray-50 transition-colors"
        >
            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">Start Page Tour</p>
                <p class="text-xs text-gray-500">Guided walkthrough of features</p>
            </div>
        </button>

        <div class="border-t border-gray-100 my-1"></div>

        <!-- Help Center Link -->
        <a
            href="{{ route('help.index') }}"
            @click="open = false"
            class="w-full flex items-center gap-3 px-4 py-2.5 text-left hover:bg-gray-50 transition-colors"
        >
            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </span>
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">Help Center</p>
                <p class="text-xs text-gray-500">Browse articles and guides</p>
            </div>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
        </a>
    </div>
</div>
