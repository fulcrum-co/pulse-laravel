@props(['user' => auth()->user()])

@php
    $helpOverlayLabels = [
        'of_label' => app(\App\Services\TerminologyService::class)->get('help_overlay_of_label'),
        'back_label' => app(\App\Services\TerminologyService::class)->get('back_label'),
        'next_label' => app(\App\Services\TerminologyService::class)->get('help_overlay_next_label'),
        'got_it_label' => app(\App\Services\TerminologyService::class)->get('help_overlay_got_it_label'),
        'dashboard_intro_title' => app(\App\Services\TerminologyService::class)->get('help_tour_dashboard_intro_title'),
        'dashboard_intro_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_dashboard_intro_desc'),
        'dashboard_selector_title' => app(\App\Services\TerminologyService::class)->get('help_tour_dashboard_selector_title'),
        'dashboard_selector_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_dashboard_selector_desc'),
        'dashboard_selector_tip' => app(\App\Services\TerminologyService::class)->get('help_tour_dashboard_selector_tip'),
        'dashboard_actions_title' => app(\App\Services\TerminologyService::class)->get('help_tour_dashboard_actions_title'),
        'dashboard_actions_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_dashboard_actions_desc'),
        'dashboard_date_range_title' => app(\App\Services\TerminologyService::class)->get('help_tour_dashboard_date_range_title'),
        'dashboard_date_range_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_dashboard_date_range_desc'),
        'dashboard_date_range_tip' => app(\App\Services\TerminologyService::class)->get('help_tour_dashboard_date_range_tip'),
        'dashboard_widgets_title' => app(\App\Services\TerminologyService::class)->get('help_tour_dashboard_widgets_title'),
        'dashboard_widgets_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_dashboard_widgets_desc'),
        'dashboard_widgets_tip' => app(\App\Services\TerminologyService::class)->get('help_tour_dashboard_widgets_tip'),
        'surveys_intro_title' => app(\App\Services\TerminologyService::class)->get('help_tour_surveys_intro_title'),
        'surveys_intro_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_surveys_intro_desc'),
        'surveys_create_title' => app(\App\Services\TerminologyService::class)->get('help_tour_surveys_create_title'),
        'surveys_create_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_surveys_create_desc'),
        'surveys_create_tip' => app(\App\Services\TerminologyService::class)->get('help_tour_surveys_create_tip'),
        'surveys_list_title' => app(\App\Services\TerminologyService::class)->get('help_tour_surveys_list_title'),
        'surveys_list_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_surveys_list_desc'),
        'alerts_intro_title' => app(\App\Services\TerminologyService::class)->get('help_tour_alerts_intro_title'),
        'alerts_intro_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_alerts_intro_desc'),
        'alerts_filters_title' => app(\App\Services\TerminologyService::class)->get('help_tour_alerts_filters_title'),
        'alerts_filters_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_alerts_filters_desc'),
        'alerts_list_title' => app(\App\Services\TerminologyService::class)->get('help_tour_alerts_list_title'),
        'alerts_list_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_alerts_list_desc'),
        'alerts_list_tip' => app(\App\Services\TerminologyService::class)->get('help_tour_alerts_list_tip'),
        'contacts_intro_title' => app(\App\Services\TerminologyService::class)->get('help_tour_contacts_intro_title'),
        'contacts_intro_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_contacts_intro_desc'),
        'contacts_search_title' => app(\App\Services\TerminologyService::class)->get('help_tour_contacts_search_title'),
        'contacts_search_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_contacts_search_desc'),
        'contacts_list_title' => app(\App\Services\TerminologyService::class)->get('help_tour_contacts_list_title'),
        'contacts_list_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_contacts_list_desc'),
        'plans_intro_title' => app(\App\Services\TerminologyService::class)->get('help_tour_plans_intro_title'),
        'plans_intro_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_plans_intro_desc'),
        'plans_search_title' => app(\App\Services\TerminologyService::class)->get('help_tour_plans_search_title'),
        'plans_search_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_plans_search_desc'),
        'plans_filters_title' => app(\App\Services\TerminologyService::class)->get('help_tour_plans_filters_title'),
        'plans_filters_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_plans_filters_desc'),
        'plans_list_title' => app(\App\Services\TerminologyService::class)->get('help_tour_plans_list_title'),
        'plans_list_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_plans_list_desc'),
        'plans_list_tip' => app(\App\Services\TerminologyService::class)->get('help_tour_plans_list_tip'),
        'resources_intro_title' => app(\App\Services\TerminologyService::class)->get('help_tour_resources_intro_title'),
        'resources_intro_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_resources_intro_desc'),
        'resources_search_title' => app(\App\Services\TerminologyService::class)->get('help_tour_resources_search_title'),
        'resources_search_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_resources_search_desc'),
        'resources_filters_title' => app(\App\Services\TerminologyService::class)->get('help_tour_resources_filters_title'),
        'resources_filters_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_resources_filters_desc'),
        'resources_filters_tip' => app(\App\Services\TerminologyService::class)->get('help_tour_resources_filters_tip'),
        'resources_categories_title' => app(\App\Services\TerminologyService::class)->get('help_tour_resources_categories_title'),
        'resources_categories_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_resources_categories_desc'),
        'collect_intro_title' => app(\App\Services\TerminologyService::class)->get('help_tour_collect_intro_title'),
        'collect_intro_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_collect_intro_desc'),
        'collect_search_title' => app(\App\Services\TerminologyService::class)->get('help_tour_collect_search_title'),
        'collect_search_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_collect_search_desc'),
        'collect_filters_title' => app(\App\Services\TerminologyService::class)->get('help_tour_collect_filters_title'),
        'collect_filters_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_collect_filters_desc'),
        'collect_filters_tip' => app(\App\Services\TerminologyService::class)->get('help_tour_collect_filters_tip'),
        'collect_list_title' => app(\App\Services\TerminologyService::class)->get('help_tour_collect_list_title'),
        'collect_list_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_collect_list_desc'),
        'collect_list_tip' => app(\App\Services\TerminologyService::class)->get('help_tour_collect_list_tip'),
        'distribute_intro_title' => app(\App\Services\TerminologyService::class)->get('help_tour_distribute_intro_title'),
        'distribute_intro_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_distribute_intro_desc'),
        'distribute_search_title' => app(\App\Services\TerminologyService::class)->get('help_tour_distribute_search_title'),
        'distribute_search_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_distribute_search_desc'),
        'distribute_filters_title' => app(\App\Services\TerminologyService::class)->get('help_tour_distribute_filters_title'),
        'distribute_filters_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_distribute_filters_desc'),
        'distribute_filters_tip' => app(\App\Services\TerminologyService::class)->get('help_tour_distribute_filters_tip'),
        'distribute_list_title' => app(\App\Services\TerminologyService::class)->get('help_tour_distribute_list_title'),
        'distribute_list_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_distribute_list_desc'),
        'distribute_list_tip' => app(\App\Services\TerminologyService::class)->get('help_tour_distribute_list_tip'),
        'reports_intro_title' => app(\App\Services\TerminologyService::class)->get('help_tour_reports_intro_title'),
        'reports_intro_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_reports_intro_desc'),
        'reports_search_title' => app(\App\Services\TerminologyService::class)->get('help_tour_reports_search_title'),
        'reports_search_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_reports_search_desc'),
        'reports_filters_title' => app(\App\Services\TerminologyService::class)->get('help_tour_reports_filters_title'),
        'reports_filters_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_reports_filters_desc'),
        'reports_filters_tip' => app(\App\Services\TerminologyService::class)->get('help_tour_reports_filters_tip'),
        'reports_list_title' => app(\App\Services\TerminologyService::class)->get('help_tour_reports_list_title'),
        'reports_list_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_reports_list_desc'),
        'reports_list_tip' => app(\App\Services\TerminologyService::class)->get('help_tour_reports_list_tip'),
        'general_intro_title' => app(\App\Services\TerminologyService::class)->get('help_tour_general_intro_title'),
        'general_intro_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_general_intro_desc'),
        'general_navigation_title' => app(\App\Services\TerminologyService::class)->get('help_tour_general_navigation_title'),
        'general_navigation_desc' => app(\App\Services\TerminologyService::class)->get('help_tour_general_navigation_desc'),
    ];
