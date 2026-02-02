@props(['user' => auth()->user()])

<div
    x-data="supportTicketModal()"
    x-cloak
    @open-support-modal.window="open($event.detail?.context)"
>
    <!-- Modal Backdrop -->
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 z-50"
        @click="close()"
    ></div>

    <!-- Modal Content -->
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="close()"
    >
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg" @click.stop>
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Contact Support</h2>
                    <p class="text-sm text-gray-500">We're here to help. Tell us what you need.</p>
                </div>
                <button
                    @click="close()"
                    class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit()" class="p-6 space-y-4">
                <!-- Success Message -->
                <div
                    x-show="success"
                    x-transition
                    class="p-4 bg-green-50 border border-green-200 rounded-lg"
                >
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="font-medium text-green-800">Message sent!</p>
                            <p class="text-sm text-green-700">We'll get back to you as soon as possible.</p>
                        </div>
                    </div>
                </div>

                <div x-show="!success" class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label for="support-name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input
                            type="text"
                            id="support-name"
                            x-model="form.name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                            :class="{ 'border-red-300': errors.name }"
                            required
                        >
                        <p x-show="errors.name" x-text="errors.name" class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="support-email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input
                            type="email"
                            id="support-email"
                            x-model="form.email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                            :class="{ 'border-red-300': errors.email }"
                            required
                        >
                        <p x-show="errors.email" x-text="errors.email" class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <!-- Organization (read-only if logged in) -->
                    @if($user && $user->organization)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Organization</label>
                        <input
                            type="text"
                            value="{{ $user->organization->name }}"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-600"
                            readonly
                        >
                    </div>
                    @endif

                    <!-- Subject (optional) -->
                    <div>
                        <label for="support-subject" class="block text-sm font-medium text-gray-700 mb-1">
                            Subject <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <input
                            type="text"
                            id="support-subject"
                            x-model="form.subject"
                            placeholder="What's this about?"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                        >
                    </div>

                    <!-- Message -->
                    <div>
                        <label for="support-message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                        <textarea
                            id="support-message"
                            x-model="form.message"
                            rows="4"
                            placeholder="Describe what you need help with..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 resize-none"
                            :class="{ 'border-red-300': errors.message }"
                            required
                        ></textarea>
                        <p x-show="errors.message" x-text="errors.message" class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <!-- Context info (hidden, auto-captured) -->
                    <input type="hidden" x-model="form.page_url">
                    <input type="hidden" x-model="form.page_context">
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="close()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                    >
                        <span x-text="success ? 'Close' : 'Cancel'"></span>
                    </button>
                    <button
                        x-show="!success"
                        type="submit"
                        :disabled="submitting"
                        class="px-4 py-2 text-sm font-medium text-white bg-orange-500 rounded-lg hover:bg-orange-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span x-show="!submitting">Send Message</span>
                        <span x-show="submitting" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Sending...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function supportTicketModal() {
    return {
        isOpen: false,
        submitting: false,
        success: false,
        form: {
            name: @js($user?->name ?? ''),
            email: @js($user?->email ?? ''),
            subject: '',
            message: '',
            page_url: '',
            page_context: ''
        },
        errors: {},

        open(context = null) {
            this.isOpen = true;
            this.success = false;
            this.errors = {};
            this.form.page_url = window.location.href;
            this.form.page_context = context || this.detectContext();
            this.form.subject = '';
            this.form.message = '';
            document.body.style.overflow = 'hidden';
        },

        close() {
            this.isOpen = false;
            document.body.style.overflow = '';
        },

        detectContext() {
            // Try to detect context from URL/route
            const path = window.location.pathname;
            if (path.includes('/plans')) return 'strategic-plans';
            if (path.includes('/surveys')) return 'surveys';
            if (path.includes('/alerts')) return 'alerts';
            if (path.includes('/students')) return 'students';
            if (path.includes('/courses')) return 'courses';
            if (path.includes('/resources')) return 'resources';
            if (path.includes('/help')) return 'help-center';
            if (path.includes('/admin')) return 'admin';
            return 'general';
        },

        async submit() {
            this.submitting = true;
            this.errors = {};

            try {
                const response = await fetch('/api/support-tickets', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok) {
                    this.success = true;
                } else {
                    this.errors = data.errors || { message: 'Something went wrong. Please try again.' };
                }
            } catch (error) {
                console.error('Failed to submit ticket:', error);
                this.errors = { message: 'Network error. Please try again.' };
            } finally {
                this.submitting = false;
            }
        }
    };
}
</script>
