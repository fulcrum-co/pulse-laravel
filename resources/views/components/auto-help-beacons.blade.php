{{-- Auto Help Beacons - Pulsating help dots at elements matching helpTours selectors --}}
{{-- Only shown when user enables "Show Tooltips" from the Help dropdown --}}
@php
    $terminology = app(\App\Services\TerminologyService::class);

    $helpBeaconLabels = [
        'click_for_details_label' => $terminology->get('help_beacon_click_for_details_label'),
        'hide_video_label' => $terminology->get('help_beacon_hide_video_label'),
        'watch_video_label' => $terminology->get('help_beacon_watch_video_label'),
        'start_walkthrough_label' => $terminology->get('help_beacon_start_walkthrough_label'),
        'dismiss_hint_title' => $terminology->get('help_beacon_dismiss_hint_title'),
    ];

    $helpBeaconTours = [
        'dashboard' => [
            [
                'section' => 'selector',
                'selector' => '[data-help="dashboard-selector"]',
                'title' => $terminology->get('help_tour_dashboard_selector_title'),
                'description' => $terminology->get('help_tour_dashboard_selector_desc'),
                'position' => 'bottom',
            ],
            [
                'section' => 'actions',
                'selector' => '[data-help="dashboard-actions"]',
                'title' => $terminology->get('help_tour_dashboard_actions_title'),
                'description' => $terminology->get('help_tour_dashboard_actions_desc'),
                'position' => 'bottom',
            ],
            [
                'section' => 'date-range',
                'selector' => '[data-help="date-range"]',
                'title' => $terminology->get('help_tour_dashboard_date_range_title'),
                'description' => $terminology->get('help_tour_dashboard_date_range_desc'),
                'position' => 'bottom',
            ],
            [
                'section' => 'widgets',
                'selector' => '[data-help="widgets-grid"]',
                'title' => $terminology->get('help_tour_dashboard_widgets_title'),
                'description' => $terminology->get('help_tour_dashboard_widgets_desc'),
                'position' => 'top',
            ],
        ],
        'surveys' => [
            [
                'section' => 'create',
                'selector' => '[data-help="create-survey"], [href*="create"]',
                'title' => $terminology->get('help_tour_surveys_create_title'),
                'description' => $terminology->get('help_tour_surveys_create_desc'),
                'position' => 'bottom',
            ],
            [
                'section' => 'list',
                'selector' => '[data-help="survey-list"], .survey-list, table',
                'title' => $terminology->get('help_tour_surveys_list_title'),
                'description' => $terminology->get('help_tour_surveys_list_desc'),
                'position' => 'top',
            ],
        ],
        'alerts' => [
            [
                'section' => 'filters',
                'selector' => '[data-help="alert-filters"], .filters, [class*="filter"]',
                'title' => $terminology->get('help_tour_alerts_filters_title'),
                'description' => $terminology->get('help_tour_alerts_filters_desc'),
                'position' => 'bottom',
            ],
            [
                'section' => 'list',
                'selector' => '[data-help="alert-list"], .alert-list, [class*="alert-item"]',
                'title' => $terminology->get('help_tour_alerts_list_title'),
                'description' => $terminology->get('help_tour_alerts_list_desc'),
                'position' => 'top',
            ],
        ],
        'contacts' => [
            [
                'section' => 'search',
                'selector' => '[data-help="search-contacts"], input[type="search"], .search',
                'title' => $terminology->get('help_tour_contacts_search_title'),
                'description' => $terminology->get('help_tour_contacts_search_desc'),
                'position' => 'bottom',
            ],
            [
                'section' => 'list',
                'selector' => '[data-help="contact-list"], .contact-list, table',
                'title' => $terminology->get('help_tour_contacts_list_title'),
                'description' => $terminology->get('help_tour_contacts_list_desc'),
                'position' => 'top',
            ],
        ],
        'plans' => [
            [
                'section' => 'search',
                'selector' => '[data-help="search-plans"], input[placeholder*="Search"]',
                'title' => $terminology->get('help_tour_plans_search_title'),
                'description' => $terminology->get('help_tour_plans_search_desc'),
                'position' => 'bottom',
            ],
            [
                'section' => 'filters',
                'selector' => '[data-help="plan-filters"], select',
                'title' => $terminology->get('help_tour_plans_filters_title'),
                'description' => $terminology->get('help_tour_plans_filters_desc'),
                'position' => 'bottom',
            ],
            [
                'section' => 'list',
                'selector' => '[data-help="plan-list"], .plan-list, [class*="plan-card"]',
                'title' => $terminology->get('help_tour_plans_list_title'),
                'description' => $terminology->get('help_tour_plans_list_desc'),
                'position' => 'top',
            ],
        ],
        'resources' => [
            [
                'section' => 'search',
                'selector' => '[data-help="search-resources"]',
                'title' => $terminology->get('help_tour_resources_search_title'),
                'description' => $terminology->get('help_tour_resources_search_desc'),
                'position' => 'bottom',
            ],
            [
                'section' => 'filters',
                'selector' => '[data-help="resource-filters"]',
                'title' => $terminology->get('help_tour_resources_filters_title'),
                'description' => $terminology->get('help_tour_resources_filters_desc'),
                'position' => 'right',
            ],
            [
                'section' => 'categories',
                'selector' => '[data-help="resource-categories"]',
                'title' => $terminology->get('help_tour_resources_categories_title'),
                'description' => $terminology->get('help_tour_resources_categories_desc'),
                'position' => 'top',
            ],
        ],
        'collect' => [
            [
                'section' => 'search',
                'selector' => '[data-help="search-collections"]',
                'title' => $terminology->get('help_tour_collect_search_title'),
                'description' => $terminology->get('help_tour_collect_search_desc'),
                'position' => 'bottom',
            ],
            [
                'section' => 'filters',
                'selector' => '[data-help="collection-filters"]',
                'title' => $terminology->get('help_tour_collect_filters_title'),
                'description' => $terminology->get('help_tour_collect_filters_desc'),
                'position' => 'bottom',
            ],
            [
                'section' => 'list',
                'selector' => '[data-help="collection-list"]',
                'title' => $terminology->get('help_tour_collect_list_title'),
                'description' => $terminology->get('help_tour_collect_list_desc'),
                'position' => 'top',
            ],
        ],
        'distribute' => [
            [
                'section' => 'search',
                'selector' => '[data-help="search-distributions"]',
                'title' => $terminology->get('help_tour_distribute_search_title'),
                'description' => $terminology->get('help_tour_distribute_search_desc'),
                'position' => 'bottom',
            ],
            [
                'section' => 'filters',
                'selector' => '[data-help="distribution-filters"]',
                'title' => $terminology->get('help_tour_distribute_filters_title'),
                'description' => $terminology->get('help_tour_distribute_filters_desc'),
                'position' => 'bottom',
            ],
            [
                'section' => 'list',
                'selector' => '[data-help="distribution-list"]',
                'title' => $terminology->get('help_tour_distribute_list_title'),
                'description' => $terminology->get('help_tour_distribute_list_desc'),
                'position' => 'top',
            ],
        ],
        'reports' => [
            [
                'section' => 'search',
                'selector' => '[data-help="search-reports"]',
                'title' => $terminology->get('help_tour_reports_search_title'),
                'description' => $terminology->get('help_tour_reports_search_desc'),
                'position' => 'bottom',
            ],
            [
                'section' => 'filters',
                'selector' => '[data-help="report-filters"]',
                'title' => $terminology->get('help_tour_reports_filters_title'),
                'description' => $terminology->get('help_tour_reports_filters_desc'),
                'position' => 'bottom',
            ],
            [
                'section' => 'list',
                'selector' => '[data-help="report-list"]',
                'title' => $terminology->get('help_tour_reports_list_title'),
                'description' => $terminology->get('help_tour_reports_list_desc'),
                'position' => 'top',
            ],
        ],
    ];
