import React from 'react';
import { createRoot } from 'react-dom/client';
import WorkflowCanvas from './WorkflowCanvas';
import type { WorkflowNode, WorkflowEdge } from './types/workflow';

// Find the mount point and initialize the React app
const mountElement = document.getElementById('workflow-canvas');

if (mountElement) {
    const workflowId = mountElement.dataset.workflowId || '';
    const initialNodes: WorkflowNode[] = JSON.parse(mountElement.dataset.nodes || '[]');
    const initialEdges: WorkflowEdge[] = JSON.parse(mountElement.dataset.edges || '[]');
    const workflowName = mountElement.dataset.workflowName || 'New Workflow';

    const root = createRoot(mountElement);
    root.render(
        <React.StrictMode>
            <WorkflowCanvas
                workflowId={workflowId}
                initialNodes={initialNodes}
                initialEdges={initialEdges}
                workflowName={workflowName}
            />
        </React.StrictMode>
    );
}
