import React, { memo } from 'react';
import { Handle, Position, type NodeProps } from '@xyflow/react';
import type { TriggerNodeData } from '../types/workflow';
import { TRIGGER_TYPES } from '../types/workflow';

function TriggerNode({ data, selected }: NodeProps<TriggerNodeData>) {
    const triggerType = TRIGGER_TYPES[data.trigger_type as keyof typeof TRIGGER_TYPES] || {
        label: 'Unknown Trigger',
        icon: 'bolt',
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
                    <div className="text-sm font-medium text-gray-900">{triggerType.label}</div>
                </div>
            </div>

            {/* Conditions Preview */}
            {data.conditions && data.conditions.length > 0 && (
                <div className="mt-2 pt-2 border-t border-gray-100">
                    <div className="text-xs text-gray-500">
                        {data.conditions.length} condition{data.conditions.length !== 1 ? 's' : ''} ({data.logic.toUpperCase()})
                    </div>
                </div>
            )}

            {/* Output Handle */}
            <Handle
                type="source"
                position={Position.Bottom}
                className="!w-3 !h-3 !bg-amber-500 !border-2 !border-white"
            />
        </div>
    );
}

export default memo(TriggerNode);
