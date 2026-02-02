@use('App\Services\RolePermissions')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $report?->report_name ?? 'New Report' }} - Report Builder - Pulse</title>

    <!-- Microsoft Clarity -->
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "v99lylydfx");
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        pulse: {
                            orange: {
                                50: '#FFF7ED',
                                100: '#FFEDD5',
                                200: '#FED7AA',
                                300: '#FDBA74',
                                400: '#FB923C',
                                500: '#F97316',
                                600: '#EA580C',
                                700: '#C2410C',
                            },
                            purple: {
                                50: '#FAF5FF',
                                100: '#F3E8FF',
                                500: '#8B5CF6',
                                600: '#7C3AED',
                                700: '#6D28D9',
                            }
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Interact.js for drag-and-drop -->
    <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Tiptap Editor -->
    <script type="module">
        import { Editor } from 'https://esm.sh/@tiptap/core@2.1.13'
        import StarterKit from 'https://esm.sh/@tiptap/starter-kit@2.1.13'
        window.TiptapEditor = Editor;
        window.TiptapStarterKit = StarterKit;
    </script>

    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }

        /* Smooth sidebar transition */
        .sidebar-transition {
            transition: width 0.2s ease-in-out;
        }
        .sidebar-content-transition {
            transition: opacity 0.15s ease-in-out;
        }

        /* Canvas Grid - 20px spacing for better alignment */
        .canvas-grid {
            background-image:
                linear-gradient(to right, #f1f5f9 1px, transparent 1px),
                linear-gradient(to bottom, #f1f5f9 1px, transparent 1px);
            background-size: 20px 20px;
            position: relative;
        }

        /* Clean canvas page styling (when grid is hidden) */
        .canvas-page {
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08), 0 8px 24px rgba(0, 0, 0, 0.08);
            border-radius: 2px;
            position: relative;
        }

        .canvas-page:not(.canvas-grid) {
            border: 1px solid #e5e7eb;
        }

        /* Enhanced selection styling */
        .element-selected {
            outline: 2px solid #3B82F6;
            outline-offset: 2px;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .element-hover:not(.element-selected) {
            outline: 2px dashed #93C5FD;
            outline-offset: 2px;
        }

        /* Resize handles - all corners */
        .resize-handle {
            width: 10px;
            height: 10px;
            background: white;
            border: 2px solid #3B82F6;
            border-radius: 2px;
            position: absolute;
            z-index: 20;
            transition: transform 0.1s ease;
        }

        .resize-handle:hover {
            transform: scale(1.2);
            background: #3B82F6;
        }

        .resize-handle-br { bottom: -5px; right: -5px; cursor: se-resize; }
        .resize-handle-bl { bottom: -5px; left: -5px; cursor: sw-resize; }
        .resize-handle-tr { top: -5px; right: -5px; cursor: ne-resize; }
        .resize-handle-tl { top: -5px; left: -5px; cursor: nw-resize; }
        .resize-handle-r { top: 50%; right: -5px; transform: translateY(-50%); cursor: e-resize; }
        .resize-handle-b { bottom: -5px; left: 50%; transform: translateX(-50%); cursor: s-resize; }

        /* Dragging state */
        .dragging {
            opacity: 0.85;
            z-index: 1000;
            cursor: grabbing !important;
        }

        /* Alignment guides */
        .alignment-guide {
            position: absolute;
            pointer-events: none;
            z-index: 999;
        }

        .alignment-guide-x {
            width: 1px;
            height: 100%;
            background: linear-gradient(to bottom, #3B82F6 50%, transparent 50%);
            background-size: 1px 8px;
            top: 0;
        }

        .alignment-guide-y {
            height: 1px;
            width: 100%;
            background: linear-gradient(to right, #3B82F6 50%, transparent 50%);
            background-size: 8px 1px;
            left: 0;
        }

        /* Snap indicator dot */
        .snap-indicator {
            position: absolute;
            width: 6px;
            height: 6px;
            background: #3B82F6;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            z-index: 1000;
            animation: snap-pulse 0.3s ease-out;
        }

        @keyframes snap-pulse {
            0% { transform: translate(-50%, -50%) scale(0); opacity: 1; }
            100% { transform: translate(-50%, -50%) scale(2); opacity: 0; }
        }

        /* Drop zone highlight */
        .canvas-drop-active {
            background-color: rgba(59, 130, 246, 0.03);
            outline: 2px dashed #93C5FD;
            outline-offset: -2px;
        }

        /* Ghost preview for dragging from sidebar */
        .drag-ghost {
            position: fixed;
            pointer-events: none;
            opacity: 0.7;
            z-index: 9999;
            transform: scale(0.9) rotate(2deg);
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            border-radius: 8px;
        }

        /* Scrollbar styling */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Element hover effect */
        [data-element-id] {
            cursor: grab;
            transition: box-shadow 0.15s ease;
        }

        [data-element-id]:hover:not(.dragging) {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        /* Keyboard navigation indicator */
        .keyboard-focus {
            outline: 2px solid #F97316 !important;
            outline-offset: 3px;
        }

        /* Multi-select styling */
        .multi-selected {
            outline: 2px solid #8B5CF6;
            outline-offset: 2px;
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15);
        }

        .multi-selected.element-selected {
            outline: 2px solid #3B82F6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15), 0 0 0 8px rgba(139, 92, 246, 0.1);
        }

        /* Selection count badge */
        .selection-count-badge {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #3B82F6;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            z-index: 1000;
            animation: badge-appear 0.2s ease-out;
        }

        @keyframes badge-appear {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Zoom transition */
        .canvas-zoom-container {
            will-change: transform;
        }

        /* Toast animations */
        @keyframes toast-in {
            from { transform: translateX(-50%) translateY(20px); opacity: 0; }
            to { transform: translateX(-50%) translateY(0); opacity: 1; }
        }

        @keyframes toast-out {
            from { transform: translateX(-50%) translateY(0); opacity: 1; }
            to { transform: translateX(-50%) translateY(20px); opacity: 0; }
        }

        /* ═══════════════════════════════════════════════════════════════════ */
        /* PHASE 6: WOW FACTOR ANIMATIONS */
        /* ═══════════════════════════════════════════════════════════════════ */

        /* 6.1 Modal entrance animation */
        @keyframes modal-in {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .animate-modal-in {
            animation: modal-in 0.2s ease-out;
        }

        /* 6.2 Quick actions toolbar */
        .quick-actions-toolbar {
            animation: toolbar-appear 0.15s ease-out;
        }

        @keyframes toolbar-appear {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* 6.3 Element entrance animation */
        [data-element-id] {
            animation: element-enter 0.2s ease-out;
        }

        @keyframes element-enter {
            from {
                opacity: 0;
                transform: translate(var(--x, 0), var(--y, 0)) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translate(var(--x, 0), var(--y, 0)) scale(1);
            }
        }

        /* Selection pulse animation */
        .element-selected {
            animation: selection-pulse 0.3s ease-out;
        }

        @keyframes selection-pulse {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
            70% { box-shadow: 0 0 0 8px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
        }

        /* 6.4 Distance indicator pills */
        .distance-indicator {
            position: absolute;
            background: #3B82F6;
            color: white;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 4px;
            pointer-events: none;
            z-index: 1001;
            white-space: nowrap;
            animation: distance-pop 0.15s ease-out;
        }

        @keyframes distance-pop {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        /* 6.5 Locked element styling */
        .element-locked {
            cursor: not-allowed !important;
            position: relative;
        }

        .element-locked::after {
            content: '🔒';
            position: absolute;
            top: 4px;
            right: 4px;
            font-size: 12px;
            opacity: 0.7;
        }

        .element-locked::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(156, 163, 175, 0.1);
            pointer-events: none;
            border-radius: inherit;
        }

        /* Delete animation */
        .element-deleting {
            animation: element-delete 0.15s ease-in forwards;
        }

        @keyframes element-delete {
            to {
                opacity: 0;
                transform: scale(0.9);
            }
        }
    </style>
</head>
<body class="bg-gray-100 overflow-hidden {{ session('demo_role_override') && session('demo_role_override') !== 'actual' ? 'pt-10' : '' }}">
    @php $inDemoMode = session('demo_role_override') && session('demo_role_override') !== 'actual'; @endphp

    <div x-data="{
            pulseNavCollapsed: localStorage.getItem('pulseNavCollapsed') === 'true' || true,
            hoveredItem: null
         }"
         x-init="$watch('pulseNavCollapsed', val => localStorage.setItem('pulseNavCollapsed', val))"
         class="flex h-screen">

        <!-- Pulse Main Navigation Sidebar -->
        <aside :class="pulseNavCollapsed ? 'w-16' : 'w-56'"
               class="sidebar-transition flex flex-col flex-shrink-0 {{ $inDemoMode ? 'bg-purple-50 border-r-4 border-purple-400' : 'bg-white border-r border-gray-200' }} z-40">

            <!-- Logo & Toggle -->
            <div class="px-3 py-3 {{ $inDemoMode ? 'border-b border-purple-200' : 'border-b border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <a href="/dashboard" class="flex items-center">
                        <span class="text-xl font-bold {{ $inDemoMode ? 'text-purple-600' : 'text-pulse-orange-500' }}">
                            <span x-show="!pulseNavCollapsed">Pulse</span>
                            <span x-show="pulseNavCollapsed" class="text-lg">P</span>
                        </span>
                    </a>
                    <button @click="pulseNavCollapsed = !pulseNavCollapsed"
                            class="p-1 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg x-show="!pulseNavCollapsed" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                        </svg>
                        <svg x-show="pulseNavCollapsed" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
                @if($inDemoMode)
                <div x-show="!pulseNavCollapsed" class="mt-2 px-2 py-1 bg-purple-100 rounded text-xs text-purple-700 font-medium text-center">
                    Viewing as: {{ ucfirst(str_replace('_', ' ', session('demo_role_override'))) }}
                </div>
                @endif
            </div>

            <!-- Quick Access -->
            <div class="py-2 border-b border-gray-200">
                <!-- Expanded: Compact Grid -->
                <div x-show="!pulseNavCollapsed" class="px-2">
                    <div class="grid grid-cols-2 gap-1">
                        @if(RolePermissions::currentUserCanAccess('home'))
                        <a href="/dashboard"
                           class="flex flex-col items-center justify-center p-2 rounded-lg border transition-colors border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300 text-xs">
                            <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            Home
                        </a>
                        @endif
                        @if(RolePermissions::currentUserCanAccess('contacts'))
                        <a href="/contacts"
                           class="flex flex-col items-center justify-center p-2 rounded-lg border transition-colors border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300 text-xs">
                            <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            Contacts
                        </a>
                        @endif
                        @if(RolePermissions::currentUserCanAccess('surveys'))
                        <a href="/surveys"
                           class="flex flex-col items-center justify-center p-2 rounded-lg border transition-colors border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300 text-xs">
                            <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                            Surveys
                        </a>
                        @endif
                        @if(RolePermissions::currentUserCanAccess('dashboards'))
                        <a href="/dashboards"
                           class="flex flex-col items-center justify-center p-2 rounded-lg border transition-colors border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300 text-xs">
                            <svg class="w-4 h-4 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                            </svg>
                            Dashboards
                        </a>
                        @endif
                    </div>
                </div>
                <!-- Collapsed: Vertical List -->
                <div x-show="pulseNavCollapsed" class="px-2 space-y-1">
                    @if(RolePermissions::currentUserCanAccess('home'))
                    <a href="/dashboard" @mouseenter="hoveredItem = 'home'" @mouseleave="hoveredItem = null"
                       class="relative flex items-center justify-center px-2 py-1.5 rounded-lg transition-colors text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <div x-show="hoveredItem === 'home'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Home</div>
                    </a>
                    @endif
                    @if(RolePermissions::currentUserCanAccess('contacts'))
                    <a href="/contacts" @mouseenter="hoveredItem = 'contacts'" @mouseleave="hoveredItem = null"
                       class="relative flex items-center justify-center px-2 py-1.5 rounded-lg transition-colors text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <div x-show="hoveredItem === 'contacts'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Contacts</div>
                    </a>
                    @endif
                    @if(RolePermissions::currentUserCanAccess('surveys'))
                    <a href="/surveys" @mouseenter="hoveredItem = 'surveys'" @mouseleave="hoveredItem = null"
                       class="relative flex items-center justify-center px-2 py-1.5 rounded-lg transition-colors text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <div x-show="hoveredItem === 'surveys'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Surveys</div>
                    </a>
                    @endif
                    @if(RolePermissions::currentUserCanAccess('dashboards'))
                    <a href="/dashboards" @mouseenter="hoveredItem = 'dashboards'" @mouseleave="hoveredItem = null"
                       class="relative flex items-center justify-center px-2 py-1.5 rounded-lg transition-colors text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                        </svg>
                        <div x-show="hoveredItem === 'dashboards'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Dashboards</div>
                    </a>
                    @endif
                </div>
            </div>

            <!-- Workspace Navigation -->
            <nav class="flex-1 py-2" :class="pulseNavCollapsed ? 'px-2 overflow-visible' : 'px-2 overflow-y-auto'">
                <p x-show="!pulseNavCollapsed" class="px-2 mb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-content-transition">Workspace</p>

                @if(RolePermissions::currentUserCanAccess('strategy'))
                <!-- Plan -->
                <div @mouseenter="hoveredItem = 'plan'" @mouseleave="hoveredItem = null" class="relative">
                    <a href="/plans"
                       :class="pulseNavCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-2 px-2 py-1.5 rounded-lg transition-colors text-gray-600 hover:bg-gray-50 hover:text-gray-900 text-sm">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span x-show="!pulseNavCollapsed" class="font-medium sidebar-content-transition">Plan</span>
                    </a>
                    <div x-show="pulseNavCollapsed && hoveredItem === 'plan'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Plan</div>
                </div>
                @endif

                @if(RolePermissions::currentUserCanAccess('reports'))
                <!-- Reports (Current) -->
                <div @mouseenter="hoveredItem = 'reports'" @mouseleave="hoveredItem = null" class="relative">
                    <a href="/reports"
                       :class="pulseNavCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-2 px-2 py-1.5 rounded-lg transition-colors bg-pulse-orange-50 text-pulse-orange-600 text-sm">
                        <svg class="w-5 h-5 flex-shrink-0 text-pulse-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span x-show="!pulseNavCollapsed" class="font-medium sidebar-content-transition">Reports</span>
                    </a>
                    <div x-show="pulseNavCollapsed && hoveredItem === 'reports'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Reports</div>
                </div>
                @endif

                @if(RolePermissions::currentUserCanAccess('collect'))
                <!-- Collect -->
                <div @mouseenter="hoveredItem = 'collect'" @mouseleave="hoveredItem = null" class="relative">
                    <a href="/collect"
                       :class="pulseNavCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-2 px-2 py-1.5 rounded-lg transition-colors text-gray-600 hover:bg-gray-50 hover:text-gray-900 text-sm">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span x-show="!pulseNavCollapsed" class="font-medium sidebar-content-transition">Collect</span>
                    </a>
                    <div x-show="pulseNavCollapsed && hoveredItem === 'collect'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Collect</div>
                </div>
                @endif

                @if(RolePermissions::currentUserCanAccess('distribute'))
                <!-- Distribute -->
                <div @mouseenter="hoveredItem = 'distribute'" @mouseleave="hoveredItem = null" class="relative">
                    <a href="/distribute"
                       :class="pulseNavCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-2 px-2 py-1.5 rounded-lg transition-colors text-gray-600 hover:bg-gray-50 hover:text-gray-900 text-sm">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                        <span x-show="!pulseNavCollapsed" class="font-medium sidebar-content-transition">Distribute</span>
                    </a>
                    <div x-show="pulseNavCollapsed && hoveredItem === 'distribute'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Distribute</div>
                </div>
                @endif

                @if(RolePermissions::currentUserCanAccess('resources'))
                <!-- Resource -->
                <div @mouseenter="hoveredItem = 'resource'" @mouseleave="hoveredItem = null" class="relative">
                    <a href="/resources"
                       :class="pulseNavCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-2 px-2 py-1.5 rounded-lg transition-colors text-gray-600 hover:bg-gray-50 hover:text-gray-900 text-sm">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span x-show="!pulseNavCollapsed" class="font-medium sidebar-content-transition">Resource</span>
                    </a>
                    <div x-show="pulseNavCollapsed && hoveredItem === 'resource'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Resource</div>
                </div>
                @endif

                @if(RolePermissions::currentUserCanAccess('marketplace'))
                <!-- Marketplace -->
                <div @mouseenter="hoveredItem = 'marketplace'" @mouseleave="hoveredItem = null" class="relative">
                    <a href="/marketplace"
                       :class="pulseNavCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-2 px-2 py-1.5 rounded-lg transition-colors text-gray-600 hover:bg-gray-50 hover:text-gray-900 text-sm">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span x-show="!pulseNavCollapsed" class="font-medium sidebar-content-transition">Marketplace</span>
                    </a>
                    <div x-show="pulseNavCollapsed && hoveredItem === 'marketplace'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Marketplace</div>
                </div>
                @endif
            </nav>

            @if(RolePermissions::currentUserCanAccess('settings'))
            <!-- Settings -->
            <div class="p-2 border-t border-gray-200">
                <div @mouseenter="hoveredItem = 'settings'" @mouseleave="hoveredItem = null" class="relative">
                    <a href="/settings"
                       :class="pulseNavCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-2 px-2 py-1.5 rounded-lg transition-colors text-gray-600 hover:bg-gray-50 hover:text-gray-900 text-sm">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span x-show="!pulseNavCollapsed" class="font-medium sidebar-content-transition">Settings</span>
                    </a>
                    <div x-show="pulseNavCollapsed && hoveredItem === 'settings'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Settings</div>
                </div>
            </div>
            @endif
        </aside>

        <!-- Report Builder Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <livewire:reports.report-builder :report="$report" :templates="$templates" />
        </div>
    </div>

    <!-- Demo Role Switcher (for admins only) -->
    @livewire('demo-role-switcher')

    @livewireScripts

    <script>
        // Enhanced Report Builder with alignment guides
        const ReportBuilder = {
            gridSize: 20,
            snapThreshold: 8,
            alignmentGuides: [],
            canvas: null,

            init() {
                this.canvas = document.querySelector('[data-report-canvas]');
                if (!this.canvas) return;

                this.initDragAndDrop();
                this.initHoverEffects();
                this.initZoomControls();
            },

            // Initialize mouse/keyboard zoom controls
            initZoomControls() {
                const canvas = document.querySelector('[data-canvas-wrapper]');
                if (!canvas) return;

                // Command + Scroll = Zoom in/out
                // Option + Scroll = Pan left/right
                canvas.addEventListener('wheel', (e) => {
                    if (e.metaKey || e.ctrlKey) {
                        e.preventDefault();
                        if (e.deltaY < 0) {
                            Livewire.dispatch('zoomIn');
                        } else {
                            Livewire.dispatch('zoomOut');
                        }
                    } else if (e.altKey) {
                        e.preventDefault();
                        // Horizontal scroll (pan left/right)
                        canvas.scrollLeft += e.deltaY;
                    }
                }, { passive: false });
            },

            // Get all element boundaries for alignment
            getElementBoundaries(excludeId = null) {
                const boundaries = { x: new Set(), y: new Set() };
                const elements = document.querySelectorAll('[data-element-id]');

                elements.forEach(el => {
                    if (el.dataset.elementId === excludeId) return;

                    const x = parseFloat(el.dataset.x) || 0;
                    const y = parseFloat(el.dataset.y) || 0;
                    const width = el.offsetWidth;
                    const height = el.offsetHeight;

                    // Left, center, right
                    boundaries.x.add(x);
                    boundaries.x.add(x + width / 2);
                    boundaries.x.add(x + width);

                    // Top, center, bottom
                    boundaries.y.add(y);
                    boundaries.y.add(y + height / 2);
                    boundaries.y.add(y + height);
                });

                // Add canvas center lines
                boundaries.x.add(400); // Center of 800px canvas
                boundaries.y.add(500); // Approximate center

                return {
                    x: Array.from(boundaries.x),
                    y: Array.from(boundaries.y)
                };
            },

            // Show alignment guides
            showAlignmentGuide(axis, position) {
                const guide = document.createElement('div');
                guide.className = `alignment-guide alignment-guide-${axis}`;
                guide.dataset.alignmentGuide = axis;

                if (axis === 'x') {
                    guide.style.left = `${position}px`;
                } else {
                    guide.style.top = `${position}px`;
                }

                this.canvas.appendChild(guide);
                this.alignmentGuides.push(guide);
            },

            // Clear all alignment guides
            clearAlignmentGuides() {
                this.alignmentGuides.forEach(g => g.remove());
                this.alignmentGuides = [];
            },

            // Check for alignment and show guides
            checkAlignment(target, x, y) {
                this.clearAlignmentGuides();

                const boundaries = this.getElementBoundaries(target.dataset.elementId);
                const width = target.offsetWidth;
                const height = target.offsetHeight;

                const elemPoints = {
                    x: [x, x + width / 2, x + width],
                    y: [y, y + height / 2, y + height]
                };

                let snapX = null, snapY = null;

                // Check X alignment
                for (const ex of elemPoints.x) {
                    for (const bx of boundaries.x) {
                        if (Math.abs(ex - bx) < this.snapThreshold) {
                            this.showAlignmentGuide('x', bx);
                            snapX = bx - (ex - x);
                            break;
                        }
                    }
                    if (snapX !== null) break;
                }

                // Check Y alignment
                for (const ey of elemPoints.y) {
                    for (const by of boundaries.y) {
                        if (Math.abs(ey - by) < this.snapThreshold) {
                            this.showAlignmentGuide('y', by);
                            snapY = by - (ey - y);
                            break;
                        }
                    }
                    if (snapY !== null) break;
                }

                return { snapX, snapY };
            },

            // Distance indicators
            distanceIndicators: [],

            showDistanceIndicator(x, y, distance, axis) {
                const indicator = document.createElement('div');
                indicator.className = 'distance-indicator';
                indicator.textContent = `${Math.round(distance)}px`;
                indicator.style.left = `${x}px`;
                indicator.style.top = `${y}px`;
                this.canvas.appendChild(indicator);
                this.distanceIndicators.push(indicator);
            },

            clearDistanceIndicators() {
                this.distanceIndicators.forEach(i => i.remove());
                this.distanceIndicators = [];
            },

            // Calculate distances to nearby elements
            calculateDistances(target, x, y) {
                this.clearDistanceIndicators();

                const elements = document.querySelectorAll('[data-element-id]');
                const targetWidth = target.offsetWidth;
                const targetHeight = target.offsetHeight;
                const targetRight = x + targetWidth;
                const targetBottom = y + targetHeight;

                elements.forEach(el => {
                    if (el === target) return;

                    const elX = parseFloat(el.dataset.x) || 0;
                    const elY = parseFloat(el.dataset.y) || 0;
                    const elWidth = el.offsetWidth;
                    const elHeight = el.offsetHeight;
                    const elRight = elX + elWidth;
                    const elBottom = elY + elHeight;

                    // Check vertical spacing (element above/below)
                    if (x < elRight && targetRight > elX) {
                        // Target is below this element
                        if (y > elBottom && y - elBottom < 60) {
                            const dist = y - elBottom;
                            const indicatorX = Math.max(x, elX) + Math.min(targetWidth, elWidth) / 2;
                            const indicatorY = elBottom + dist / 2;
                            this.showDistanceIndicator(indicatorX, indicatorY, dist, 'y');
                        }
                        // Target is above this element
                        if (elY > targetBottom && elY - targetBottom < 60) {
                            const dist = elY - targetBottom;
                            const indicatorX = Math.max(x, elX) + Math.min(targetWidth, elWidth) / 2;
                            const indicatorY = targetBottom + dist / 2;
                            this.showDistanceIndicator(indicatorX, indicatorY, dist, 'y');
                        }
                    }

                    // Check horizontal spacing (element left/right)
                    if (y < elBottom && targetBottom > elY) {
                        // Target is to the right of this element
                        if (x > elRight && x - elRight < 60) {
                            const dist = x - elRight;
                            const indicatorX = elRight + dist / 2;
                            const indicatorY = Math.max(y, elY) + Math.min(targetHeight, elHeight) / 2;
                            this.showDistanceIndicator(indicatorX, indicatorY, dist, 'x');
                        }
                        // Target is to the left of this element
                        if (elX > targetRight && elX - targetRight < 60) {
                            const dist = elX - targetRight;
                            const indicatorX = targetRight + dist / 2;
                            const indicatorY = Math.max(y, elY) + Math.min(targetHeight, elHeight) / 2;
                            this.showDistanceIndicator(indicatorX, indicatorY, dist, 'x');
                        }
                    }
                });
            },

            initDragAndDrop() {
                const self = this;

                // Only make non-locked elements draggable
                interact('[data-element-id]:not([data-locked="true"])')
                    .draggable({
                        inertia: {
                            resistance: 20,
                            minSpeed: 100,
                            endSpeed: 50
                        },
                        modifiers: [
                            interact.modifiers.snap({
                                targets: [interact.snappers.grid({ x: self.gridSize, y: self.gridSize })],
                                range: self.snapThreshold,
                                relativePoints: [{ x: 0, y: 0 }]
                            }),
                            interact.modifiers.restrict({
                                restriction: 'parent',
                                elementRect: { top: 0, left: 0, bottom: 1, right: 1 }
                            })
                        ],
                        autoScroll: true,
                        listeners: {
                            start(event) {
                                event.target.classList.add('dragging');
                                const elementId = event.target.dataset.elementId;
                                Livewire.dispatch('selectElement', { elementId });
                            },
                            move(event) {
                                const target = event.target;
                                let x = (parseFloat(target.dataset.x) || 0) + event.dx;
                                let y = (parseFloat(target.dataset.y) || 0) + event.dy;

                                // Check for alignment snapping
                                const snap = self.checkAlignment(target, x, y);
                                if (snap.snapX !== null) x = snap.snapX;
                                if (snap.snapY !== null) y = snap.snapY;

                                // Ensure non-negative
                                x = Math.max(0, x);
                                y = Math.max(0, y);

                                target.style.transform = `translate(${x}px, ${y}px)`;
                                target.dataset.x = x;
                                target.dataset.y = y;

                                // Show distance indicators
                                self.calculateDistances(target, x, y);
                            },
                            end(event) {
                                event.target.classList.remove('dragging');
                                self.clearAlignmentGuides();
                                self.clearDistanceIndicators();

                                const elementId = event.target.dataset.elementId;
                                const x = Math.max(0, parseFloat(event.target.dataset.x) || 0);
                                const y = Math.max(0, parseFloat(event.target.dataset.y) || 0);

                                Livewire.dispatch('updateElementPosition', { elementId, x, y });
                                Livewire.dispatch('commitElementChange');
                            }
                        }
                    })
                    .resizable({
                        edges: { right: true, bottom: true, left: false, top: false },
                        modifiers: [
                            interact.modifiers.restrictSize({
                                min: { width: 60, height: 40 }
                            }),
                            interact.modifiers.snap({
                                targets: [interact.snappers.grid({ x: self.gridSize, y: self.gridSize })],
                                range: self.snapThreshold
                            })
                        ],
                        listeners: {
                            move(event) {
                                const target = event.target;
                                target.style.width = `${event.rect.width}px`;
                                target.style.height = `${event.rect.height}px`;
                            },
                            end(event) {
                                const elementId = event.target.dataset.elementId;
                                Livewire.dispatch('updateElementSize', {
                                    elementId,
                                    width: Math.round(event.rect.width),
                                    height: Math.round(event.rect.height)
                                });
                                Livewire.dispatch('commitElementChange');
                            }
                        }
                    });
            },

            initHoverEffects() {
                document.querySelectorAll('[data-element-id]').forEach(el => {
                    el.addEventListener('mouseenter', () => {
                        if (!el.classList.contains('dragging')) {
                            el.classList.add('element-hover');
                        }
                    });
                    el.addEventListener('mouseleave', () => {
                        el.classList.remove('element-hover');
                    });
                });
            },

            // Nudge element with arrow keys
            nudgeElement(direction, amount = 1) {
                const selected = document.querySelector('.element-selected');
                if (!selected) return;

                let x = parseFloat(selected.dataset.x) || 0;
                let y = parseFloat(selected.dataset.y) || 0;

                switch (direction) {
                    case 'up': y = Math.max(0, y - amount); break;
                    case 'down': y += amount; break;
                    case 'left': x = Math.max(0, x - amount); break;
                    case 'right': x += amount; break;
                }

                selected.style.transform = `translate(${x}px, ${y}px)`;
                selected.dataset.x = x;
                selected.dataset.y = y;

                Livewire.dispatch('updateElementPosition', {
                    elementId: selected.dataset.elementId,
                    x, y
                });
            },

            // Show a toast notification
            showToast(message, duration = 2000) {
                // Remove any existing toast
                const existing = document.querySelector('.toast-notification');
                if (existing) existing.remove();

                const toast = document.createElement('div');
                toast.className = 'toast-notification';
                toast.innerHTML = `
                    <div style="
                        position: fixed;
                        bottom: 20px;
                        left: 50%;
                        transform: translateX(-50%);
                        background: #1F2937;
                        color: white;
                        padding: 12px 24px;
                        border-radius: 8px;
                        font-size: 14px;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                        z-index: 9999;
                        animation: toast-in 0.3s ease-out;
                    ">${message}</div>
                `;
                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.style.animation = 'toast-out 0.3s ease-in forwards';
                    setTimeout(() => toast.remove(), 300);
                }, duration);
            }
        };

        // Initialize
        document.addEventListener('livewire:navigated', () => ReportBuilder.init());
        document.addEventListener('DOMContentLoaded', () => ReportBuilder.init());

        // Re-initialize after Livewire updates
        Livewire.hook('morph.updated', ({ el, component }) => {
            ReportBuilder.init();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            const isInInput = ['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName) ||
                              document.activeElement.closest('[data-tiptap-editor]') ||
                              document.activeElement.isContentEditable;

            // Undo: Ctrl/Cmd + Z
            if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) {
                e.preventDefault();
                Livewire.dispatch('undo');
            }
            // Redo: Ctrl/Cmd + Shift + Z or Ctrl/Cmd + Y
            if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.key === 'z' && e.shiftKey))) {
                e.preventDefault();
                Livewire.dispatch('redo');
            }
            // Save: Ctrl/Cmd + S
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                Livewire.dispatch('save');
            }
            // Duplicate: Ctrl/Cmd + D
            if ((e.ctrlKey || e.metaKey) && e.key === 'd' && !isInInput) {
                e.preventDefault();
                const selected = document.querySelector('.element-selected');
                if (selected) {
                    Livewire.dispatch('duplicateElement', { elementId: selected.dataset.elementId });
                }
            }
            // Delete: Delete or Backspace (when not in input)
            if ((e.key === 'Delete' || e.key === 'Backspace') && !isInInput) {
                e.preventDefault();
                Livewire.dispatch('deleteSelectedElement');
            }
            // Escape: Deselect
            if (e.key === 'Escape') {
                Livewire.dispatch('selectElement', { elementId: null });
            }
            // Arrow keys: Nudge selected element
            if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key) && !isInInput) {
                e.preventDefault();
                const amount = e.shiftKey ? ReportBuilder.gridSize : 1; // Shift = grid-sized nudge
                const direction = {
                    'ArrowUp': 'up',
                    'ArrowDown': 'down',
                    'ArrowLeft': 'left',
                    'ArrowRight': 'right'
                }[e.key];
                ReportBuilder.nudgeElement(direction, amount);

                // Commit after a short delay (debounce multiple key presses)
                clearTimeout(window.nudgeCommitTimeout);
                window.nudgeCommitTimeout = setTimeout(() => {
                    Livewire.dispatch('commitElementChange');
                }, 300);
            }
            // Copy: Ctrl/Cmd + C
            if ((e.ctrlKey || e.metaKey) && e.key === 'c' && !isInInput) {
                e.preventDefault();
                Livewire.dispatch('copySelected');
                ReportBuilder.showToast('Copied to clipboard');
            }
            // Cut: Ctrl/Cmd + X
            if ((e.ctrlKey || e.metaKey) && e.key === 'x' && !isInInput) {
                e.preventDefault();
                Livewire.dispatch('cutSelected');
                ReportBuilder.showToast('Cut to clipboard');
            }
            // Paste: Ctrl/Cmd + V
            if ((e.ctrlKey || e.metaKey) && e.key === 'v' && !isInInput) {
                e.preventDefault();
                Livewire.dispatch('pasteFromClipboard');
            }
            // Select All: Ctrl/Cmd + A
            if ((e.ctrlKey || e.metaKey) && e.key === 'a' && !isInInput) {
                e.preventDefault();
                Livewire.dispatch('selectAll');
            }
            // Show shortcuts: ? key
            if (e.key === '?' && !isInInput) {
                e.preventDefault();
                Livewire.dispatch('$set', { property: 'showShortcutsModal', value: true });
            }
            // Zoom in: Ctrl/Cmd + Plus or =
            if ((e.ctrlKey || e.metaKey) && (e.key === '+' || e.key === '=')) {
                e.preventDefault();
                Livewire.dispatch('zoomIn');
            }
            // Zoom out: Ctrl/Cmd + Minus
            if ((e.ctrlKey || e.metaKey) && e.key === '-') {
                e.preventDefault();
                Livewire.dispatch('zoomOut');
            }
            // Reset zoom: Ctrl/Cmd + 0
            if ((e.ctrlKey || e.metaKey) && e.key === '0') {
                e.preventDefault();
                Livewire.dispatch('resetZoom');
            }
        });

        // Mouse wheel zoom (Cmd/Ctrl + scroll) and horizontal pan (Option/Alt + scroll)
        document.addEventListener('wheel', (e) => {
            // Only handle if over the canvas area
            const canvas = e.target.closest('[data-canvas-wrapper]');
            if (!canvas) return;

            // Cmd/Ctrl + scroll = zoom in/out
            if (e.metaKey || e.ctrlKey) {
                e.preventDefault();
                if (e.deltaY < 0) {
                    Livewire.dispatch('zoomIn');
                } else {
                    Livewire.dispatch('zoomOut');
                }
            }
            // Option/Alt + scroll = horizontal pan
            else if (e.altKey) {
                e.preventDefault();
                canvas.scrollLeft += e.deltaY;
            }
        }, { passive: false });

        // Chart.js management
        const chartInstances = {};

        function initCharts() {
            document.querySelectorAll('[data-chart-element]').forEach(container => {
                const elementId = container.dataset.chartElement;
                const canvas = container.querySelector('canvas');
                if (!canvas) return;

                // Destroy existing chart
                if (chartInstances[elementId]) {
                    chartInstances[elementId].destroy();
                }

                // Get chart config from data attribute
                const config = JSON.parse(container.dataset.chartConfig || '{}');
                const chartData = JSON.parse(container.dataset.chartData || '{}');

                const chartType = config.chart_type || 'line';
                const metricKeys = config.metric_keys || [];
                const colors = config.colors || ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'];

                // Build datasets
                const datasets = metricKeys.map((key, index) => {
                    const data = chartData[key] || [];
                    return {
                        label: key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
                        data: data.map(d => d.value),
                        borderColor: colors[index % colors.length],
                        backgroundColor: colors[index % colors.length] + '20',
                        tension: 0.3,
                        fill: chartType === 'line'
                    };
                });

                // Get labels from first metric
                const firstMetric = metricKeys[0];
                const labels = chartData[firstMetric]?.map(d => d.period) || [];

                chartInstances[elementId] = new Chart(canvas, {
                    type: chartType,
                    data: { labels, datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: metricKeys.length > 1,
                                position: 'bottom'
                            }
                        },
                        scales: chartType === 'pie' || chartType === 'doughnut' ? {} : {
                            y: { beginAtZero: false },
                            x: { grid: { display: false } }
                        }
                    }
                });
            });
        }

        // Initialize charts after Livewire updates
        Livewire.hook('morph.updated', () => {
            setTimeout(initCharts, 100);
        });

        // Initial chart load
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(initCharts, 500);
        });

        // Listen for chart data updates
        Livewire.on('chartsUpdated', () => {
            setTimeout(initCharts, 100);
        });

        // Capture charts as images for PDF export
        async function captureChartsAsImages() {
            const images = {};
            for (const [elementId, chart] of Object.entries(chartInstances)) {
                images[elementId] = chart.toBase64Image('image/png', 1);
            }
            return images;
        }

        // Listen for PDF export request
        Livewire.on('prepareForPdf', async () => {
            const chartImages = await captureChartsAsImages();
            Livewire.dispatch('chartImagesReady', { images: chartImages });
        });
    </script>
</body>
</html>