@endphp
<div
    x-data="autoHelpBeacons(@js($helpBeaconLabels), @js($helpBeaconTours))"
    x-init="init()"
    x-show="hintsEnabled && activeBeacons.length > 0"
    x-transition:enter="transition ease-out duration-500"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @enable-help-hints.window="enableHints()"
    @disable-help-hints.window="disableHints()"
    @dismiss-beacon.window="dismissBeacon($event.detail.section)"
    class="pointer-events-none fixed inset-0 z-30"
>
    <!-- Dynamically created beacons will be positioned absolutely within this container -->
    <template x-for="(beacon, index) in activeBeacons" :key="beacon.section">
        <div
            class="absolute pointer-events-auto"
            :style="`top: ${beacon.top}px; left: ${beacon.left}px;`"
        >
            <!-- Beacon Container -->
            <div class="relative">
                <!-- Pulsating Dot Button (shown when NOT expanded) -->
                <button
                    x-show="expandedBeacon !== beacon.section"
                    @mouseenter="hoveredBeacon = beacon.section"
                    @mouseleave="hoveredBeacon = null"
                    @click.stop="expandedBeacon = beacon.section; hoveredBeacon = null"
                    class="relative flex items-center justify-center"
                    :title="beacon.title"
                >
                    <!-- Pulsating Dot -->
                    <span class="relative flex h-3.5 w-3.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-purple-500 ring-2 ring-white shadow-lg cursor-pointer hover:bg-purple-600 transition-colors"></span>
                    </span>
                </button>

                <!-- Hover Preview Tooltip (brief description on hover) -->
                <div
                    x-show="hoveredBeacon === beacon.section && expandedBeacon !== beacon.section"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    :class="{
                        'bottom-full mb-2 left-1/2 -translate-x-1/2': beacon.tooltipPosition === 'top',
                        'top-full mt-2 left-1/2 -translate-x-1/2': beacon.tooltipPosition === 'bottom',
                        'right-full mr-2 top-1/2 -translate-y-1/2': beacon.tooltipPosition === 'left',
                        'left-full ml-2 top-1/2 -translate-y-1/2': beacon.tooltipPosition === 'right'
                    }"
                    class="absolute z-50 px-3 py-2 bg-gray-900 text-white rounded-lg shadow-xl whitespace-nowrap"
                >
                    <!-- Arrow -->
                    <div
                        :class="{
                            'top-full -mt-1 left-1/2 -translate-x-1/2': beacon.tooltipPosition === 'top',
                            'bottom-full -mb-1 left-1/2 -translate-x-1/2': beacon.tooltipPosition === 'bottom',
                            'left-full -ml-1 top-1/2 -translate-y-1/2': beacon.tooltipPosition === 'left',
                            'right-full -mr-1 top-1/2 -translate-y-1/2': beacon.tooltipPosition === 'right'
                        }"
                        class="absolute w-2 h-2 bg-gray-900 transform rotate-45"
                    ></div>
                    <span class="text-xs font-medium" x-text="beacon.title"></span>
                    <span class="text-xs text-gray-400 ml-1">&middot; <span x-text="labels.click_for_details_label"></span></span>
                </div>

                <!-- Expanded Info Card (shown when clicked/toggled) -->
                <div
                    x-show="expandedBeacon === beacon.section"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-90"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-90"
                    :class="{
                        'bottom-full mb-3 left-1/2 -translate-x-1/2': beacon.tooltipPosition === 'top',
                        'top-full mt-3 left-1/2 -translate-x-1/2': beacon.tooltipPosition === 'bottom',
                        'right-full mr-3 top-1/2 -translate-y-1/2': beacon.tooltipPosition === 'left',
                        'left-full ml-3 top-1/2 -translate-y-1/2': beacon.tooltipPosition === 'right'
                    }"
                    class="absolute z-50 w-72 bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden"
                    @click.outside="expandedBeacon = null"
                >
                    <!-- Header with close button -->
                    <div class="flex items-center justify-between px-4 py-3 bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-purple-100">
                                <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            <h4 class="text-sm font-semibold text-gray-900" x-text="beacon.title"></h4>
                            <span x-show="beacon.video_url" class="px-1.5 py-0.5 bg-purple-100 text-purple-600 text-xs rounded-full flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z" />
                                </svg>
                            </span>
                        </div>
                        <button
                            @click.stop="expandedBeacon = null"
                            class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="p-4">
                        <p class="text-sm text-gray-600 leading-relaxed mb-4" x-text="beacon.description"></p>

                        <!-- Video Embed (if available) -->
                        <div x-show="beacon.video_url && showingVideo === beacon.section" x-cloak class="mb-4">
                            <div class="relative w-full rounded-lg overflow-hidden bg-gray-900" style="padding-top: 56.25%;">
                                <iframe
                                    :src="getEmbedUrl(beacon.video_url)"
                                    class="absolute inset-0 w-full h-full"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                ></iframe>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2">
                            <!-- Watch Video button (if video URL exists) -->
                            <button
                                x-show="beacon.video_url"
                                @click.stop="toggleVideo(beacon.section)"
                                :class="showingVideo === beacon.section ? 'bg-purple-100 text-purple-700' : 'bg-purple-600 text-white hover:bg-purple-700'"
                                class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z" />
                                </svg>
                                <span x-text="showingVideo === beacon.section ? labels.hide_video_label : labels.watch_video_label"></span>
                            </button>
                            <!-- Start Walkthrough button (if no video) -->
                            <button
                                x-show="!beacon.video_url"
                                @click.stop="startWalkthrough(beacon.section)"
                                class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span x-text="labels.start_walkthrough_label"></span>
                            </button>
                            <button
                                @click.stop="dismissBeaconLocal(beacon.section)"
                                class="p-2.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                                :title="labels.dismiss_hint_title"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Arrow pointing to beacon position -->
                    <div
                        :class="{
                            'top-full -mt-1.5 left-1/2 -translate-x-1/2': beacon.tooltipPosition === 'top',
                            'bottom-full -mb-1.5 left-1/2 -translate-x-1/2': beacon.tooltipPosition === 'bottom',
                            'left-full -ml-1.5 top-1/2 -translate-y-1/2': beacon.tooltipPosition === 'left',
                            'right-full -mr-1.5 top-1/2 -translate-y-1/2': beacon.tooltipPosition === 'right'
                        }"
                        class="absolute w-3 h-3 bg-white border-gray-200 transform rotate-45"
                        :style="{
                            'border-bottom': beacon.tooltipPosition === 'top' ? '1px solid #e5e7eb' : 'none',
                            'border-right': beacon.tooltipPosition === 'top' ? '1px solid #e5e7eb' : 'none',
                            'border-top': beacon.tooltipPosition === 'bottom' ? '1px solid #e5e7eb' : 'none',
                            'border-left': beacon.tooltipPosition === 'bottom' ? '1px solid #e5e7eb' : 'none'
                        }"
                    ></div>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
