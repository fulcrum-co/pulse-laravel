{{-- Inline Tooltip Creator - Admin tool to create tooltips by clicking on elements --}}
{{-- Only enabled when admin toggles "Create Tooltips" from Help dropdown --}}
<div
    x-data="inlineTooltipCreator()"
    x-show="active"
    x-cloak
    @enable-tooltip-creator.window="enable()"
    @disable-tooltip-creator.window="disable()"
    class="fixed inset-0 z-[100]"
>
    {{-- Floating Toolbar --}}
    <div
        x-show="active"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-full"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="fixed top-0 left-0 right-0 z-[101] bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg"
    >
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-white/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </span>
                <div>
                    <p class="font-semibold">Tooltip Creator Mode</p>
                    <p class="text-sm text-orange-100">Click on any element to add a tooltip</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-orange-100 hidden sm:inline">
                    Page: <span class="font-medium text-white" x-text="currentContext"></span>
                </span>
                <button
                    @click="disable()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Exit Creator
                </button>
            </div>
        </div>
    </div>

    {{-- Highlight Overlay (shown when hovering) --}}
    <div
        x-show="hoveredElement && !showForm"
        x-transition
        class="fixed pointer-events-none border-2 border-dashed border-purple-500 rounded-lg bg-purple-500/10 z-[99]"
        :style="`top: ${highlightRect.top}px; left: ${highlightRect.left}px; width: ${highlightRect.width}px; height: ${highlightRect.height}px;`"
    >
        <div class="absolute -top-8 left-0 bg-purple-600 text-white text-xs px-2 py-1 rounded whitespace-nowrap">
            <span x-text="hoveredElementInfo"></span>
        </div>
    </div>

    {{-- Click Capture Layer (invisible, captures clicks) --}}
    <div
        x-show="active && !showForm"
        @click.stop="handleClick($event)"
        @mousemove="handleMouseMove($event)"
        class="fixed inset-0 cursor-crosshair z-[98]"
        style="top: 60px;"
    ></div>

    {{-- Tooltip Creation Form --}}
    <div
        x-show="showForm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed z-[102] w-96 bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden"
        :style="`top: ${formPosition.top}px; left: ${formPosition.left}px;`"
        @click.outside="cancelForm()"
    >
        {{-- Form Header --}}
        <div class="px-5 py-4 bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-purple-100">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="font-semibold text-gray-900">Add New Tooltip</h3>
                        <p class="text-xs text-gray-500">For: <code class="bg-gray-100 px-1 rounded" x-text="truncateSelector(generatedSelector)"></code></p>
                    </div>
                </div>
                <button @click="cancelForm()" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Form Body --}}
        <form @submit.prevent="saveTooltip()" class="p-5 space-y-4">
            {{-- Title --}}
            <div>
                <label for="tooltip-title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input
                    type="text"
                    id="tooltip-title"
                    x-model="formData.title"
                    placeholder="e.g., Search Reports"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm"
                    required
                    autofocus
                >
            </div>

            {{-- Description --}}
            <div>
                <label for="tooltip-description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea
                    id="tooltip-description"
                    x-model="formData.description"
                    rows="3"
                    placeholder="Explain what this feature does..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm resize-none"
                    required
                ></textarea>
            </div>

            {{-- Position --}}
            <div>
                <label for="tooltip-position" class="block text-sm font-medium text-gray-700 mb-1">Tooltip Position</label>
                <select
                    id="tooltip-position"
                    x-model="formData.position"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm"
                >
                    <option value="top">Above element</option>
                    <option value="bottom">Below element</option>
                    <option value="left">Left of element</option>
                    <option value="right">Right of element</option>
                </select>
            </div>

            {{-- Video URL (Optional) --}}
            <div>
                <label for="tooltip-video" class="block text-sm font-medium text-gray-700 mb-1">
                    Video URL <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <input
                    type="url"
                    id="tooltip-video"
                    x-model="formData.videoUrl"
                    placeholder="https://www.loom.com/share/..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm"
                >
                <p class="mt-1 text-xs text-gray-400">Supports Loom, YouTube, Vimeo</p>
            </div>

            {{-- Error Message --}}
            <div x-show="errorMessage" x-cloak class="p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-600" x-text="errorMessage"></p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <button
                    type="button"
                    @click="cancelForm()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    :disabled="saving"
                    class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2"
                >
                    <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="saving ? 'Saving...' : 'Create Tooltip'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function inlineTooltipCreator() {
    return {
        active: false,
        hoveredElement: null,
        highlightRect: { top: 0, left: 0, width: 0, height: 0 },
        hoveredElementInfo: '',
        showForm: false,
        formPosition: { top: 100, left: 100 },
        selectedElement: null,
        generatedSelector: '',
        currentContext: '',
        saving: false,
        errorMessage: '',

        formData: {
            title: '',
            description: '',
            position: 'bottom',
            videoUrl: ''
        },

        enable() {
            this.active = true;
            this.currentContext = this.detectContext();
            document.body.style.overflow = 'hidden'; // Prevent scrolling
            // Add padding to body to account for toolbar
            document.body.style.paddingTop = '60px';
        },

        disable() {
            this.active = false;
            this.showForm = false;
            this.hoveredElement = null;
            this.selectedElement = null;
            this.resetForm();
            document.body.style.overflow = '';
            document.body.style.paddingTop = '';
            sessionStorage.removeItem('tooltipCreatorMode');
            window.dispatchEvent(new CustomEvent('tooltip-creator-disabled'));
        },

        handleMouseMove(event) {
            if (this.showForm) return;

            // Find element under cursor (excluding our overlay elements)
            const elementsUnder = document.elementsFromPoint(event.clientX, event.clientY);
            const targetElement = elementsUnder.find(el =>
                !el.closest('[x-data="inlineTooltipCreator()"]') &&
                el.tagName !== 'HTML' &&
                el.tagName !== 'BODY'
            );

            if (targetElement && targetElement !== this.hoveredElement) {
                this.hoveredElement = targetElement;
                const rect = targetElement.getBoundingClientRect();
                this.highlightRect = {
                    top: rect.top,
                    left: rect.left,
                    width: rect.width,
                    height: rect.height
                };
                this.hoveredElementInfo = this.getElementInfo(targetElement);
            }
        },

        handleClick(event) {
            if (this.showForm) return;

            // Find element under cursor
            const elementsUnder = document.elementsFromPoint(event.clientX, event.clientY);
            const targetElement = elementsUnder.find(el =>
                !el.closest('[x-data="inlineTooltipCreator()"]') &&
                el.tagName !== 'HTML' &&
                el.tagName !== 'BODY'
            );

            if (!targetElement) return;

            this.selectedElement = targetElement;
            this.generatedSelector = this.generateSelector(targetElement);

            // Position form near the click, but keep it visible
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            let left = event.clientX + 20;
            let top = event.clientY;

            // Adjust if form would go off screen
            if (left + 400 > viewportWidth) {
                left = event.clientX - 420;
            }
            if (top + 450 > viewportHeight) {
                top = viewportHeight - 470;
            }
            if (top < 80) top = 80;
            if (left < 20) left = 20;

            this.formPosition = { top, left };
            this.showForm = true;
            this.hoveredElement = null;

            // Auto-generate title from element
            this.formData.title = this.suggestTitle(targetElement);
        },

        getElementInfo(el) {
            let info = el.tagName.toLowerCase();
            if (el.id) info += `#${el.id}`;
            if (el.className && typeof el.className === 'string') {
                const classes = el.className.split(' ')
                    .filter(c => c && !c.startsWith('hover:') && !c.startsWith('focus:'))
                    .slice(0, 2)
                    .join('.');
                if (classes) info += `.${classes}`;
            }
            return info;
        },

        generateSelector(el) {
            // Priority 1: data-help attribute (best)
            if (el.dataset && el.dataset.help) {
                return `[data-help="${el.dataset.help}"]`;
            }

            // Priority 2: Unique ID
            if (el.id && this.isUniqueSelector(`#${el.id}`)) {
                return `#${el.id}`;
            }

            // Priority 3: data-testid or data-cy
            if (el.dataset && el.dataset.testid) {
                return `[data-testid="${el.dataset.testid}"]`;
            }

            // Priority 4: Unique class combination
            if (el.className && typeof el.className === 'string') {
                const classes = el.className.split(' ')
                    .filter(c => c && !c.startsWith('hover:') && !c.startsWith('focus:') && !c.includes(':'))
                    .slice(0, 3);

                if (classes.length > 0) {
                    const classSelector = '.' + classes.join('.');
                    if (this.isUniqueSelector(classSelector)) {
                        return classSelector;
                    }
                }
            }

            // Priority 5: Path-based selector
            return this.getPathSelector(el);
        },

        isUniqueSelector(selector) {
            try {
                return document.querySelectorAll(selector).length === 1;
            } catch (e) {
                return false;
            }
        },

        getPathSelector(el) {
            const path = [];
            let current = el;

            while (current && current !== document.body && path.length < 4) {
                let selector = current.tagName.toLowerCase();

                // Add ID if exists
                if (current.id) {
                    selector += `#${current.id}`;
                    path.unshift(selector);
                    break;
                }

                // Add nth-child for uniqueness
                const parent = current.parentElement;
                if (parent) {
                    const siblings = Array.from(parent.children).filter(c => c.tagName === current.tagName);
                    if (siblings.length > 1) {
                        const index = siblings.indexOf(current) + 1;
                        selector += `:nth-child(${index})`;
                    }
                }

                path.unshift(selector);
                current = parent;
            }

            return path.join(' > ');
        },

        suggestTitle(el) {
            // Try to extract meaningful title from element
            const textContent = el.textContent?.trim().slice(0, 50);
            const ariaLabel = el.getAttribute('aria-label');
            const title = el.getAttribute('title');
            const placeholder = el.getAttribute('placeholder');

            return ariaLabel || title || placeholder || textContent || '';
        },

        truncateSelector(selector) {
            if (selector.length > 35) {
                return selector.slice(0, 32) + '...';
            }
            return selector;
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
            return 'dashboard'; // Default
        },

        generateSectionId() {
            // Generate a unique section ID from the selector or title
            const base = this.formData.title
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-|-$/g, '')
                .slice(0, 30);

            return base || `tooltip-${Date.now()}`;
        },

        resetForm() {
            this.formData = {
                title: '',
                description: '',
                position: 'bottom',
                videoUrl: ''
            };
            this.errorMessage = '';
        },

        cancelForm() {
            this.showForm = false;
            this.selectedElement = null;
            this.resetForm();
        },

        async saveTooltip() {
            this.saving = true;
            this.errorMessage = '';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                if (!csrfToken) {
                    throw new Error('CSRF token not found');
                }

                const response = await fetch('/api/help/hints', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        page_context: this.currentContext,
                        section: this.generateSectionId(),
                        selector: this.generatedSelector,
                        title: this.formData.title,
                        description: this.formData.description,
                        position: this.formData.position,
                        video_url: this.formData.videoUrl || null
                    })
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `Failed to save tooltip (${response.status})`);
                }

                // Success! Show toast and reset
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: 'Tooltip created successfully!',
                        type: 'success'
                    }
                }));

                this.showForm = false;
                this.selectedElement = null;
                this.resetForm();

                // Optionally refresh beacons if they're visible
                window.dispatchEvent(new CustomEvent('refresh-help-beacons'));

            } catch (error) {
                console.error('Failed to save tooltip:', error);
                this.errorMessage = error.message || 'Failed to save tooltip. Please try again.';
            } finally {
                this.saving = false;
            }
        }
    };
}
</script>
