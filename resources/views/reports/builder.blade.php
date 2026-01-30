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

        .canvas-grid {
            background-image:
                linear-gradient(to right, #f1f5f9 1px, transparent 1px),
                linear-gradient(to bottom, #f1f5f9 1px, transparent 1px);
            background-size: 10px 10px;
        }

        .element-selected {
            outline: 2px solid #3B82F6;
            outline-offset: 2px;
        }

        .element-hover:not(.element-selected) {
            outline: 2px dashed #93C5FD;
            outline-offset: 2px;
        }

        .resize-handle {
            width: 10px;
            height: 10px;
            background: #3B82F6;
            border: 2px solid white;
            border-radius: 2px;
            position: absolute;
        }

        .resize-handle-br { bottom: -5px; right: -5px; cursor: se-resize; }
        .resize-handle-bl { bottom: -5px; left: -5px; cursor: sw-resize; }
        .resize-handle-tr { top: -5px; right: -5px; cursor: ne-resize; }
        .resize-handle-tl { top: -5px; left: -5px; cursor: nw-resize; }

        .dragging {
            opacity: 0.8;
            z-index: 1000;
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
    </style>
</head>
<body class="bg-gray-100 overflow-hidden">
    <livewire:reports.report-builder :report="$report" :templates="$templates" />

    @livewireScripts

    <script>
        // Interact.js integration with Livewire
        document.addEventListener('livewire:navigated', initReportBuilder);
        document.addEventListener('DOMContentLoaded', initReportBuilder);

        function initReportBuilder() {
            // Initialize draggable elements on canvas
            initCanvasElements();
        }

        function initCanvasElements() {
            const canvas = document.querySelector('[data-report-canvas]');
            if (!canvas) return;

            // Make elements draggable
            interact('[data-element-id]')
                .draggable({
                    inertia: false,
                    modifiers: [
                        interact.modifiers.snap({
                            targets: [interact.snappers.grid({ x: 10, y: 10 })],
                            range: Infinity,
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
                            const x = (parseFloat(target.dataset.x) || 0) + event.dx;
                            const y = (parseFloat(target.dataset.y) || 0) + event.dy;

                            target.style.transform = `translate(${x}px, ${y}px)`;
                            target.dataset.x = x;
                            target.dataset.y = y;
                        },
                        end(event) {
                            event.target.classList.remove('dragging');
                            const elementId = event.target.dataset.elementId;
                            const x = parseFloat(event.target.dataset.x) || 0;
                            const y = parseFloat(event.target.dataset.y) || 0;

                            Livewire.dispatch('updateElementPosition', {
                                elementId,
                                x,
                                y
                            });
                            Livewire.dispatch('commitElementChange');
                        }
                    }
                })
                .resizable({
                    edges: { right: true, bottom: true },
                    modifiers: [
                        interact.modifiers.restrictSize({
                            min: { width: 50, height: 30 }
                        }),
                        interact.modifiers.snap({
                            targets: [interact.snappers.grid({ x: 10, y: 10 })],
                            range: Infinity
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
                                width: event.rect.width,
                                height: event.rect.height
                            });
                            Livewire.dispatch('commitElementChange');
                        }
                    }
                });
        }

        // Re-initialize after Livewire updates
        Livewire.hook('morph.updated', ({ el, component }) => {
            initCanvasElements();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
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
            // Delete: Delete or Backspace (when not in input)
            if ((e.key === 'Delete' || e.key === 'Backspace') && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName) && !document.activeElement.closest('[data-tiptap-editor]')) {
                e.preventDefault();
                Livewire.dispatch('deleteSelectedElement');
            }
            // Escape: Deselect
            if (e.key === 'Escape') {
                Livewire.dispatch('selectElement', { elementId: null });
            }
        });

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