@endphp

{{-- Page Help Overlay - Guided walkthrough for current page --}}
<div
    x-data="pageHelpOverlay(@js($helpOverlayLabels))"
    x-show="active"
    x-cloak
    @start-page-help.window="startHelp($event.detail?.context, $event.detail?.section)"
    class="fixed inset-0 z-[100]"
>
    <!-- Backdrop with spotlight cutout -->
    <div
        class="absolute inset-0 bg-black/50 transition-opacity duration-300"
        :style="spotlightStyle"
        @click="close()"
    ></div>

    <!-- Help Tooltip -->
    <div
        x-show="currentStep"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        :style="tooltipStyle"
        class="absolute bg-white rounded-xl shadow-2xl max-w-sm w-full pointer-events-auto"
    >
        <!-- Arrow -->
        <div
            x-show="currentStep?.selector"
            class="absolute w-4 h-4 bg-white transform rotate-45 shadow-sm"
            :class="{
                '-top-2 left-8': tooltipPosition === 'bottom',
                '-bottom-2 left-8': tooltipPosition === 'top',
                '-left-2 top-8': tooltipPosition === 'right',
                '-right-2 top-8': tooltipPosition === 'left'
            }"
        ></div>

        <!-- Content -->
        <div class="relative p-5">
            <!-- Header with step counter -->
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center w-6 h-6 rounded-full bg-purple-100 text-purple-700 text-xs font-bold" x-text="currentIndex + 1"></span>
                    <span class="text-xs text-gray-500"><span x-text="labels.of_label"></span> <span x-text="steps.length"></span></span>
                </div>
                <button
                    @click="close()"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Progress bar -->
            <div class="h-1 bg-gray-100 rounded-full mb-4 overflow-hidden">
                <div
                    class="h-full bg-purple-500 transition-all duration-300"
                    :style="`width: ${((currentIndex + 1) / steps.length) * 100}%`"
                ></div>
            </div>

            <!-- Step title -->
            <h3 class="text-base font-semibold text-gray-900 mb-2" x-text="currentStep?.title"></h3>

            <!-- Step description -->
            <p class="text-sm text-gray-600 mb-4" x-text="currentStep?.description"></p>

            <!-- Tip (if any) -->
            <div
                x-show="currentStep?.tip"
                class="flex items-start gap-2 p-3 bg-amber-50 border border-amber-100 rounded-lg mb-4"
            >
                <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <span class="text-xs text-amber-800" x-text="currentStep?.tip"></span>
            </div>

            <!-- Navigation buttons -->
            <div class="flex items-center justify-between gap-3">
                <button
                    @click="prevStep()"
                    x-show="currentIndex > 0"
                    class="px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
                >
                    ← <span x-text="labels.back_label"></span>
                </button>
                <div x-show="currentIndex === 0"></div>

                <button
                    @click="nextStep()"
                    class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors"
                    x-text="currentIndex === steps.length - 1 ? labels.got_it_label : labels.next_label"
                ></button>
            </div>
        </div>
    </div>
