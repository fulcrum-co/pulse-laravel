import React from 'react';

interface NodePaletteProps {
    className?: string;
    onAddNode?: (type: string) => void;
}

interface PaletteItem {
    type: string;
    label: string;
    description: string;
    color: string;
    borderColor: string;
    icon: JSX.Element;
}

const paletteItems: PaletteItem[] = [
    {
        type: 'trigger',
        label: 'Trigger',
        description: 'Start the workflow',
        color: 'amber',
        borderColor: '#F59E0B',
        icon: (
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
        ),
    },
    {
        type: 'condition',
        label: 'Condition',
        description: 'IF/ELSE logic',
        color: 'purple',
        borderColor: '#8B5CF6',
        icon: (
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        ),
    },
    {
        type: 'delay',
        label: 'Delay',
        description: 'Wait before continuing',
        color: 'indigo',
        borderColor: '#6366F1',
        icon: (
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        ),
    },
    {
        type: 'action',
        label: 'Action',
        description: 'Send notification, call API',
        color: 'emerald',
        borderColor: '#10B981',
        icon: (
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        ),
    },
    {
        type: 'branch',
        label: 'Split',
        description: 'Parallel execution paths',
        color: 'orange',
        borderColor: '#F97316',
        icon: (
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
        ),
    },
];

const colorClasses: Record<string, { bg: string; border: string; text: string; hover: string }> = {
    amber: {
        bg: 'bg-amber-50',
        border: 'border-amber-200',
        text: 'text-amber-600',
        hover: 'hover:border-amber-400 hover:bg-amber-100',
    },
    purple: {
        bg: 'bg-purple-50',
        border: 'border-purple-200',
        text: 'text-purple-600',
        hover: 'hover:border-purple-400 hover:bg-purple-100',
    },
    indigo: {
        bg: 'bg-indigo-50',
        border: 'border-indigo-200',
        text: 'text-indigo-600',
        hover: 'hover:border-indigo-400 hover:bg-indigo-100',
    },
    emerald: {
        bg: 'bg-emerald-50',
        border: 'border-emerald-200',
        text: 'text-emerald-600',
        hover: 'hover:border-emerald-400 hover:bg-emerald-100',
    },
    orange: {
        bg: 'bg-orange-50',
        border: 'border-orange-200',
        text: 'text-orange-600',
        hover: 'hover:border-orange-400 hover:bg-orange-100',
    },
};

export default function NodePalette({ className = '', onAddNode }: NodePaletteProps) {
    const onDragStart = (event: React.DragEvent, item: PaletteItem) => {
        console.log('[NodePalette] Drag started for:', item.type);

        // Set the data transfer
        event.dataTransfer.setData('application/reactflow', item.type);
        event.dataTransfer.effectAllowed = 'move';

        // Create custom drag image - a tiny compact pill
        const dragImage = document.createElement('div');
        dragImage.textContent = item.label;
        dragImage.style.cssText = `
            position: fixed;
            top: -1000px;
            left: -1000px;
            display: inline-block;
            padding: 2px 8px;
            background: white;
            border: 1px solid ${item.borderColor};
            border-radius: 3px;
            font-size: 10px;
            font-weight: 500;
            font-family: system-ui, -apple-system, sans-serif;
            color: ${item.borderColor};
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
            white-space: nowrap;
            transform: scale(1);
            transform-origin: top left;
        `;
        document.body.appendChild(dragImage);

        // Set as drag image - offset to center on cursor
        event.dataTransfer.setDragImage(dragImage, 25, 10);

        // Clean up after drag starts
        requestAnimationFrame(() => {
            document.body.removeChild(dragImage);
        });
    };

    const handleClick = (nodeType: string) => {
        if (onAddNode) {
            onAddNode(nodeType);
        }
    };

    return (
        <div className={`bg-white p-4 ${className}`}>
            <h3 className="text-sm font-semibold text-gray-900 mb-3">Add Nodes</h3>
            <p className="text-xs text-gray-500 mb-4">Click or drag onto canvas</p>

            <div className="space-y-2">
                {paletteItems.map((item) => {
                    const colors = colorClasses[item.color];
                    return (
                        <div
                            key={item.type}
                            draggable
                            onDragStart={(e) => onDragStart(e, item)}
                            onClick={() => handleClick(item.type)}
                            className={`
                                flex items-center gap-3 p-3 rounded-lg border cursor-grab
                                transition-all duration-150
                                ${colors.bg} ${colors.border} ${colors.hover}
                                active:scale-95 active:cursor-grabbing
                            `}
                        >
                            <div className={`flex-shrink-0 ${colors.text}`}>
                                {item.icon}
                            </div>
                            <div className="min-w-0 flex-1">
                                <div className={`text-sm font-medium ${colors.text}`}>
                                    {item.label}
                                </div>
                                <div className="text-xs text-gray-500 truncate">
                                    {item.description}
                                </div>
                            </div>
                            <svg className={`w-4 h-4 ${colors.text} opacity-50`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                    );
                })}
            </div>

            <div className="mt-4 pt-4 border-t border-gray-100">
                <p className="text-xs text-gray-400">
                    Tip: Connect nodes by dragging from handles.
                </p>
            </div>
        </div>
    );
}
