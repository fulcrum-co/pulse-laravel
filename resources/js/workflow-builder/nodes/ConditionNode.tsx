import React, { memo, useState } from 'react';
import { Handle, Position, type NodeProps } from '@xyflow/react';
import type { ConditionNodeData } from '../types/workflow';

const nodeTypeOptions = [
    { type: 'condition', label: 'Condition', color: 'purple' },
    { type: 'delay', label: 'Delay', color: 'indigo' },
    { type: 'action', label: 'Action', color: 'emerald' },
    { type: 'branch', label: 'Split', color: 'orange' },
];

function ConditionNode({ id, data, selected }: NodeProps<ConditionNodeData>) {
    const [showMenuYes, setShowMenuYes] = useState(false);
    const [showMenuNo, setShowMenuNo] = useState(false);

    const handleAddNode = (nodeType: string, branchId: string) => {
        const event = new CustomEvent('addNodeFromBranch', {
            detail: {
                sourceNodeId: id,
                branchIndex: branchId === 'true' ? 0 : 1,
                branchId,
                nodeType,
            },
        });
        window.dispatchEvent(event);
        setShowMenuYes(false);
        setShowMenuNo(false);
    };

    return (
        <div
            className={`
                px-4 py-3 rounded-lg border-2 bg-white shadow-sm min-w-[180px]
                ${selected ? 'border-purple-500 ring-2 ring-purple-200' : 'border-purple-300'}
            `}
        >
            {/* Input Handle */}
            <Handle
                type="target"
                position={Position.Top}
                className="!w-3 !h-3 !bg-purple-500 !border-2 !border-white"
            />

            {/* Header */}
            <div className="flex items-center gap-2 mb-2">
                <div className="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                    <svg className="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div className="text-xs font-semibold uppercase text-purple-600 tracking-wide">Condition</div>
                    <div className="text-sm font-medium text-gray-900">IF / ELSE</div>
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

            {/* Output Handles */}
            <Handle
                type="source"
                position={Position.Bottom}
                id="true"
                style={{ left: '30%' }}
                className="!w-3 !h-3 !bg-green-500 !border-2 !border-white"
            />
            <Handle
                type="source"
                position={Position.Bottom}
                id="false"
                style={{ left: '70%' }}
                className="!w-3 !h-3 !bg-red-500 !border-2 !border-white"
            />

            {/* Labels with Add Buttons */}
            <div className="flex justify-between mt-2 text-xs">
                <div className="relative flex flex-col items-center">
                    <span className="text-green-600 mb-1">Yes</span>
                    <button
                        onClick={(e) => {
                            e.stopPropagation();
                            setShowMenuYes(!showMenuYes);
                            setShowMenuNo(false);
                        }}
                        className="w-4 h-4 rounded-full bg-green-100 hover:bg-green-200 flex items-center justify-center text-green-600 transition-colors"
                        title="Add node to Yes path"
                    >
                        <svg className="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                    {showMenuYes && (
                        <div className="absolute top-full mt-1 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50 min-w-[100px]">
                            {nodeTypeOptions.map((option) => (
                                <button
                                    key={option.type}
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        handleAddNode(option.type, 'true');
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
                <div className="relative flex flex-col items-center">
                    <span className="text-red-600 mb-1">No</span>
                    <button
                        onClick={(e) => {
                            e.stopPropagation();
                            setShowMenuNo(!showMenuNo);
                            setShowMenuYes(false);
                        }}
                        className="w-4 h-4 rounded-full bg-red-100 hover:bg-red-200 flex items-center justify-center text-red-600 transition-colors"
                        title="Add node to No path"
                    >
                        <svg className="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                    {showMenuNo && (
                        <div className="absolute top-full mt-1 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50 min-w-[100px]">
                            {nodeTypeOptions.map((option) => (
                                <button
                                    key={option.type}
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        handleAddNode(option.type, 'false');
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
        </div>
    );
}

export default memo(ConditionNode);
