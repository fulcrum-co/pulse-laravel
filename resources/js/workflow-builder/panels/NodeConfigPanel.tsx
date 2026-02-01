import React, { useState, useEffect } from 'react';
import type { Node } from '@xyflow/react';
import type {
    TriggerNodeData,
    ConditionNodeData,
    DelayNodeData,
    ActionNodeData,
    BranchNodeData,
} from '../types/workflow';
import { TRIGGER_TYPES, ACTION_TYPES, OPERATORS } from '../types/workflow';

interface NodeConfigPanelProps {
    node: Node;
    onUpdate: (data: Record<string, unknown>) => void;
    onClose: () => void;
    onDelete: () => void;
}

export default function NodeConfigPanel({ node, onUpdate, onClose, onDelete }: NodeConfigPanelProps) {
    const [localData, setLocalData] = useState<Record<string, unknown>>(node.data);

    useEffect(() => {
        setLocalData(node.data);
    }, [node.id, node.data]);

    const updateField = (field: string, value: unknown) => {
        const newData = { ...localData, [field]: value };
        setLocalData(newData);
        onUpdate(newData);
    };

    const updateNestedField = (parent: string, field: string, value: unknown) => {
        const parentObj = (localData[parent] as Record<string, unknown>) || {};
        const newParent = { ...parentObj, [field]: value };
        updateField(parent, newParent);
    };

    const renderTriggerConfig = () => {
        const data = localData as TriggerNodeData;
        return (
            <div className="space-y-4">
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                        Trigger Type
                    </label>
                    <select
                        value={data.trigger_type || ''}
                        onChange={(e) => updateField('trigger_type', e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                    >
                        <option value="">Select trigger...</option>
                        {Object.entries(TRIGGER_TYPES).map(([key, config]) => (
                            <option key={key} value={key}>{config.label}</option>
                        ))}
                    </select>
                </div>

                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                        Condition Logic
                    </label>
                    <select
                        value={data.logic || 'and'}
                        onChange={(e) => updateField('logic', e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                    >
                        <option value="and">ALL conditions must match (AND)</option>
                        <option value="or">ANY condition can match (OR)</option>
                    </select>
                </div>

                <ConditionBuilder
                    conditions={data.conditions || []}
                    onChange={(conditions) => updateField('conditions', conditions)}
                />
            </div>
        );
    };

    const renderConditionConfig = () => {
        const data = localData as ConditionNodeData;
        return (
            <div className="space-y-4">
                <p className="text-sm text-gray-500">
                    Define when the workflow should take the "Yes" or "No" path.
                </p>

                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                        Field
                    </label>
                    <input
                        type="text"
                        value={data.field || ''}
                        onChange={(e) => updateField('field', e.target.value)}
                        placeholder="e.g., contact.gpa, metric.value"
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                    />
                </div>

                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                        Operator
                    </label>
                    <select
                        value={data.operator || ''}
                        onChange={(e) => updateField('operator', e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                    >
                        <option value="">Select operator...</option>
                        {Object.entries(OPERATORS).map(([key, label]) => (
                            <option key={key} value={key}>{label}</option>
                        ))}
                    </select>
                </div>

                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                        Value
                    </label>
                    <input
                        type="text"
                        value={data.value !== undefined ? String(data.value) : ''}
                        onChange={(e) => updateField('value', e.target.value)}
                        placeholder="Comparison value"
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                    />
                </div>
            </div>
        );
    };

    const renderDelayConfig = () => {
        const data = localData as DelayNodeData;
        return (
            <div className="space-y-4">
                <p className="text-sm text-gray-500">
                    Pause the workflow for a specified duration before continuing.
                </p>

                <div className="flex gap-3">
                    <div className="flex-1">
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Duration
                        </label>
                        <input
                            type="number"
                            min="1"
                            value={data.duration || 1}
                            onChange={(e) => updateField('duration', parseInt(e.target.value) || 1)}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        />
                    </div>

                    <div className="flex-1">
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Unit
                        </label>
                        <select
                            value={data.unit || 'hours'}
                            onChange={(e) => updateField('unit', e.target.value)}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="minutes">Minutes</option>
                            <option value="hours">Hours</option>
                            <option value="days">Days</option>
                        </select>
                    </div>
                </div>
            </div>
        );
    };

    const renderActionConfig = () => {
        const data = localData as ActionNodeData;
        return (
            <div className="space-y-4">
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                        Action Type
                    </label>
                    <select
                        value={data.action_type || ''}
                        onChange={(e) => updateField('action_type', e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                    >
                        <option value="">Select action...</option>
                        {Object.entries(ACTION_TYPES).map(([key, config]) => (
                            <option key={key} value={key}>{config.label}</option>
                        ))}
                    </select>
                </div>

                {data.action_type && renderActionTypeConfig(data)}
            </div>
        );
    };

    const renderActionTypeConfig = (data: ActionNodeData) => {
        const config = data.config || {};

        switch (data.action_type) {
            case 'send_email':
            case 'send_sms':
            case 'send_whatsapp':
                return (
                    <>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Recipients
                            </label>
                            <input
                                type="text"
                                value={(config.recipients || []).join(', ')}
                                onChange={(e) => updateNestedField('config', 'recipients', e.target.value.split(',').map(s => s.trim()).filter(Boolean))}
                                placeholder="Enter user IDs or roles, comma-separated"
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                            <p className="text-xs text-gray-400 mt-1">Use: user IDs, role:admin, context:contact_id</p>
                        </div>
                        {data.action_type === 'send_email' && (
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Subject
                                </label>
                                <input
                                    type="text"
                                    value={config.subject || ''}
                                    onChange={(e) => updateNestedField('config', 'subject', e.target.value)}
                                    placeholder="Email subject line"
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                />
                            </div>
                        )}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Message
                            </label>
                            <textarea
                                value={config.message || config.body || ''}
                                onChange={(e) => updateNestedField('config', data.action_type === 'send_email' ? 'body' : 'message', e.target.value)}
                                placeholder="Message content. Use {{contact.name}} for variables."
                                rows={4}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                            <p className="text-xs text-gray-400 mt-1">Variables: {'{{contact.name}}'}, {'{{metric.value}}'}, {'{{trigger.field}}'}</p>
                        </div>
                    </>
                );

            case 'webhook':
                return (
                    <>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                URL
                            </label>
                            <input
                                type="url"
                                value={config.url || ''}
                                onChange={(e) => updateNestedField('config', 'url', e.target.value)}
                                placeholder="https://api.example.com/webhook"
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Method
                            </label>
                            <select
                                value={config.method || 'POST'}
                                onChange={(e) => updateNestedField('config', 'method', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            >
                                <option value="POST">POST</option>
                                <option value="PUT">PUT</option>
                                <option value="PATCH">PATCH</option>
                            </select>
                        </div>
                    </>
                );

            case 'create_task':
                return (
                    <>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Task Title
                            </label>
                            <input
                                type="text"
                                value={config.title || ''}
                                onChange={(e) => updateNestedField('config', 'title', e.target.value)}
                                placeholder="Follow up with {{contact.name}}"
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Description
                            </label>
                            <textarea
                                value={config.description || ''}
                                onChange={(e) => updateNestedField('config', 'description', e.target.value)}
                                placeholder="Task details..."
                                rows={3}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Assign To
                            </label>
                            <input
                                type="text"
                                value={config.assignee || ''}
                                onChange={(e) => updateNestedField('config', 'assignee', e.target.value)}
                                placeholder="User ID or role"
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                        </div>
                    </>
                );

            case 'in_app_notification':
                return (
                    <>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Recipients
                            </label>
                            <input
                                type="text"
                                value={(config.recipients || []).join(', ')}
                                onChange={(e) => updateNestedField('config', 'recipients', e.target.value.split(',').map(s => s.trim()).filter(Boolean))}
                                placeholder="Enter user IDs or roles, comma-separated"
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                            <p className="text-xs text-gray-400 mt-1">Use: user IDs, role:admin, context:contact_id</p>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Title
                            </label>
                            <input
                                type="text"
                                value={config.title || ''}
                                onChange={(e) => updateNestedField('config', 'title', e.target.value)}
                                placeholder="{{contact.name}} needs attention"
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Message
                            </label>
                            <textarea
                                value={config.message || ''}
                                onChange={(e) => updateNestedField('config', 'message', e.target.value)}
                                placeholder="Notification message content..."
                                rows={3}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                            <p className="text-xs text-gray-400 mt-1">Variables: {'{{contact.name}}'}, {'{{metric.value}}'}</p>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Priority
                            </label>
                            <select
                                value={config.priority || 'normal'}
                                onChange={(e) => updateNestedField('config', 'priority', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            >
                                <option value="low">Low</option>
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Action URL (optional)
                            </label>
                            <input
                                type="text"
                                value={config.url || ''}
                                onChange={(e) => updateNestedField('config', 'url', e.target.value)}
                                placeholder="/students/{{contact_id}}"
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                            <p className="text-xs text-gray-400 mt-1">Deep link when notification is clicked</p>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Action Label (optional)
                            </label>
                            <input
                                type="text"
                                value={config.action_label || ''}
                                onChange={(e) => updateNestedField('config', 'action_label', e.target.value)}
                                placeholder="View Details"
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                        </div>
                    </>
                );

            default:
                return (
                    <p className="text-sm text-gray-500 italic">
                        Configuration options will appear here based on the selected action type.
                    </p>
                );
        }
    };

    const renderBranchConfig = () => {
        const data = localData as BranchNodeData;
        const branches = data.branches || [{ id: '1', label: 'Path 1' }, { id: '2', label: 'Path 2' }];

        return (
            <div className="space-y-4">
                <p className="text-sm text-gray-500">
                    Split the workflow into multiple parallel paths.
                </p>

                <div className="space-y-3">
                    {branches.map((branch, index) => (
                        <div key={branch.id} className="flex gap-2">
                            <input
                                type="text"
                                value={branch.label || ''}
                                onChange={(e) => {
                                    const newBranches = [...branches];
                                    newBranches[index] = { ...branch, label: e.target.value };
                                    updateField('branches', newBranches);
                                }}
                                placeholder={`Path ${index + 1} label`}
                                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            />
                            {branches.length > 2 && (
                                <button
                                    onClick={() => {
                                        const newBranches = branches.filter((_, i) => i !== index);
                                        updateField('branches', newBranches);
                                    }}
                                    className="px-2 text-gray-400 hover:text-red-500"
                                >
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            )}
                        </div>
                    ))}
                </div>

                {branches.length < 5 && (
                    <button
                        onClick={() => {
                            const newBranch = { id: String(Date.now()), label: `Path ${branches.length + 1}` };
                            updateField('branches', [...branches, newBranch]);
                        }}
                        className="w-full px-3 py-2 border border-dashed border-gray-300 rounded-lg text-sm text-gray-500 hover:border-orange-400 hover:text-orange-600 transition-colors"
                    >
                        + Add Branch
                    </button>
                )}
            </div>
        );
    };

    const renderConfigByType = () => {
        switch (node.type) {
            case 'trigger':
                return renderTriggerConfig();
            case 'condition':
                return renderConditionConfig();
            case 'delay':
                return renderDelayConfig();
            case 'action':
                return renderActionConfig();
            case 'branch':
                return renderBranchConfig();
            default:
                return <p className="text-gray-500">Unknown node type</p>;
        }
    };

    const getNodeTitle = () => {
        switch (node.type) {
            case 'trigger': return 'Configure Trigger';
            case 'condition': return 'Configure Condition';
            case 'delay': return 'Configure Delay';
            case 'action': return 'Configure Action';
            case 'branch': return 'Configure Split';
            default: return 'Node Configuration';
        }
    };

    const getTitleColor = () => {
        switch (node.type) {
            case 'trigger': return 'text-amber-600';
            case 'condition': return 'text-purple-600';
            case 'delay': return 'text-indigo-600';
            case 'action': return 'text-emerald-600';
            case 'branch': return 'text-orange-600';
            default: return 'text-gray-600';
        }
    };

    return (
        <div className="bg-white rounded-xl border border-gray-200 shadow-lg w-80 max-h-[calc(100vh-200px)] flex flex-col">
            {/* Header */}
            <div className="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                <h3 className={`font-semibold ${getTitleColor()}`}>
                    {getNodeTitle()}
                </h3>
                <button
                    onClick={onClose}
                    className="p-1 text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {/* Content */}
            <div className="flex-1 overflow-y-auto p-4">
                {renderConfigByType()}
            </div>

            {/* Footer */}
            <div className="px-4 py-3 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                <button
                    onClick={onDelete}
                    className="w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                >
                    Delete Node
                </button>
            </div>
        </div>
    );
}

// Condition Builder sub-component
interface Condition {
    field: string;
    operator: string;
    value: string | number | boolean;
}

interface ConditionBuilderProps {
    conditions: Condition[];
    onChange: (conditions: Condition[]) => void;
}

function ConditionBuilder({ conditions, onChange }: ConditionBuilderProps) {
    const addCondition = () => {
        onChange([...conditions, { field: '', operator: 'equals', value: '' }]);
    };

    const updateCondition = (index: number, field: keyof Condition, value: string) => {
        const newConditions = [...conditions];
        newConditions[index] = { ...newConditions[index], [field]: value };
        onChange(newConditions);
    };

    const removeCondition = (index: number) => {
        onChange(conditions.filter((_, i) => i !== index));
    };

    return (
        <div className="space-y-3">
            <label className="block text-sm font-medium text-gray-700">
                Conditions
            </label>

            {conditions.length === 0 && (
                <p className="text-sm text-gray-400 italic">No conditions set. Add one below.</p>
            )}

            {conditions.map((condition, index) => (
                <div key={index} className="p-3 bg-gray-50 rounded-lg space-y-2">
                    <div className="flex justify-between items-center">
                        <span className="text-xs font-medium text-gray-500">Condition {index + 1}</span>
                        <button
                            onClick={() => removeCondition(index)}
                            className="text-gray-400 hover:text-red-500"
                        >
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <input
                        type="text"
                        value={condition.field}
                        onChange={(e) => updateCondition(index, 'field', e.target.value)}
                        placeholder="Field (e.g., contact.gpa)"
                        className="w-full px-2 py-1.5 text-sm border border-gray-200 rounded focus:ring-1 focus:ring-amber-500"
                    />

                    <select
                        value={condition.operator}
                        onChange={(e) => updateCondition(index, 'operator', e.target.value)}
                        className="w-full px-2 py-1.5 text-sm border border-gray-200 rounded focus:ring-1 focus:ring-amber-500"
                    >
                        {Object.entries(OPERATORS).map(([key, label]) => (
                            <option key={key} value={key}>{label}</option>
                        ))}
                    </select>

                    <input
                        type="text"
                        value={String(condition.value)}
                        onChange={(e) => updateCondition(index, 'value', e.target.value)}
                        placeholder="Value"
                        className="w-full px-2 py-1.5 text-sm border border-gray-200 rounded focus:ring-1 focus:ring-amber-500"
                    />
                </div>
            ))}

            <button
                onClick={addCondition}
                className="w-full px-3 py-2 border border-dashed border-gray-300 rounded-lg text-sm text-gray-500 hover:border-amber-400 hover:text-amber-600 transition-colors"
            >
                + Add Condition
            </button>
        </div>
    );
}
