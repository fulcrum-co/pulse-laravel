import React, { memo } from 'react';
import { Handle, Position, type NodeProps } from '@xyflow/react';
import type { ConditionNodeData } from '../types/workflow';

function ConditionNode({ data, selected }: NodeProps<ConditionNodeData>) {
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

            {/* Labels */}
            <div className="flex justify-between mt-2 text-xs text-gray-400">
                <span className="text-green-600">Yes</span>
                <span className="text-red-600">No</span>
            </div>
        </div>
    );
}

export default memo(ConditionNode);
