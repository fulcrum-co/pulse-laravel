import React, { memo } from 'react';
import { Handle, Position, type NodeProps } from '@xyflow/react';
import type { BranchNodeData } from '../types/workflow';

function BranchNode({ data, selected }: NodeProps<BranchNodeData>) {
    const branchCount = data.branches?.length || 2;

    return (
        <div
            className={`
                px-4 py-3 rounded-lg border-2 bg-white shadow-sm min-w-[180px]
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
                    <div className="text-sm text-gray-500">{branchCount} branches</div>
                </div>
            </div>

            {/* Branch Labels */}
            <div className="flex justify-between text-xs text-gray-400 px-2">
                {data.branches?.map((branch, index) => (
                    <span key={index} className="truncate max-w-[60px]">
                        {branch.label || `Path ${index + 1}`}
                    </span>
                )) || (
                    <>
                        <span>Path 1</span>
                        <span>Path 2</span>
                    </>
                )}
            </div>

            {/* Output Handles - Dynamic based on branch count */}
            {(data.branches || [{ id: '1' }, { id: '2' }]).map((branch, index) => {
                const total = data.branches?.length || 2;
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
