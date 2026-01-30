import type { Node, Edge } from '@xyflow/react';

export type NodeType = 'trigger' | 'condition' | 'delay' | 'action' | 'branch' | 'merge';

export interface TriggerNodeData {
    trigger_type: string;
    conditions: Condition[];
    logic: 'and' | 'or';
}

export interface ConditionNodeData {
    conditions: Condition[];
    logic: 'and' | 'or';
}

export interface DelayNodeData {
    duration: number;
    unit: 'minutes' | 'hours' | 'days';
}

export interface ActionNodeData {
    action_type: string;
    config: Record<string, unknown>;
}

export interface BranchNodeData {
    branches: Branch[];
}

export interface Branch {
    id: string;
    name: string;
    conditions: Condition[];
    logic: 'and' | 'or';
    is_default?: boolean;
}

export interface Condition {
    field: string;
    operator: string;
    value: string | number | boolean | string[];
}

export type WorkflowNodeData =
    | TriggerNodeData
    | ConditionNodeData
    | DelayNodeData
    | ActionNodeData
    | BranchNodeData
    | Record<string, unknown>;

export type WorkflowNode = Node<WorkflowNodeData, NodeType>;
export type WorkflowEdge = Edge;

export interface Workflow {
    _id: string;
    org_id: string;
    name: string;
    description?: string;
    status: 'draft' | 'active' | 'paused' | 'archived';
    mode: 'simple' | 'advanced';
    trigger_type: string;
    trigger_config: Record<string, unknown>;
    nodes: WorkflowNode[];
    edges: WorkflowEdge[];
    settings: WorkflowSettings;
}

export interface WorkflowSettings {
    cooldown_minutes: number;
    max_executions_per_day: number;
    timezone?: string;
    active_hours?: { start: string; end: string };
}

export interface SaveWorkflowResponse {
    success: boolean;
    message: string;
    workflow: Workflow;
}

// Trigger types
export const TRIGGER_TYPES = {
    metric_threshold: { label: 'Metric Threshold', icon: 'chart-bar' },
    metric_change: { label: 'Metric Change', icon: 'arrow-trending-up' },
    survey_response: { label: 'Survey Response', icon: 'clipboard-document-list' },
    survey_answer: { label: 'Survey Answer', icon: 'chat-bubble-left' },
    attendance: { label: 'Attendance', icon: 'calendar' },
    schedule: { label: 'Schedule', icon: 'clock' },
    manual: { label: 'Manual', icon: 'hand-raised' },
} as const;

// Action types
export const ACTION_TYPES = {
    send_sms: { label: 'Send SMS', icon: 'chat-bubble-left', color: '#3B82F6' },
    send_email: { label: 'Send Email', icon: 'envelope', color: '#10B981' },
    send_whatsapp: { label: 'Send WhatsApp', icon: 'chat-bubble-oval-left', color: '#25D366' },
    make_call: { label: 'Make Call', icon: 'phone', color: '#8B5CF6' },
    in_app_notification: { label: 'In-App Notification', icon: 'bell', color: '#F59E0B' },
    webhook: { label: 'Webhook', icon: 'arrow-top-right-on-square', color: '#6366F1' },
    create_task: { label: 'Create Task', icon: 'clipboard-document-check', color: '#EC4899' },
    assign_resource: { label: 'Assign Resource', icon: 'user-plus', color: '#14B8A6' },
    trigger_workflow: { label: 'Trigger Workflow', icon: 'bolt', color: '#F97316' },
} as const;

// Operators
export const OPERATORS = {
    equals: 'Equals',
    not_equals: 'Not Equals',
    greater_than: 'Greater Than',
    less_than: 'Less Than',
    greater_or_equal: 'Greater or Equal',
    less_or_equal: 'Less or Equal',
    contains: 'Contains',
    not_contains: 'Not Contains',
    is_empty: 'Is Empty',
    is_not_empty: 'Is Not Empty',
    in: 'In List',
    not_in: 'Not In List',
} as const;
