import React, { memo, useState } from 'react';
import { Handle, Position, type NodeProps } from '@xyflow/react';
import type { BranchNodeData } from '../types/workflow';

const nodeTypeOptions = [
    { type: 'condition', label: 'Condition', color: 'purple' },
    { type: 'delay', label: 'Delay', color: 'indigo' },
    { type: 'action', label: 'Action', color: 'emerald' },
    { type: 'branch', label: 'Split', color: 'orange' },
];

function BranchNode({ id, data, selected }: NodeProps<BranchNodeData>) {
    const [showMenu, setShowMenu] = useState<number | null>(null);
    const branches = data.branches || [
        { id: '1', name: 'Yes' },
        { id: '2', name: 'No' },
    ];

    const handleAddNode = (branchIndex: number, nodeType: string) => {
        // Dispatch custom event that WorkflowCanvas will handle
        const event = new CustomEvent('addNodeFromBranch', {
            detail: {
                sourceNodeId: id,
                branchIndex,
                branchId: `branch-${branchIndex}`,
                nodeType,
            },
        });
        window.dispatchEvent(event);
        setShowMenu(null);
    };

    return (
        <div
            className={`
                px-4 py-3 rounded-lg border-2 bg-white shadow-sm min-w-[200px]
                ${selected ? 'border-orange-500 ring-2 ring-orange-200' : 'border-orange-300'}
            `}
        >
            {/* Input Handle */}
            <Handle
                type="target"
                position={Position.Top}
                className="!w-3 !h-3 !bg-orange-500 !border-2 !border-white"
            />

            {/* Header */}
            <div className="flex items-center gap-2 mb-3">
                <div className="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
                    <svg className="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <div>
                    <div className="text-xs font-semibold uppercase text-orange-600 tracking-wide">Split</div>
                    <div className="text-sm text-gray-500">{branches.length} paths</div>
                </div>
            </div>

            {/* Branch Labels with Add Buttons */}
            <div className="flex justify-around gap-2 mt-2">
                {branches.map((branch, index) => (
                    <div key={index} className="relative flex flex-col items-center">
                        <span className="text-xs font-medium text-gray-600 mb-1">
                            {branch.name || branch.label || `Path ${index + 1}`}
                        </span>
                        <button
                            onClick={(e) => {
                                e.stopPropagation();
                                setShowMenu(showMenu === index ? null : index);
                            }}
                            className="w-5 h-5 rounded-full bg-orange-100 hover:bg-orange-200 flex items-center justify-center text-orange-600 transition-colors"
                            title={`Add node to ${branch.name || `Path ${index + 1}`}`}
                        >
                            <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                            </svg>
                        </button>

                        {/* Dropdown Menu */}
                        {showMenu === index && (
                            <div className="absolute top-full mt-1 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50 min-w-[120px]">
                                {nodeTypeOptions.map((option) => (
                                    <button
                                        key={option.type}
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            handleAddNode(index, option.type);
                                        }}
                                        className="w-full px-3 py-1.5 text-left text-xs hover:bg-gray-50 flex items-center gap-2"
                                    >
                                        <span className={`w-2 h-2 rounded-full bg-${option.color}-500`}></span>
                                        {option.label}
                                    </button>
                                ))}
                            </div>
                        )}
                    </div>
                ))}
            </div>

            {/* Output Handles */}
            {branches.map((branch, index) => {
                const total = branches.length;
                const spacing = 100 / (total + 1);
                const position = spacing * (index + 1);

                return (
                    <Handle
                        key={branch.id || index}
                        type="source"
                        position={Position.Bottom}
                        id={`branch-${index}`}
                        className="!w-3 !h-3 !bg-orange-500 !border-2 !border-white"
                        style={{ left: `${position}%` }}
                    />
                );
            })}
        </div>
    );
}

export default memo(BranchNode);
