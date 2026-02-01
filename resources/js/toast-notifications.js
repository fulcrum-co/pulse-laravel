/**
 * Toast Notifications Alpine Component
 * Handles real-time notification popups via Laravel Echo/Reverb
 */
document.addEventListener('alpine:init', () => {
    Alpine.data('toastNotifications', (userId, userPrefs) => ({
        toasts: [],
        userPrefs: userPrefs || { enabled: true, priority_threshold: 'low' },
        maxToasts: 5,
        autoDismissMs: 6000,

        init() {
            console.log('Toast init: Starting...');

            if (typeof window.Echo === 'undefined') {
                console.log('Toast: Echo not available');
                return;
            }

            console.log('Toast init: Subscribing to user.' + userId);

            window.Echo.private('user.' + userId)
                .listen('.notification.created', (e) => {
                    console.log('Toast: Received notification!', e);
                    this.handleNotification(e);

                    // Update badge
                    window.dispatchEvent(new CustomEvent('notification-badge-update', {
                        detail: { count: e.unread_count }
                    }));

                    // Notify Livewire
                    if (typeof Livewire !== 'undefined') {
                        Livewire.dispatch('notification-received');
                    }
                });

            console.log('Toast init: Listener registered');
        },

        handleNotification(data) {
            if (!this.userPrefs.enabled) {
                console.log('Toast: Disabled by user preferences');
                return;
            }
            if (!this.meetsThreshold(data.priority, this.userPrefs.priority_threshold)) {
                console.log('Toast: Below priority threshold');
                return;
            }

            const toast = {
                id: data.id || Date.now(),
                type: data.type,
                category: data.category,
                priority: data.priority,
                title: data.title,
                body: data.body,
                icon: data.icon,
                action_url: data.action_url,
                action_label: data.action_label,
                created_at: data.created_at,
                visible: true,
                progress: 100,
            };

            console.log('Toast: Adding toast to list', toast);

            // Use spread to trigger Alpine reactivity
            this.toasts = [toast, ...this.toasts].slice(0, this.maxToasts);

            // Start progress bar after DOM update
            this.$nextTick(() => this.startProgressBar(toast));
        },

        startProgressBar(toast) {
            const intervalMs = 50;
            const decrement = (intervalMs / this.autoDismissMs) * 100;

            const interval = setInterval(() => {
                const t = this.toasts.find(x => x.id === toast.id);
                if (!t) {
                    clearInterval(interval);
                    return;
                }
                t.progress -= decrement;
                if (t.progress <= 0) {
                    clearInterval(interval);
                    this.dismiss(t);
                }
            }, intervalMs);
        },

        meetsThreshold(priority, threshold) {
            const levels = { low: 0, normal: 1, high: 2, urgent: 3, critical: 3 };
            return (levels[priority] ?? 1) >= (levels[threshold] ?? 0);
        },

        dismiss(toast) {
            toast.visible = false;
            setTimeout(() => {
                this.toasts = this.toasts.filter(t => t.id !== toast.id);
            }, 300);
        },

        handleClick(toast) {
            if (toast.action_url) {
                window.location.href = toast.action_url;
            }
            this.dismiss(toast);
        },

        getPriorityClasses(priority) {
            const map = {
                urgent: 'bg-red-100 text-red-600',
                critical: 'bg-red-100 text-red-600',
                high: 'bg-amber-100 text-amber-600',
                normal: 'bg-blue-100 text-blue-600',
                low: 'bg-gray-100 text-gray-600',
            };
            return map[priority] || map.normal;
        },

        getPriorityBarClass(priority) {
            const map = {
                urgent: 'bg-red-500',
                critical: 'bg-red-500',
                high: 'bg-amber-500',
                normal: 'bg-blue-500',
                low: 'bg-gray-400',
            };
            return map[priority] || map.normal;
        },

        formatTime(isoString) {
            if (!isoString) return 'Just now';
            const date = new Date(isoString);
            const diffMs = Date.now() - date;
            const diffMins = Math.floor(diffMs / 60000);
            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return diffMins + 'm ago';
            const diffHours = Math.floor(diffMins / 60);
            if (diffHours < 24) return diffHours + 'h ago';
            return date.toLocaleDateString();
        }
    }));
});
