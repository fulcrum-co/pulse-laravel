<?php

namespace App\Livewire\Reports\Concerns;

trait WithSmartBlocks
{
    public array $smartBlockTypes = [
        'data_table' => [
            'label' => 'Data Table',
            'icon' => 'table',
            'description' => 'Display data in a table format',
        ],
        'chart' => [
            'label' => 'Chart',
            'icon' => 'chart-bar',
            'description' => 'Visualize data with charts',
        ],
        'metric' => [
            'label' => 'Metric Card',
            'icon' => 'presentation-chart-line',
            'description' => 'Display a single metric with trend',
        ],
        'comparison' => [
            'label' => 'Comparison',
            'icon' => 'scale',
            'description' => 'Compare two or more values',
        ],
        'progress' => [
            'label' => 'Progress Bar',
            'icon' => 'chart-bar-square',
            'description' => 'Show progress towards a goal',
        ],
        'list' => [
            'label' => 'Dynamic List',
            'icon' => 'list-bullet',
            'description' => 'Display a list of items',
        ],
    ];

    public function addSmartBlock(string $type, int $x = 100, int $y = 100): void
    {
        if (!isset($this->smartBlockTypes[$type])) {
            return;
        }

        $blockConfig = $this->getSmartBlockConfig($type);

        $element = [
            'id' => uniqid('smart_'),
            'type' => 'smart_block',
            'smart_type' => $type,
            'x' => $x,
            'y' => $y,
            'width' => $blockConfig['width'] ?? 400,
            'height' => $blockConfig['height'] ?? 300,
            'config' => $blockConfig['config'] ?? [],
            'data_source' => null,
            'refresh_interval' => null,
        ];

        if (method_exists($this, 'addElement')) {
            $this->addElement('smart_block', $element);
        } else {
            $this->elements[] = $element;
        }
    }

    protected function getSmartBlockConfig(string $type): array
    {
        return match ($type) {
            'data_table' => [
                'width' => 500,
                'height' => 300,
                'config' => [
                    'columns' => [],
                    'sortable' => true,
                    'paginated' => false,
                    'rows_per_page' => 10,
                ],
            ],
            'chart' => [
                'width' => 400,
                'height' => 300,
                'config' => [
                    'chart_type' => 'bar',
                    'show_legend' => true,
                    'show_labels' => true,
                ],
            ],
            'metric' => [
                'width' => 200,
                'height' => 120,
                'config' => [
                    'label' => 'Metric',
                    'value' => '0',
                    'trend' => null,
                    'trend_direction' => 'up',
                ],
            ],
            'comparison' => [
                'width' => 350,
                'height' => 200,
                'config' => [
                    'items' => [],
                    'show_difference' => true,
                ],
            ],
            'progress' => [
                'width' => 300,
                'height' => 80,
                'config' => [
                    'value' => 0,
                    'max' => 100,
                    'label' => 'Progress',
                    'show_percentage' => true,
                ],
            ],
            'list' => [
                'width' => 300,
                'height' => 250,
                'config' => [
                    'items' => [],
                    'numbered' => false,
                    'show_icons' => false,
                ],
            ],
            default => [
                'width' => 300,
                'height' => 200,
                'config' => [],
            ],
        };
    }

    public function updateSmartBlockData(string $elementId, array $data): void
    {
        if (method_exists($this, 'updateElement')) {
            $this->updateElement($elementId, ['data' => $data]);
        }
    }

    public function setSmartBlockDataSource(string $elementId, string $source, ?array $params = null): void
    {
        if (method_exists($this, 'updateElement')) {
            $this->updateElement($elementId, [
                'data_source' => $source,
                'data_params' => $params,
            ]);
        }
    }

    public function refreshSmartBlock(string $elementId): void
    {
        // Trigger data refresh for a smart block
        $this->dispatch('refreshSmartBlock', elementId: $elementId);
    }
}
