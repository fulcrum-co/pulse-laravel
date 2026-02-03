<?php

namespace App\Livewire\Reports\Concerns;

use Illuminate\Support\Str;

trait WithElementDefaults
{
    protected function createDefaultElement(string $type, ?array $config = null): array
    {
        $id = Str::uuid()->toString();

        $defaults = match ($type) {
            'text' => $this->getTextElementDefaults(),
            'chart' => $this->getChartElementDefaults(),
            'table' => $this->getTableElementDefaults(),
            'metric_card' => $this->getMetricCardElementDefaults(),
            'ai_text' => $this->getAiTextElementDefaults(),
            'image' => $this->getImageElementDefaults(),
            'spacer' => $this->getSpacerElementDefaults(),
            default => $this->getGenericElementDefaults(),
        };

        return array_merge([
            'id' => $id,
            'type' => $type,
            'locked' => false,
        ], $defaults, $config ?? []);
    }

    protected function getTextElementDefaults(): array
    {
        return [
            'position' => ['x' => 40, 'y' => $this->getNextY()],
            'size' => ['width' => 400, 'height' => 60],
            'config' => ['content' => '<p>Enter your text here...</p>', 'format' => 'html'],
            'styles' => ['backgroundColor' => 'transparent', 'padding' => 8, 'borderRadius' => 4],
        ];
    }

    protected function getChartElementDefaults(): array
    {
        return [
            'position' => ['x' => 40, 'y' => $this->getNextY()],
            'size' => ['width' => 500, 'height' => 300],
            'config' => [
                'chart_type' => 'line',
                'title' => 'Chart Title',
                'metric_keys' => ['gpa'],
                'colors' => ['#3B82F6'],
            ],
            'styles' => [
                'backgroundColor' => '#FFFFFF',
                'borderRadius' => 8,
                'padding' => 16,
                'borderWidth' => 1,
                'borderColor' => '#E5E7EB',
            ],
        ];
    }

    protected function getTableElementDefaults(): array
    {
        return [
            'position' => ['x' => 40, 'y' => $this->getNextY()],
            'size' => ['width' => 600, 'height' => 250],
            'config' => [
                'title' => 'Data Table',
                'columns' => ['name', 'gpa', 'attendance'],
                'data_source' => 'learners',
                'sortable' => true,
            ],
            'styles' => ['backgroundColor' => '#FFFFFF', 'borderRadius' => 8],
        ];
    }

    protected function getMetricCardElementDefaults(): array
    {
        return [
            'position' => ['x' => 40, 'y' => $this->getNextY()],
            'size' => ['width' => 180, 'height' => 100],
            'config' => [
                'metric_key' => 'gpa',
                'label' => 'GPA',
                'show_trend' => true,
                'comparison_period' => 'last_month',
            ],
            'styles' => [
                'backgroundColor' => '#F0F9FF',
                'borderRadius' => 8,
                'padding' => 16,
            ],
        ];
    }

    protected function getAiTextElementDefaults(): array
    {
        return [
            'position' => ['x' => 40, 'y' => $this->getNextY()],
            'size' => ['width' => 600, 'height' => 150],
            'config' => [
                'prompt' => 'Write a summary of the learner performance data.',
                'format' => 'narrative',
                'context_metrics' => ['gpa', 'attendance_rate', 'wellness_score'],
                'generated_content' => null,
                'generated_at' => null,
            ],
            'styles' => [
                'backgroundColor' => '#F9FAFB',
                'borderRadius' => 8,
                'padding' => 20,
            ],
        ];
    }

    protected function getImageElementDefaults(): array
    {
        return [
            'position' => ['x' => 40, 'y' => $this->getNextY()],
            'size' => ['width' => 300, 'height' => 200],
            'config' => ['src' => null, 'alt' => '', 'fit' => 'contain'],
            'styles' => ['borderRadius' => 4],
        ];
    }

    protected function getSpacerElementDefaults(): array
    {
        return [
            'position' => ['x' => 40, 'y' => $this->getNextY()],
            'size' => ['width' => 600, 'height' => 40],
            'config' => [],
            'styles' => ['backgroundColor' => 'transparent'],
        ];
    }

    protected function getGenericElementDefaults(): array
    {
        return [
            'position' => ['x' => 40, 'y' => $this->getNextY()],
            'size' => ['width' => 200, 'height' => 100],
            'config' => [],
            'styles' => [],
        ];
    }
}
