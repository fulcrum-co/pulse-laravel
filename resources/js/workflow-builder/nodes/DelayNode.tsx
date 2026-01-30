import React, { memo } from 'react';
import { Handle, Position, type NodeProps } from '@xyflow/react';
import type { DelayNodeData } from '../types/workflow';

function DelayNode({ data, selected }: NodeProps<DelayNodeData>) {
    const formatDuration = () => {
        const duration = data.duration || 1;
        const unit = data.unit || 'hours';
        return `${duration} ${unit}`;
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
        </div>
    );
}

export default memo(DelayNode);