</div>

<script>
function pageHelpOverlay(labels) {
    return {
        labels,
        active: false,
        steps: [],
        currentIndex: 0,
        currentStep: null,
        targetElement: null,
        tooltipPosition: 'bottom',
        hintsLoaded: false,

        // Define help tours for different pages (fallback data, overridden by API)
        helpTours: {
            'dashboard': [
                {
                    section: 'intro',
                    selector: null,
                    title: labels.dashboard_intro_title,
                    description: labels.dashboard_intro_desc,
                    position: 'center'
                },
                {
                    section: 'selector',
                    selector: '[data-help="dashboard-selector"]',
                    title: labels.dashboard_selector_title,
                    description: labels.dashboard_selector_desc,
                    tip: labels.dashboard_selector_tip,
                    position: 'bottom'
                },
                {
                    section: 'actions',
                    selector: '[data-help="dashboard-actions"]',
                    title: labels.dashboard_actions_title,
                    description: labels.dashboard_actions_desc,
                    position: 'bottom'
                },
                {
                    section: 'date-range',
                    selector: '[data-help="date-range"]',
                    title: labels.dashboard_date_range_title,
                    description: labels.dashboard_date_range_desc,
                    tip: labels.dashboard_date_range_tip,
                    position: 'bottom'
                },
                {
                    section: 'widgets',
                    selector: '[data-help="widgets-grid"]',
                    title: labels.dashboard_widgets_title,
                    description: labels.dashboard_widgets_desc,
                    tip: labels.dashboard_widgets_tip,
                    position: 'top'
                }
            ],
            'surveys': [
                {
                    section: 'intro',
                    selector: null,
                    title: labels.surveys_intro_title,
                    description: labels.surveys_intro_desc,
                    position: 'center'
                },
                {
                    section: 'create',
                    selector: '[data-help="create-survey"], button:contains("Create"), a[href*="create"]',
                    title: labels.surveys_create_title,
                    description: labels.surveys_create_desc,
                    tip: labels.surveys_create_tip,
                    position: 'bottom'
                },
                {
                    section: 'list',
                    selector: '[data-help="survey-list"], .survey-list, table',
                    title: labels.surveys_list_title,
                    description: labels.surveys_list_desc,
                    position: 'top'
                }
            ],
            'alerts': [
                {
                    section: 'intro',
                    selector: null,
                    title: labels.alerts_intro_title,
                    description: labels.alerts_intro_desc,
                    position: 'center'
                },
                {
                    section: 'filters',
                    selector: '[data-help="alert-filters"], .filters, [class*="filter"]',
                    title: labels.alerts_filters_title,
                    description: labels.alerts_filters_desc,
                    position: 'bottom'
                },
                {
                    section: 'list',
                    selector: '[data-help="alert-list"], .alert-list, [class*="alert-item"]',
                    title: labels.alerts_list_title,
                    description: labels.alerts_list_desc,
                    tip: labels.alerts_list_tip,
                    position: 'top'
                }
            ],
            'contacts': [
                {
                    section: 'intro',
                    selector: null,
                    title: labels.contacts_intro_title,
                    description: labels.contacts_intro_desc,
                    position: 'center'
                },
                {
                    section: 'search',
                    selector: '[data-help="search-contacts"], input[type="search"], .search',
                    title: labels.contacts_search_title,
                    description: labels.contacts_search_desc,
                    position: 'bottom'
                },
                {
                    section: 'list',
                    selector: '[data-help="contact-list"], .contact-list, table',
                    title: labels.contacts_list_title,
                    description: labels.contacts_list_desc,
                    position: 'top'
                }
            ],
            'plans': [
                {
                    section: 'intro',
                    selector: null,
                    title: labels.plans_intro_title,
                    description: labels.plans_intro_desc,
                    position: 'center'
                },
                {
                    section: 'search',
                    selector: '[data-help="search-plans"], input[placeholder*="Search"]',
                    title: labels.plans_search_title,
                    description: labels.plans_search_desc,
                    position: 'bottom'
                },
                {
                    section: 'filters',
                    selector: '[data-help="plan-filters"], select',
                    title: labels.plans_filters_title,
                    description: labels.plans_filters_desc,
                    position: 'bottom'
                },
                {
                    section: 'list',
                    selector: '[data-help="plan-list"], .plan-list, [class*="plan-card"]',
                    title: labels.plans_list_title,
                    description: labels.plans_list_desc,
                    tip: labels.plans_list_tip,
                    position: 'top'
                }
            ],
            'resources': [
                {
                    section: 'intro',
                    selector: null,
                    title: labels.resources_intro_title,
                    description: labels.resources_intro_desc,
                    position: 'center'
                },
                {
                    section: 'search',
                    selector: '[data-help="search-resources"]',
                    title: labels.resources_search_title,
                    description: labels.resources_search_desc,
                    position: 'bottom'
                },
                {
                    section: 'filters',
                    selector: '[data-help="resource-filters"]',
                    title: labels.resources_filters_title,
                    description: labels.resources_filters_desc,
                    tip: labels.resources_filters_tip,
                    position: 'right'
                },
                {
                    section: 'categories',
                    selector: '[data-help="resource-categories"]',
                    title: labels.resources_categories_title,
                    description: labels.resources_categories_desc,
                    position: 'top'
                }
            ],
            'collect': [
                {
                    section: 'intro',
                    selector: null,
                    title: labels.collect_intro_title,
                    description: labels.collect_intro_desc,
                    position: 'center'
                },
                {
                    section: 'search',
                    selector: '[data-help="search-collections"]',
                    title: labels.collect_search_title,
                    description: labels.collect_search_desc,
                    position: 'bottom'
                },
                {
                    section: 'filters',
                    selector: '[data-help="collection-filters"]',
                    title: labels.collect_filters_title,
                    description: labels.collect_filters_desc,
                    tip: labels.collect_filters_tip,
                    position: 'bottom'
                },
                {
                    section: 'list',
                    selector: '[data-help="collection-list"]',
                    title: labels.collect_list_title,
                    description: labels.collect_list_desc,
                    tip: labels.collect_list_tip,
                    position: 'top'
                }
            ],
            'distribute': [
                {
                    section: 'intro',
                    selector: null,
                    title: labels.distribute_intro_title,
                    description: labels.distribute_intro_desc,
                    position: 'center'
                },
                {
                    section: 'search',
                    selector: '[data-help="search-distributions"]',
                    title: labels.distribute_search_title,
                    description: labels.distribute_search_desc,
                    position: 'bottom'
                },
                {
                    section: 'filters',
                    selector: '[data-help="distribution-filters"]',
                    title: labels.distribute_filters_title,
                    description: labels.distribute_filters_desc,
                    tip: labels.distribute_filters_tip,
                    position: 'bottom'
                },
                {
                    section: 'list',
                    selector: '[data-help="distribution-list"]',
                    title: labels.distribute_list_title,
                    description: labels.distribute_list_desc,
                    tip: labels.distribute_list_tip,
                    position: 'top'
                }
            ],
            'reports': [
                {
                    section: 'intro',
                    selector: null,
                    title: labels.reports_intro_title,
                    description: labels.reports_intro_desc,
                    position: 'center'
                },
                {
                    section: 'search',
                    selector: '[data-help="search-reports"]',
                    title: labels.reports_search_title,
                    description: labels.reports_search_desc,
                    position: 'bottom'
                },
                {
                    section: 'filters',
                    selector: '[data-help="report-filters"]',
                    title: labels.reports_filters_title,
                    description: labels.reports_filters_desc,
                    tip: labels.reports_filters_tip,
                    position: 'bottom'
                },
                {
                    section: 'list',
                    selector: '[data-help="report-list"]',
                    title: labels.reports_list_title,
                    description: labels.reports_list_desc,
                    tip: labels.reports_list_tip,
                    position: 'top'
                }
            ],
            'general': [
                {
                    section: 'intro',
                    selector: null,
                    title: labels.general_intro_title,
                    description: labels.general_intro_desc,
                    position: 'center'
                },
                {
                    section: 'navigation',
                    selector: 'header, [class*="header"]',
                    title: labels.general_navigation_title,
                    description: labels.general_navigation_desc,
                    position: 'bottom'
                }
            ]
        },

        async startHelp(context = null, section = null) {
            // Load hints from API if not already loaded
            if (!this.hintsLoaded) {
                await this.loadHintsFromApi();
            }

            const pageContext = context || this.detectContext();
            this.steps = this.helpTours[pageContext] || this.helpTours['general'];

            // If a specific section is requested, find its index
            let startIndex = 0;
            if (section) {
                const sectionIndex = this.steps.findIndex(s => s.section === section);
                if (sectionIndex !== -1) {
                    startIndex = sectionIndex;
                }
            }

            this.currentIndex = startIndex;
            this.setCurrentStep(this.steps[startIndex]);
            this.active = true;
            document.body.style.overflow = 'hidden';
        },

        async loadHintsFromApi() {
            try {
                const response = await fetch('/api/help/page-hints', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.hints && Object.keys(data.hints).length > 0) {
                        // Merge API hints with existing tours (API data extends static data)
                        for (const [context, hints] of Object.entries(data.hints)) {
                            if (this.helpTours[context]) {
                                // Add intro step if it exists, then API hints
                                const intro = this.helpTours[context].find(s => s.section === 'intro');
                                this.helpTours[context] = intro ? [intro, ...hints] : hints;
                            } else {
                                this.helpTours[context] = hints;
                            }
                        }
                    }
                }
            } catch (e) {
                // Fallback to static data (already loaded)
                console.debug('Using fallback help tours');
            }
            this.hintsLoaded = true;
        },

        detectContext() {
            const path = window.location.pathname;
            if (path.includes('/plans')) return 'plans';
            if (path.includes('/surveys')) return 'surveys';
            if (path.includes('/alerts')) return 'alerts';
            if (path.includes('/participants') || path.includes('/contacts')) return 'contacts';
            if (path.includes('/resources')) return 'resources';
            if (path.includes('/collect')) return 'collect';
            if (path.includes('/distribute')) return 'distribute';
            if (path.includes('/reports')) return 'reports';
            if (path.includes('/dashboard') || path === '/') return 'dashboard';
            return 'general';
        },

        setCurrentStep(step) {
            this.currentStep = step;

            if (step.selector) {
                this.$nextTick(() => {
                    this.highlightTarget(step.selector);
                });
            } else {
                this.targetElement = null;
                this.tooltipPosition = 'center';
            }
        },

        highlightTarget(selector) {
            // Try multiple selectors (comma-separated fallbacks)
            const selectors = selector.split(',').map(s => s.trim());
            let el = null;

            for (const sel of selectors) {
                try {
                    el = document.querySelector(sel);
                    if (el) break;
                } catch (e) {
                    // Invalid selector, try next
                }
            }

            if (el) {
                this.targetElement = el;
                this.tooltipPosition = this.currentStep.position || 'bottom';
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                this.targetElement = null;
                this.tooltipPosition = 'center';
            }
        },

        nextStep() {
            if (this.currentIndex < this.steps.length - 1) {
                this.currentIndex++;
                this.setCurrentStep(this.steps[this.currentIndex]);
            } else {
                this.close();
            }
        },

        prevStep() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
                this.setCurrentStep(this.steps[this.currentIndex]);
            }
        },

        close() {
            this.active = false;
            this.targetElement = null;
            document.body.style.overflow = '';
            // Dispatch event so beacons can reappear
            window.dispatchEvent(new CustomEvent('help-overlay-closed'));
        },

        get spotlightStyle() {
            if (!this.targetElement) {
                return '';
            }

            const rect = this.targetElement.getBoundingClientRect();
            const padding = 8;

            return `
                clip-path: polygon(
                    0 0, 100% 0, 100% 100%, 0 100%, 0 0,
                    ${rect.left - padding}px ${rect.top - padding}px,
                    ${rect.left - padding}px ${rect.bottom + padding}px,
                    ${rect.right + padding}px ${rect.bottom + padding}px,
                    ${rect.right + padding}px ${rect.top - padding}px,
                    ${rect.left - padding}px ${rect.top - padding}px
                );
            `;
        },

        get tooltipStyle() {
            if (!this.targetElement || this.tooltipPosition === 'center') {
                return 'top: 50%; left: 50%; transform: translate(-50%, -50%);';
            }

            const rect = this.targetElement.getBoundingClientRect();
            const tooltipWidth = 360;
            const tooltipHeight = 250;
            const margin = 16;

            let top, left;

            switch (this.tooltipPosition) {
                case 'top':
                    top = rect.top - tooltipHeight - margin;
                    left = rect.left + (rect.width / 2) - (tooltipWidth / 2);
                    break;
                case 'bottom':
                    top = rect.bottom + margin;
                    left = rect.left + (rect.width / 2) - (tooltipWidth / 2);
                    break;
                case 'left':
                    top = rect.top + (rect.height / 2) - (tooltipHeight / 2);
                    left = rect.left - tooltipWidth - margin;
                    break;
                case 'right':
                    top = rect.top + (rect.height / 2) - (tooltipHeight / 2);
                    left = rect.right + margin;
                    break;
                default:
                    top = rect.bottom + margin;
                    left = rect.left;
            }

            // Keep tooltip in viewport
            left = Math.max(16, Math.min(left, window.innerWidth - tooltipWidth - 16));
            top = Math.max(16, Math.min(top, window.innerHeight - tooltipHeight - 16));

            return `top: ${top}px; left: ${left}px;`;
        }
    };
}
</script>
