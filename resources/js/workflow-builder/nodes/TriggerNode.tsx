import React, { memo, useState } from 'react';
import { Handle, Position, type NodeProps } from '@xyflow/react';
import type { TriggerNodeData } from '../types/workflow';
import { TRIGGER_TYPES } from '../types/workflow';

const nodeTypeOptions = [
    { type: 'condition', label: 'Condition', color: 'purple' },
    { type: 'delay', label: 'Delay', color: 'indigo' },
    { type: 'action', label: 'Action', color: 'emerald' },
    { type: 'branch', label: 'Split', color: 'orange' },
];

function TriggerNode({ id, data, selected }: NodeProps<TriggerNodeData>) {
    const [showMenu, setShowMenu] = useState(false);
    // Safely get trigger type label - ensure we always render a string
    const getTriggerLabel = (): string => {
        if (!data?.trigger_type) return 'Unknown Trigger';
        if (typeof data.trigger_type !== 'string') return 'Unknown Trigger';
        const triggerConfig = TRIGGER_TYPES[data.trigger_type as keyof typeof TRIGGER_TYPES];
        if (!triggerConfig) return 'Unknown Trigger';
        return typeof triggerConfig.label === 'string' ? triggerConfig.label : 'Unknown Trigger';
    };

    const triggerLabel = getTriggerLabel();

    const handleAddNode = (nodeType: string) => {
        const event = new CustomEvent('addNodeFromBranch', {
            detail: {
                sourceNodeId: id,
                branchIndex: 0,
                branchId: 'default',
                nodeType,
            },
        });
        window.dispatchEvent(event);
        setShowMenu(false);
    };

    return (
        <div
            className={`
                px-4 py-3 rounded-lg border-2 bg-white shadow-sm min-w-[200px]
                ${selected ? 'border-amber-500 ring-2 ring-amber-200' : 'border-amber-300'}
            `}
        >
            {/* Header */}
            <div className="flex items-center gap-2 mb-2">
                <div className="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                    <svg className="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <div className="text-xs font-semibold uppercase text-amber-600 tracking-wide">Trigger</div>
                    <div className="text-sm font-medium text-gray-900">{triggerLabel}</div>
                </div>
            </div>

            {/* Conditions Preview */}
            {data?.conditions && data.conditions.length > 0 && (
                <div className="mt-2 pt-2 border-t border-gray-100">
                    <div className="text-xs text-gray-500">
                        {data.conditions.length} condition{data.conditions.length !== 1 ? 's' : ''} ({(data.logic || 'and').toUpperCase()})
                    </div>
                </div>
            )}

            {/* Output Handle */}
            <Handle
                type="source"
                position={Position.Bottom}
                className="!w-3 !h-3 !bg-amber-500 !border-2 !border-white"
            />

            {/* Add Node Button */}
            <div className="absolute -bottom-8 left-1/2 -translate-x-1/2">
                <button
                    onClick={(e) => {
                        e.stopPropagation();
                        setShowMenu(!showMenu);
                    }}
                    className="w-5 h-5 rounded-full bg-amber-100 hover:bg-amber-200 flex items-center justify-center text-amber-600 transition-colors shadow-sm border border-amber-200"
                    title="Add next node"
                >
                    <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                    </svg>
                </button>

                {showMenu && (
                    <div className="absolute top-full mt-1 left-1/2 -translate-x-1/2 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50 min-w-[100px]">
                        {nodeTypeOptions.map((option) => (
                            <button
                                key={option.type}
                                onClick={(e) => {
                                    e.stopPropagation();
                                    handleAddNode(option.type);
                                }}
                                className="w-full px-3 py-1.5 text-left text-xs hover:bg-gray-50 flex items-center gap-2"
                            >
                                <span className={`w-2 h-2 rounded-full bg-${option.color}-500`} />
                                {option.label}
                            </button>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}

export default memo(TriggerNode);
