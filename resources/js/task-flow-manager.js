/**
 * Task Flow Manager
 * Manages a queue of notification tasks with persistent state via sessionStorage
 * Provides HubSpot-style "Start your daily tasks" flow
 */

console.log('Task flow manager: Script loaded');

// Global function to start task flow - can be called from anywhere
window.startTaskFlow = function(notifications) {
    console.log('Task flow: startTaskFlow called');
    console.log('Task flow: notifications received:', notifications);
    console.log('Task flow: Starting with', notifications?.length, 'tasks');

    if (!notifications || notifications.length === 0) {
        console.log('Task flow: No notifications to process');
        return;
    }

    // Save to sessionStorage
    sessionStorage.setItem('taskFlowQueue', JSON.stringify({
        queue: notifications,
        currentIndex: 0
    }));

    // Navigate to first task
    const firstTask = notifications[0];
    if (firstTask && firstTask.action_url) {
        console.log('Task flow: Navigating to', firstTask.action_url);
        window.location.href = firstTask.action_url;
    }
};

// Alpine component for the task flow bar UI
document.addEventListener('alpine:init', () => {
    Alpine.data('taskFlowManager', () => ({
        queue: [],
        currentIndex: 0,
        isActive: false,

        init() {
            console.log('Task flow bar: Initializing...');

            // Restore from sessionStorage on page load
            const saved = sessionStorage.getItem('taskFlowQueue');
            if (saved) {
                try {
                    const data = JSON.parse(saved);
                    this.queue = data.queue || [];
                    this.currentIndex = data.currentIndex || 0;
                    this.isActive = this.queue.length > 0;
                    console.log('Task flow bar: Restored state, active:', this.isActive, 'task', this.currentIndex + 1, 'of', this.queue.length);
                } catch (e) {
                    console.error('Task flow bar: Failed to restore state', e);
                    this.clearState();
                }
            }

            // Listen for task completion events from destination pages
            window.addEventListener('task-completed', () => this.completeCurrentTask());

            // Listen for Livewire events
            if (typeof Livewire !== 'undefined') {
                Livewire.on('task-completed', () => this.completeCurrentTask());
            }
        },

        get currentTask() {
            return this.queue[this.currentIndex] || null;
        },

        get progress() {
            return this.queue.length ? ((this.currentIndex + 1) / this.queue.length) * 100 : 0;
        },

        get remainingCount() {
            return Math.max(0, this.queue.length - this.currentIndex - 1);
        },

        async completeCurrentTask() {
            // Mark notification as resolved via API, then navigate
            if (this.currentTask) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                try {
                    const response = await fetch(`/api/notifications/${this.currentTask.id}/resolve`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                    });

                    if (response.ok) {
                        const data = await response.json();
                        // Update the header notification badge
                        if (data.unread_count !== undefined) {
                            window.dispatchEvent(new CustomEvent('notification-badge-update', {
                                detail: { count: data.unread_count }
                            }));
                        }
                    }
                } catch (err) {
                    console.error('Task flow: Failed to resolve notification', err);
                }
            }
            this.goToNext();
        },

        skipCurrentTask() {
            this.goToNext();
        },

        goToNext() {
            if (this.currentIndex < this.queue.length - 1) {
                this.currentIndex++;
                this.saveState();
                this.navigateToCurrent();
            } else {
                this.exitFlow(true); // completed all
            }
        },

        goToPrevious() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
                this.saveState();
                this.navigateToCurrent();
            }
        },

        navigateToCurrent() {
            if (this.currentTask && this.currentTask.action_url) {
                console.log('Task flow: Navigating to', this.currentTask.action_url, 'Task:', this.currentTask.title);
                window.location.href = this.currentTask.action_url;
            } else {
                console.error('Task flow: No action_url for current task', this.currentTask);
                // Skip to next task if current has no URL
                if (this.currentIndex < this.queue.length - 1) {
                    this.currentIndex++;
                    this.saveState();
                    this.navigateToCurrent();
                } else {
                    this.exitFlow(true);
                }
            }
        },

        exitFlow(completed = false) {
            this.isActive = false;
            this.queue = [];
            this.currentIndex = 0;
            this.clearState();

            // Redirect to alerts with completion status
            const url = completed ? '/alerts?completed=1' : '/alerts';
            window.location.href = url;
        },

        saveState() {
            sessionStorage.setItem('taskFlowQueue', JSON.stringify({
                queue: this.queue,
                currentIndex: this.currentIndex
            }));
        },

        clearState() {
            sessionStorage.removeItem('taskFlowQueue');
        },

        getPriorityClasses(priority) {
            const map = {
                urgent: 'bg-red-100 text-red-700',
                high: 'bg-amber-100 text-amber-700',
                normal: 'bg-blue-100 text-blue-700',
                low: 'bg-gray-100 text-gray-700',
            };
            return map[priority] || map.normal;
        }
    }));
});
