import React, { useCallback, useState, useEffect, useRef } from 'react';
import {
    ReactFlow,
    ReactFlowProvider,
    Controls,
    Background,
    MiniMap,
    addEdge,
    useNodesState,
    useEdgesState,
    useReactFlow,
    type Connection,
    type NodeTypes,
    Panel,
    BackgroundVariant,
} from '@xyflow/react';
import '@xyflow/react/dist/style.css';

import TriggerNode from './nodes/TriggerNode';
import ConditionNode from './nodes/ConditionNode';
import DelayNode from './nodes/DelayNode';
import ActionNode from './nodes/ActionNode';
import BranchNode from './nodes/BranchNode';
import NodePalette from './panels/NodePalette';
import NodeConfigPanel from './panels/NodeConfigPanel';
import type { WorkflowNode, WorkflowEdge } from './types/workflow';

const nodeTypes: NodeTypes = {
    trigger: TriggerNode,
    condition: ConditionNode,
    delay: DelayNode,
    action: ActionNode,
    branch: BranchNode,
};

interface WorkflowCanvasProps {
    workflowId: string;
    initialNodes: WorkflowNode[];
    initialEdges: WorkflowEdge[];
    workflowName: string;
}

// Inner component that uses useReactFlow hook
function WorkflowCanvasInner({
    workflowId,
    initialNodes,
    initialEdges,
    workflowName,
}: WorkflowCanvasProps) {
    const [nodes, setNodes, onNodesChange] = useNodesState(initialNodes);
    const [edges, setEdges, onEdgesChange] = useEdgesState(initialEdges);
    const [selectedNode, setSelectedNode] = useState<WorkflowNode | null>(null);
    const [isSaving, setIsSaving] = useState(false);
    const [lastSaved, setLastSaved] = useState<Date | null>(null);
    const saveTimeoutRef = useRef<NodeJS.Timeout | null>(null);
    const reactFlowWrapper = useRef<HTMLDivElement>(null);
    const { screenToFlowPosition } = useReactFlow();

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // Debug: Log when component mounts
    useEffect(() => {
        console.log('[WorkflowCanvas] Component mounted - version 2026-01-30-v2');
    }, []);

    // Handle adding nodes from branch buttons
    useEffect(() => {
        const handleAddFromBranch = (e: CustomEvent) => {
            const { sourceNodeId, branchIndex, branchId, nodeType } = e.detail;
            const sourceNode = nodes.find((n) => n.id === sourceNodeId);
            if (!sourceNode) return;

            // Calculate position below the source node, offset by branch
            const offsetX = branchIndex === 0 ? -100 : branchIndex === 1 ? 100 : 0;
            const newPosition = {
                x: sourceNode.position.x + offsetX,
                y: sourceNode.position.y + 200,
            };

            // Create the new node
            const newNode: WorkflowNode = {
                id: `${nodeType}-${Date.now()}`,
                type: nodeType as WorkflowNode['type'],
                position: newPosition,
                data: getDefaultNodeData(nodeType),
            };

            // Add node and connect it
            setNodes((nds) => [...nds, newNode]);
            setEdges((eds) =>
                addEdge(
                    {
                        id: `e${sourceNodeId}-${newNode.id}`,
                        source: sourceNodeId,
                        // Only set sourceHandle for branch/condition nodes, not 'default'
                        ...(branchId !== 'default' ? { sourceHandle: branchId } : {}),
                        target: newNode.id,
                        animated: true,
                    },
                    eds
                )
            );
            setSelectedNode(newNode);
        };

        window.addEventListener('addNodeFromBranch', handleAddFromBranch as EventListener);
        return () => {
            window.removeEventListener('addNodeFromBranch', handleAddFromBranch as EventListener);
        };
    }, [nodes, setNodes, setEdges]);

    // Auto-save on changes (debounced)
    useEffect(() => {
        if (saveTimeoutRef.current) {
            clearTimeout(saveTimeoutRef.current);
        }

        saveTimeoutRef.current = setTimeout(() => {
            saveWorkflow();
        }, 2000); // 2 second debounce

        return () => {
            if (saveTimeoutRef.current) {
                clearTimeout(saveTimeoutRef.current);
            }
        };
    }, [nodes, edges]);

    const saveWorkflow = async () => {
        if (!workflowId) return;

        setIsSaving(true);
        try {
            const response = await fetch(`/alerts/${workflowId}/save`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ nodes, edges }),
            });

            if (response.ok) {
                setLastSaved(new Date());
            } else {
                console.error('Failed to save workflow');
            }
        } catch (error) {
            console.error('Error saving workflow:', error);
        } finally {
            setIsSaving(false);
        }
    };

    const onConnect = useCallback(
        (params: Connection) => {
            setEdges((eds) => addEdge({ ...params, animated: true }, eds));
        },
        [setEdges]
    );

    const onNodeClick = useCallback((_: React.MouseEvent, node: WorkflowNode) => {
        setSelectedNode(node);
    }, []);

    const onPaneClick = useCallback(() => {
        setSelectedNode(null);
    }, []);

    const updateNodeData = useCallback(
        (nodeId: string, newData: Record<string, unknown>) => {
            setNodes((nds) =>
                nds.map((node) =>
                    node.id === nodeId
                        ? { ...node, data: { ...node.data, ...newData } }
                        : node
                )
            );
        },
        [setNodes]
    );

    const addNode = useCallback(
        (type: string, position?: { x: number; y: number }) => {
            // Default position: top-center of visible canvas area
            // Stack nodes vertically if multiple are added via click
            const defaultPosition = {
                x: 350,
                y: 50 + nodes.length * 150,
            };
            const newNode: WorkflowNode = {
                id: `${type}-${Date.now()}`,
                type: type as WorkflowNode['type'],
                position: position || defaultPosition,
                data: getDefaultNodeData(type),
            };
            setNodes((nds) => [...nds, newNode]);
            setSelectedNode(newNode);
        },
        [nodes.length, setNodes]
    );

    const deleteNode = useCallback(
        (nodeId: string) => {
            setNodes((nds) => nds.filter((node) => node.id !== nodeId));
            setEdges((eds) =>
                eds.filter((edge) => edge.source !== nodeId && edge.target !== nodeId)
            );
            if (selectedNode?.id === nodeId) {
                setSelectedNode(null);
            }
        },
        [selectedNode, setNodes, setEdges]
    );

    const onDragOver = useCallback((event: React.DragEvent) => {
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
    }, []);

    const onDrop = useCallback(
        (event: React.DragEvent) => {
            event.preventDefault();
            console.log('[WorkflowCanvas] Drop event received');

            const type = event.dataTransfer.getData('application/reactflow');
            console.log('[WorkflowCanvas] Node type from drop:', type);

            if (!type) {
                console.log('[WorkflowCanvas] No type found, ignoring drop');
                return;
            }

            // Use screenToFlowPosition to get the correct position in the flow
            const position = screenToFlowPosition({
                x: event.clientX,
                y: event.clientY,
            });
            console.log('[WorkflowCanvas] Adding node at position:', position);

            addNode(type, position);
        },
        [addNode, screenToFlowPosition]
    );

    return (
        <div className="h-full w-full flex" style={{ minHeight: '500px' }}>
            {/* Node Palette */}
            <div className="w-64 bg-white border-r border-gray-200 flex-shrink-0 overflow-y-auto">
                <NodePalette onAddNode={(type) => addNode(type)} />
            </div>

            {/* Canvas */}
            <div
                ref={reactFlowWrapper}
                className="flex-1"
                style={{ height: '100%' }}
                onDragOver={onDragOver}
                onDrop={onDrop}
            >
                <ReactFlow
                    nodes={nodes}
                    edges={edges}
                    onNodesChange={onNodesChange}
                    onEdgesChange={onEdgesChange}
                    onConnect={onConnect}
                    onNodeClick={onNodeClick}
                    onPaneClick={onPaneClick}
                    nodeTypes={nodeTypes}
                    defaultViewport={{ x: 0, y: 0, zoom: 1 }}
                    snapToGrid
                    snapGrid={[15, 15]}
                    deleteKeyCode={['Backspace', 'Delete']}
                    selectionKeyCode={null}
                    multiSelectionKeyCode={['Shift']}
                    defaultEdgeOptions={{
                        animated: true,
                        style: { strokeWidth: 2 },
                        deletable: true,
                    }}
                    proOptions={{ hideAttribution: true }}
                >
                    <Controls />
                    <MiniMap
                        nodeColor={(node) => {
                            switch (node.type) {
                                case 'trigger':
                                    return '#F59E0B';
                                case 'condition':
                                    return '#8B5CF6';
                                case 'delay':
                                    return '#6366F1';
                                case 'action':
                                    return '#10B981';
                                case 'branch':
                                    return '#EC4899';
                                default:
                                    return '#9CA3AF';
                            }
                        }}
                    />
                    <Background variant={BackgroundVariant.Dots} gap={15} size={1} />

                    {/* Save Status Panel */}
                    <Panel position="top-right" className="bg-white rounded-lg shadow-sm border border-gray-200 px-3 py-2">
                        <div className="flex items-center gap-2 text-sm">
                            {isSaving ? (
                                <>
                                    <div className="w-2 h-2 bg-yellow-400 rounded-full animate-pulse" />
                                    <span className="text-gray-600">Saving...</span>
                                </>
                            ) : lastSaved ? (
                                <>
                                    <div className="w-2 h-2 bg-green-400 rounded-full" />
                                    <span className="text-gray-600">
                                        Saved {lastSaved.toLocaleTimeString()}
                                    </span>
                                </>
                            ) : (
                                <>
                                    <div className="w-2 h-2 bg-gray-300 rounded-full" />
                                    <span className="text-gray-400">Not saved</span>
                                </>
                            )}
                        </div>
                    </Panel>
                </ReactFlow>
            </div>

            {/* Config Panel */}
            {selectedNode && (
                <div className="w-80 bg-white border-l border-gray-200 flex-shrink-0 overflow-y-auto">
                    <NodeConfigPanel
                        node={selectedNode}
                        onUpdate={(data) => updateNodeData(selectedNode.id, data)}
                        onDelete={() => deleteNode(selectedNode.id)}
                        onClose={() => setSelectedNode(null)}
                    />
                </div>
            )}
        </div>
    );
}

function getDefaultNodeData(type: string): Record<string, unknown> {
    switch (type) {
        case 'trigger':
            return {
                trigger_type: 'metric_threshold',
                conditions: [],
                logic: 'and',
            };
        case 'condition':
            return {
                conditions: [],
                logic: 'and',
            };
        case 'delay':
            return {
                duration: 1,
                unit: 'hours',
            };
        case 'action':
            return {
                action_type: 'send_sms',
                config: {
                    recipients: [],
                    message: '',
                },
            };
        case 'branch':
            return {
                branches: [
                    { id: '1', name: 'Yes', conditions: [], logic: 'and' },
                    { id: '2', name: 'No', conditions: [], logic: 'and', is_default: true },
                ],
            };
        default:
            return {};
    }
}

// Wrapper component that provides ReactFlowProvider
export default function WorkflowCanvas(props: WorkflowCanvasProps) {
    return (
        <ReactFlowProvider>
            <WorkflowCanvasInner {...props} />
        </ReactFlowProvider>
    );
}