function autoHelpBeacons(labels = {}, fallbackTours = {}) {
    return {
        labels: labels,
        activeBeacons: [],
        hintsEnabled: false,
        hoveredBeacon: null,
        expandedBeacon: null,
        showingVideo: null,
        resizeObserver: null,
        mutationObserver: null,
        dismissedPages: [],
        hintsLoaded: false,

        // Help tours config for different pages (fallback data, overridden by API)
        helpTours: fallbackTours,

        init() {
            // Load dismissed beacons from localStorage
            try {
                const dismissed = localStorage.getItem('helpBeaconsDismissed');
                this.dismissedPages = dismissed ? JSON.parse(dismissed) : [];
            } catch (e) {
                this.dismissedPages = [];
            }

            // Load hints from API (with fallback to static data)
            this.loadHintsFromApi();

            // Listen for help overlay activation to hide beacons
            window.addEventListener('start-page-help', () => {
                this.hintsEnabled = false;
                this.expandedBeacon = null;
                // Dispatch event so the Help button knows hints are disabled
                window.dispatchEvent(new CustomEvent('help-hints-disabled'));
            });

            // Listen for help overlay close - don't auto-re-enable
            window.addEventListener('help-overlay-closed', () => {
                // Hints stay off - user must re-enable from menu
            });
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
                        this.helpTours = data.hints;
                    }
                }
            } catch (e) {
                // Fallback to static data (already loaded)
                console.debug('Using fallback help hints');
            }
            this.hintsLoaded = true;
        },

        enableHints() {
            this.hintsEnabled = true;
            this.createBeacons();
            this.setupObservers();
        },

        disableHints() {
            this.hintsEnabled = false;
            this.expandedBeacon = null;
            this.hoveredBeacon = null;
            this.showingVideo = null;
            this.activeBeacons = [];
            this.cleanupObservers();
        },

        startWalkthrough(section) {
            this.expandedBeacon = null;
            this.hintsEnabled = false;
            // Dispatch event so the Help button knows hints are disabled
            window.dispatchEvent(new CustomEvent('help-hints-disabled'));
            // Clear session storage for this page
            const pageKey = 'helpHints:' + window.location.pathname;
            sessionStorage.removeItem(pageKey);
            // Start the walkthrough
            window.dispatchEvent(new CustomEvent('start-page-help', {
                detail: { section: section },
                bubbles: true
            }));
        },

        toggleVideo(section) {
            if (this.showingVideo === section) {
                this.showingVideo = null;
            } else {
                this.showingVideo = section;
            }
        },

        getEmbedUrl(url) {
            if (!url) return '';

            // Loom
            if (url.includes('loom.com')) {
                const loomMatch = url.match(/loom\.com\/share\/([a-zA-Z0-9]+)/);
                if (loomMatch) {
                    return `https://www.loom.com/embed/${loomMatch[1]}`;
                }
            }

            // YouTube
            if (url.includes('youtube.com') || url.includes('youtu.be')) {
                let videoId = '';
                if (url.includes('youtu.be')) {
                    videoId = url.split('youtu.be/')[1]?.split('?')[0];
                } else {
                    const urlParams = new URLSearchParams(url.split('?')[1]);
                    videoId = urlParams.get('v');
                }
                if (videoId) {
                    return `https://www.youtube.com/embed/${videoId}`;
                }
            }

            // Vimeo
            if (url.includes('vimeo.com')) {
                const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);
                if (vimeoMatch) {
                    return `https://player.vimeo.com/video/${vimeoMatch[1]}`;
                }
            }

            // Return original URL as fallback
            return url;
        },

        dismissBeaconLocal(section) {
            this.expandedBeacon = null;
            this.dismissBeacon(section);
        },

        dismissBeacon(section) {
            const context = this.detectContext();
            const key = `${context}:${section}`;
            if (!this.dismissedPages.includes(key)) {
                this.dismissedPages.push(key);
                localStorage.setItem('helpBeaconsDismissed', JSON.stringify(this.dismissedPages));
            }
            this.activeBeacons = this.activeBeacons.filter(b => b.section !== section);
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
            return null;
        },

        createBeacons() {
            const context = this.detectContext();
            if (!context || !this.helpTours[context]) {
                this.activeBeacons = [];
                return;
            }

            const steps = this.helpTours[context];
            const beacons = [];

            steps.forEach(step => {
                if (!step.selector) return; // Skip intro steps without selectors

                // Skip dismissed beacons
                const key = `${context}:${step.section}`;
                if (this.dismissedPages.includes(key)) return;

                const element = this.findElement(step.selector);
                if (!element) return;

                const position = this.calculateBeaconPosition(element, step.position);
                if (!position) return;

                beacons.push({
                    section: step.section,
                    title: step.title,
                    description: step.description,
                    video_url: step.video_url || null,
                    top: position.top + (step.offset_y || 0),
                    left: position.left + (step.offset_x || 0),
                    tooltipPosition: this.getTooltipPosition(step.position)
                });
            });

            this.activeBeacons = beacons;
        },

        findElement(selector) {
            // Try multiple selectors (comma-separated fallbacks)
            const selectors = selector.split(',').map(s => s.trim());

            for (const sel of selectors) {
                try {
                    const el = document.querySelector(sel);
                    if (el && this.isElementVisible(el)) {
                        return el;
                    }
                } catch (e) {
                    // Invalid selector, try next
                }
            }
            return null;
        },

        isElementVisible(el) {
            const rect = el.getBoundingClientRect();
            const style = window.getComputedStyle(el);

            return (
                rect.width > 0 &&
                rect.height > 0 &&
                style.display !== 'none' &&
                style.visibility !== 'hidden' &&
                style.opacity !== '0'
            );
        },

        calculateBeaconPosition(element, preferredPosition) {
            const rect = element.getBoundingClientRect();
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

            // Position beacon at top-right corner of the element with offset
            let top = rect.top + scrollTop - 6;
            let left = rect.right + scrollLeft - 6;

            // Adjust based on preferred position
            switch (preferredPosition) {
                case 'top':
                    top = rect.top + scrollTop - 12;
                    left = rect.left + scrollLeft + (rect.width / 2);
                    break;
                case 'bottom':
                    top = rect.bottom + scrollTop - 6;
                    left = rect.left + scrollLeft + (rect.width / 2);
                    break;
                case 'left':
                    top = rect.top + scrollTop + (rect.height / 2) - 6;
                    left = rect.left + scrollLeft - 6;
                    break;
                case 'right':
                default:
                    top = rect.top + scrollTop + 8;
                    left = rect.right + scrollLeft - 20;
                    break;
            }

            // Ensure beacon stays within viewport bounds
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight + scrollTop;

            if (left < 10) left = 10;
            if (left > viewportWidth - 20) left = viewportWidth - 20;
            if (top < scrollTop + 10) top = scrollTop + 10;
            if (top > viewportHeight - 20) top = viewportHeight - 20;

            return { top, left };
        },

        getTooltipPosition(elementPosition) {
            // Tooltip should appear opposite to where beacon is placed relative to element
            const opposites = {
                'top': 'bottom',
                'bottom': 'top',
                'left': 'right',
                'right': 'left'
            };
            return opposites[elementPosition] || 'left';
        },

        setupObservers() {
            this.cleanupObservers();

            // Recalculate positions on resize
            this.resizeObserver = new ResizeObserver(() => {
                if (this.hintsEnabled) {
                    this.createBeacons();
                }
            });
            this.resizeObserver.observe(document.body);

            // Recalculate on DOM changes (e.g., Livewire updates)
            this.mutationObserver = new MutationObserver(() => {
                if (this.hintsEnabled) {
                    // Debounce to avoid excessive recalculations
                    clearTimeout(this._mutationTimeout);
                    this._mutationTimeout = setTimeout(() => {
                        this.createBeacons();
                    }, 300);
                }
            });

            this.mutationObserver.observe(document.body, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['class', 'style']
            });

            // Recalculate on scroll
            this._scrollHandler = () => {
                if (this.hintsEnabled) {
                    clearTimeout(this._scrollTimeout);
                    this._scrollTimeout = setTimeout(() => {
                        this.createBeacons();
                    }, 100);
                }
            };
            window.addEventListener('scroll', this._scrollHandler, { passive: true });
        },

        cleanupObservers() {
            if (this.resizeObserver) {
                this.resizeObserver.disconnect();
                this.resizeObserver = null;
            }
            if (this.mutationObserver) {
                this.mutationObserver.disconnect();
                this.mutationObserver = null;
            }
            if (this._scrollHandler) {
                window.removeEventListener('scroll', this._scrollHandler);
                this._scrollHandler = null;
            }
        }
    };
}
</script>
