import React, { memo, useState } from 'react';
import { Handle, Position, type NodeProps } from '@xyflow/react';
import type { DelayNodeData } from '../types/workflow';

const nodeTypeOptions = [
    { type: 'condition', label: 'Condition', color: 'purple' },
    { type: 'delay', label: 'Delay', color: 'indigo' },
    { type: 'action', label: 'Action', color: 'emerald' },
    { type: 'branch', label: 'Split', color: 'orange' },
];

function DelayNode({ id, data, selected }: NodeProps<DelayNodeData>) {
    const [showMenu, setShowMenu] = useState(false);

    const formatDuration = () => {
        const duration = data?.duration || 1;
        const unit = data?.unit || 'hours';
        return `${duration} ${unit}`;
    };

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
                px-4 py-3 rounded-lg border-2 bg-white shadow-sm min-w-[160px]
                ${selected ? 'border-indigo-500 ring-2 ring-indigo-200' : 'border-indigo-300'}
            `}
        >
            {/* Input Handle */}
            <Handle
                type="target"
                position={Position.Top}
                className="!w-3 !h-3 !bg-indigo-500 !border-2 !border-white"
            />

            {/* Header */}
            <div className="flex items-center gap-2">
                <div className="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg className="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div className="text-xs font-semibold uppercase text-indigo-600 tracking-wide">Delay</div>
                    <div className="text-sm font-medium text-gray-900">{formatDuration()}</div>
                </div>
            </div>

            {/* Output Handle */}
            <Handle
                type="source"
                position={Position.Bottom}
                className="!w-3 !h-3 !bg-indigo-500 !border-2 !border-white"
            />

            {/* Add Node Button */}
            <div className="absolute -bottom-8 left-1/2 -translate-x-1/2">
                <button
                    onClick={(e) => {
                        e.stopPropagation();
                        setShowMenu(!showMenu);
                    }}
                    className="w-5 h-5 rounded-full bg-indigo-100 hover:bg-indigo-200 flex items-center justify-center text-indigo-600 transition-colors shadow-sm border border-indigo-200"
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

export default memo(DelayNode);
