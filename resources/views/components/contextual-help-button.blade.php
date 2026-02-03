{{-- Contextual Help Button - Dropdown with page help options --}}
<div
    x-data="{
        open: false,
        hintsEnabled: false,
        tooltipCreatorMode: false,
        labels: @js([
            'help_options' => app(\App\Services\TerminologyService::class)->get('help_options_label'),
            'help' => app(\App\Services\TerminologyService::class)->get('help_label'),
            'hide_tooltips' => app(\App\Services\TerminologyService::class)->get('hide_tooltips_label'),
            'show_tooltips' => app(\App\Services\TerminologyService::class)->get('show_tooltips_label'),
            'help_hints_description' => app(\App\Services\TerminologyService::class)->get('help_hints_description_label'),
            'start_page_tour' => app(\App\Services\TerminologyService::class)->get('start_page_tour_label'),
            'tooltips_step_through' => app(\App\Services\TerminologyService::class)->get('tooltips_step_through_label'),
            'help_center' => app(\App\Services\TerminologyService::class)->get('help_center_label'),
            'browse_articles_guides' => app(\App\Services\TerminologyService::class)->get('browse_articles_guides_label'),
            'exit_creator_mode' => app(\App\Services\TerminologyService::class)->get('exit_creator_mode_label'),
            'create_tooltips' => app(\App\Services\TerminologyService::class)->get('create_tooltips_label'),
            'help_tips_description' => app(\App\Services\TerminologyService::class)->get('help_tips_description_label'),
            'manage_tooltips' => app(\App\Services\TerminologyService::class)->get('manage_tooltips_label'),
            'view_edit_tooltips' => app(\App\Services\TerminologyService::class)->get('view_edit_tooltips_label'),
        ]),
        init() {
            // Check if hints are enabled GLOBALLY (persists across all pages until toggled off)
            this.hintsEnabled = localStorage.getItem('helpHintsEnabled') === 'true';
            if (this.hintsEnabled) {
                this.$nextTick(() => {
                    window.dispatchEvent(new CustomEvent('enable-help-hints'));
                });
            }
            // Check if tooltip creator mode was enabled (admin only)
            this.tooltipCreatorMode = sessionStorage.getItem('tooltipCreatorMode') === 'true';
            if (this.tooltipCreatorMode) {
                this.$nextTick(() => {
                    window.dispatchEvent(new CustomEvent('enable-tooltip-creator'));
                });
            }
        }
    }"
    @help-hints-disabled.window="hintsEnabled = false"
    @tooltip-creator-disabled.window="tooltipCreatorMode = false"
    class="relative"
>
    <!-- Help Button -->
    <button
        @click="open = !open"
        class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg transition-colors"
        :class="hintsEnabled ? 'text-purple-600 bg-purple-50' : 'text-gray-600 hover:text-purple-600 hover:bg-purple-50'"
        :title="labels.help_options"
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
        </svg>
        <span x-text="labels.help"></span>
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
        <!-- Tooltips Toggle (GLOBAL - persists across all pages) -->
        <button
            @click="
                hintsEnabled = !hintsEnabled;
                if (hintsEnabled) {
                    localStorage.setItem('helpHintsEnabled', 'true');
                    window.dispatchEvent(new CustomEvent('enable-help-hints'));
                } else {
                    localStorage.removeItem('helpHintsEnabled');
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
                <p class="text-sm font-medium text-gray-900" x-text="hintsEnabled ? labels.hide_tooltips : labels.show_tooltips"></p>
                <p class="text-xs text-gray-500" x-text="labels.help_hints_description"></p>
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

        <!-- Start Page Tour - Steps through each tooltip one-by-one -->
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
                <p class="text-sm font-medium text-gray-900" x-text="labels.start_page_tour"></p>
                <p class="text-xs text-gray-500" x-text="labels.tooltips_step_through"></p>
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
                <p class="text-sm font-medium text-gray-900" x-text="labels.help_center"></p>
                <p class="text-xs text-gray-500" x-text="labels.browse_articles_guides"></p>
            </div>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
        </a>

        {{-- Admin Only: Create Tooltips Toggle --}}
        @if(auth()->check() && auth()->user()->isAdmin())
        <div class="border-t border-gray-100 my-1"></div>

        <button
            @click="
                tooltipCreatorMode = !tooltipCreatorMode;
                if (tooltipCreatorMode) {
                    sessionStorage.setItem('tooltipCreatorMode', 'true');
                    window.dispatchEvent(new CustomEvent('enable-tooltip-creator'));
                } else {
                    sessionStorage.removeItem('tooltipCreatorMode');
                    window.dispatchEvent(new CustomEvent('disable-tooltip-creator'));
                }
                open = false;
            "
            class="w-full flex items-center gap-3 px-4 py-2.5 text-left hover:bg-gray-50 transition-colors"
        >
            <span class="flex items-center justify-center w-8 h-8 rounded-lg" :class="tooltipCreatorMode ? 'bg-orange-100' : 'bg-gray-100'">
                <svg class="w-4 h-4" :class="tooltipCreatorMode ? 'text-orange-600' : 'text-gray-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </span>
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-900" x-text="tooltipCreatorMode ? labels.exit_creator_mode : labels.create_tooltips"></p>
                <p class="text-xs text-gray-500" x-text="labels.help_tips_description"></p>
            </div>
            <span
                class="w-9 h-5 rounded-full transition-colors relative"
                :class="tooltipCreatorMode ? 'bg-orange-500' : 'bg-gray-200'"
            >
                <span
                    class="absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform"
                    :class="tooltipCreatorMode ? 'translate-x-4' : 'translate-x-0.5'"
                ></span>
            </span>
        </button>

        {{-- Admin Link to Manage Tooltips --}}
        <a
            href="{{ route('admin.help-hints') }}"
            @click="open = false"
            class="w-full flex items-center gap-3 px-4 py-2.5 text-left hover:bg-gray-50 transition-colors"
        >
            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </span>
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-900" x-text="labels.manage_tooltips"></p>
                <p class="text-xs text-gray-500" x-text="labels.view_edit_tooltips"></p>
            </div>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>
        @endif
    </div>
</div>
