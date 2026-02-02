@props(['user' => auth()->user()])

{{-- Page Help Overlay - Guided walkthrough for current page --}}
<div
    x-data="pageHelpOverlay()"
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
                    <span class="text-xs text-gray-500">of <span x-text="steps.length"></span></span>
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
                    ← Back
                </button>
                <div x-show="currentIndex === 0"></div>

                <button
                    @click="nextStep()"
                    class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors"
                    x-text="currentIndex === steps.length - 1 ? 'Got it!' : 'Next →'"
                ></button>
            </div>
        </div>
    </div>
</div>

<script>
function pageHelpOverlay() {
    return {
        active: false,
        steps: [],
        currentIndex: 0,
        currentStep: null,
        targetElement: null,
        tooltipPosition: 'bottom',

        // Define help tours for different pages (each step has a section identifier)
        helpTours: {
            'dashboard': [
                {
                    section: 'intro',
                    selector: null,
                    title: 'Welcome to your Dashboard',
                    description: 'This is your central hub for monitoring student wellness across your organization. Let me show you around.',
                    position: 'center'
                },
                {
                    section: 'selector',
                    selector: '[data-help="dashboard-selector"]',
                    title: 'Dashboard Selector',
                    description: 'Switch between different dashboards or create new ones. Each dashboard can have its own set of customized widgets.',
                    tip: 'Create multiple dashboards for different purposes - one for daily monitoring, one for weekly reviews.',
                    position: 'bottom'
                },
                {
                    section: 'actions',
                    selector: '[data-help="dashboard-actions"]',
                    title: 'Dashboard Actions',
                    description: 'Add widgets, set date ranges, and manage your dashboard settings. Customize your view to focus on what matters most.',
                    position: 'bottom'
                },
                {
                    section: 'date-range',
                    selector: '[data-help="date-range"]',
                    title: 'Date Range Filter',
                    description: 'Filter your dashboard data by week, month, or quarter to see trends over different time periods.',
                    tip: 'Use the quarter view for strategic planning and the week view for daily operations.',
                    position: 'bottom'
                },
                {
                    section: 'widgets',
                    selector: '[data-help="widgets-grid"]',
                    title: 'Dashboard Widgets',
                    description: 'Your customizable widgets display key metrics, charts, and lists. Add, remove, or rearrange widgets to build your perfect dashboard.',
                    tip: 'Click the Add Widget button to explore available widget types.',
                    position: 'top'
                }
            ],
            'surveys': [
                {
                    section: 'intro',
                    selector: null,
                    title: 'Surveys & Assessments',
                    description: 'Create and manage wellness surveys to check in with your students. This is where you\'ll build, distribute, and analyze survey results.',
                    position: 'center'
                },
                {
                    section: 'create',
                    selector: '[data-help="create-survey"], button:contains("Create"), a[href*="create"]',
                    title: 'Creating Surveys',
                    description: 'Click here to build a new survey. You can choose from templates or create custom questions.',
                    tip: 'Start with a template to save time!',
                    position: 'bottom'
                },
                {
                    section: 'list',
                    selector: '[data-help="survey-list"], .survey-list, table',
                    title: 'Your Surveys',
                    description: 'View all your surveys here. You can see their status, response rates, and take quick actions.',
                    position: 'top'
                }
            ],
            'alerts': [
                {
                    section: 'intro',
                    selector: null,
                    title: 'Alert Management',
                    description: 'Alerts help you identify students who may need support. This system monitors survey responses and flags concerning patterns.',
                    position: 'center'
                },
                {
                    section: 'filters',
                    selector: '[data-help="alert-filters"], .filters, [class*="filter"]',
                    title: 'Filter Alerts',
                    description: 'Use these filters to focus on specific alert types, severity levels, or time periods.',
                    position: 'bottom'
                },
                {
                    section: 'list',
                    selector: '[data-help="alert-list"], .alert-list, [class*="alert-item"]',
                    title: 'Alert Details',
                    description: 'Each alert shows the student, the trigger, and recommended actions. Click to view more details.',
                    tip: 'Prioritize high-severity alerts first.',
                    position: 'top'
                }
            ],
            'contacts': [
                {
                    section: 'intro',
                    selector: null,
                    title: 'Contact Management',
                    description: 'View and manage all contacts including students, teachers, and parents. Track their information and engagement.',
                    position: 'center'
                },
                {
                    section: 'search',
                    selector: '[data-help="search-contacts"], input[type="search"], .search',
                    title: 'Find Contacts',
                    description: 'Search by name, email, or other criteria to quickly find specific contacts.',
                    position: 'bottom'
                },
                {
                    section: 'list',
                    selector: '[data-help="contact-list"], .contact-list, table',
                    title: 'Contact Directory',
                    description: 'Browse all contacts in your organization. Click on a contact to view their full profile.',
                    position: 'top'
                }
            ],
            'plans': [
                {
                    section: 'intro',
                    selector: null,
                    title: 'Strategic Plans',
                    description: 'Create and track organizational wellness goals and initiatives. Strategic plans help align your team around shared objectives.',
                    position: 'center'
                },
                {
                    section: 'search',
                    selector: '[data-help="search-plans"], input[placeholder*="Search"]',
                    title: 'Search Plans',
                    description: 'Quickly find specific plans by searching for keywords in the plan name or description.',
                    position: 'bottom'
                },
                {
                    section: 'filters',
                    selector: '[data-help="plan-filters"], select',
                    title: 'Filter Plans',
                    description: 'Filter plans by type (Growth, Strategic, Action, etc.) or status (Active, Draft, Completed).',
                    position: 'bottom'
                },
                {
                    section: 'list',
                    selector: '[data-help="plan-list"], .plan-list, [class*="plan-card"]',
                    title: 'Your Plans',
                    description: 'View all strategic plans here. Each card shows the plan name, progress, goals, and key dates.',
                    tip: 'Click on any plan card to view details and track progress.',
                    position: 'top'
                }
            ],
            'resources': [
                {
                    section: 'intro',
                    selector: null,
                    title: 'Resource Library',
                    description: 'Access and share educational resources, intervention materials, and support documents with your team.',
                    position: 'center'
                },
                {
                    section: 'search',
                    selector: '[data-help="search-resources"]',
                    title: 'Search Resources',
                    description: 'Search across all resource types including content, providers, programs, and courses to quickly find what you need.',
                    position: 'bottom'
                },
                {
                    section: 'filters',
                    selector: '[data-help="resource-filters"]',
                    title: 'Filter & Sort',
                    description: 'Use the sidebar to filter by category, content type, and sort order to narrow down your resource search.',
                    tip: 'Combine multiple filters to find exactly what you\'re looking for.',
                    position: 'right'
                },
                {
                    section: 'categories',
                    selector: '[data-help="resource-categories"]',
                    title: 'Resource Categories',
                    description: 'Browse resources by category - Content, Providers, Programs, and Courses. Click any card to explore that category.',
                    position: 'top'
                }
            ],
            'collect': [
                {
                    section: 'intro',
                    selector: null,
                    title: 'Data Collections',
                    description: 'Set up recurring data collection to systematically gather progress monitoring data, check-ins, and insights from students, staff, or parents.',
                    position: 'center'
                },
                {
                    section: 'search',
                    selector: '[data-help="search-collections"]',
                    title: 'Search Collections',
                    description: 'Quickly find specific data collections by searching for keywords in the collection name.',
                    position: 'bottom'
                },
                {
                    section: 'filters',
                    selector: '[data-help="collection-filters"]',
                    title: 'Filter Collections',
                    description: 'Filter by status (Active, Paused, Draft) or type (Recurring, One-time, Event-triggered) to narrow your view.',
                    tip: 'Use the status filter to focus on active collections that need attention.',
                    position: 'bottom'
                },
                {
                    section: 'list',
                    selector: '[data-help="collection-list"]',
                    title: 'Your Collections',
                    description: 'View all data collections here. Each card shows session counts, entries, and next scheduled run time.',
                    tip: 'Click on a collection to view its sessions and collected data.',
                    position: 'top'
                }
            ],
            'distribute': [
                {
                    section: 'intro',
                    selector: null,
                    title: 'Distributions',
                    description: 'Send reports and messages to targeted groups via email or SMS. Set up one-time or recurring campaigns.',
                    position: 'center'
                },
                {
                    section: 'search',
                    selector: '[data-help="search-distributions"]',
                    title: 'Search Distributions',
                    description: 'Quickly find specific distributions by searching for keywords in the distribution name.',
                    position: 'bottom'
                },
                {
                    section: 'filters',
                    selector: '[data-help="distribution-filters"]',
                    title: 'Filter Distributions',
                    description: 'Filter by status or channel (Email, SMS) to find specific distributions quickly.',
                    tip: 'Filter by channel to review all email or SMS campaigns separately.',
                    position: 'bottom'
                },
                {
                    section: 'list',
                    selector: '[data-help="distribution-list"]',
                    title: 'Your Distributions',
                    description: 'Track all distributions here. See delivery counts, recipient lists, and next scheduled send time.',
                    tip: 'Hover over a distribution card to see quick action buttons.',
                    position: 'top'
                }
            ],
            'reports': [
                {
                    section: 'intro',
                    selector: null,
                    title: 'Reports',
                    description: 'Build beautiful, data-driven reports with our drag-and-drop editor. Share insights with stakeholders.',
                    position: 'center'
                },
                {
                    section: 'search',
                    selector: '[data-help="search-reports"]',
                    title: 'Search Reports',
                    description: 'Quickly find specific reports by searching for keywords in the report name.',
                    position: 'bottom'
                },
                {
                    section: 'filters',
                    selector: '[data-help="report-filters"]',
                    title: 'Filter Reports',
                    description: 'Filter reports by status (Draft or Published) and switch between grid, list, and table views.',
                    tip: 'Use the Published filter to see reports ready for sharing.',
                    position: 'bottom'
                },
                {
                    section: 'list',
                    selector: '[data-help="report-list"]',
                    title: 'Your Reports',
                    description: 'View all your reports here. Click to edit, duplicate, or delete reports. Published reports can be shared with stakeholders.',
                    tip: 'Click "Duplicate" to create a new report based on an existing template.',
                    position: 'top'
                }
            ],
            'general': [
                {
                    section: 'intro',
                    selector: null,
                    title: 'Page Help',
                    description: 'Welcome! This guided tour will help you understand the features on this page.',
                    position: 'center'
                },
                {
                    section: 'navigation',
                    selector: 'header, [class*="header"]',
                    title: 'Navigation',
                    description: 'Use the header to navigate between different sections of Pulse. The sidebar provides quick access to all main features.',
                    position: 'bottom'
                }
            ]
        },

        startHelp(context = null, section = null) {
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

        detectContext() {
            const path = window.location.pathname;
            if (path.includes('/plans')) return 'plans';
            if (path.includes('/surveys')) return 'surveys';
            if (path.includes('/alerts')) return 'alerts';
            if (path.includes('/students') || path.includes('/contacts')) return 'contacts';
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
