<?php

namespace App\Services;

use App\Models\ContactNote;
use App\Models\Goal;
use App\Models\KeyResult;
use App\Models\StrategyDriftScore;
use App\Models\StrategicPlan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StrategyDriftService
{
    /**
     * Calculate alignment between a narrative (contact note) and strategic plans.
     */
    public function calculateAlignment(ContactNote $note): StrategyDriftScore
    {
        if (! $note->embedding) {
            throw new \InvalidArgumentException('Contact note has no embedding. Generate embedding first.');
        }

        // Find the most relevant strategic elements
        $matches = $this->findRelevantStrategicElements($note);

        // Calculate weighted alignment score
        $score = $this->computeAlignmentScore($matches);

        // Determine alignment level
        $level = StrategyDriftScore::levelFromScore($score);

        // Determine drift direction based on history
        $direction = $this->calculateDriftDirection($note, $score);

        // Create and store the score
        $driftScore = StrategyDriftScore::create([
            'org_id' => $note->org_id,
            'contact_note_id' => $note->id,
            'alignment_score' => $score,
            'alignment_level' => $level,
            'matched_context' => $matches,
            'drift_direction' => $direction,
            'scored_by' => 'system',
            'scored_at' => now(),
        ]);

        // Update the note's drift scored timestamp
        $note->update(['drift_scored_at' => now()]);

        return $driftScore;
    }

    /**
     * Find strategic elements most relevant to this narrative.
     */
    protected function findRelevantStrategicElements(ContactNote $note): array
    {
        $orgId = $note->org_id;
        $embedding = $note->embedding;
        $results = collect();

        // Search Strategic Plans
        $plans = StrategicPlan::query()
            ->where('org_id', $orgId)
            ->whereNotNull('embedding')
            ->selectRaw('*, \'StrategicPlan\' as element_type, 1 - (embedding <=> ?) as similarity', [$embedding])
            ->having('similarity', '>=', 0.3)
            ->orderByDesc('similarity')
            ->limit(5)
            ->get();

        foreach ($plans as $plan) {
            $results->push([
                'type' => 'StrategicPlan',
                'id' => $plan->id,
                'title' => $plan->title,
                'similarity' => round($plan->similarity, 4),
            ]);
        }

        // Search Goals
        $goals = Goal::query()
            ->whereHas('strategicPlan', fn ($q) => $q->where('org_id', $orgId))
            ->whereNotNull('embedding')
            ->selectRaw('*, \'Goal\' as element_type, 1 - (embedding <=> ?) as similarity', [$embedding])
            ->having('similarity', '>=', 0.3)
            ->orderByDesc('similarity')
            ->limit(5)
            ->get();

        foreach ($goals as $goal) {
            $results->push([
                'type' => 'Goal',
                'id' => $goal->id,
                'title' => $goal->title,
                'similarity' => round($goal->similarity, 4),
            ]);
        }

        // Search Key Results
        $keyResults = KeyResult::query()
            ->whereHas('goal.strategicPlan', fn ($q) => $q->where('org_id', $orgId))
            ->whereNotNull('embedding')
            ->selectRaw('*, \'KeyResult\' as element_type, 1 - (embedding <=> ?) as similarity', [$embedding])
            ->having('similarity', '>=', 0.3)
            ->orderByDesc('similarity')
            ->limit(5)
            ->get();

        foreach ($keyResults as $kr) {
            $results->push([
                'type' => 'KeyResult',
                'id' => $kr->id,
                'title' => $kr->title ?? $kr->description,
                'similarity' => round($kr->similarity, 4),
            ]);
        }

        // Sort all results by similarity and return top 5
        return $results
            ->sortByDesc('similarity')
            ->take(5)
            ->values()
            ->toArray();
    }

    /**
     * Compute the overall alignment score from matched elements.
     */
    protected function computeAlignmentScore(array $matches): float
    {
        if (empty($matches)) {
            return 0.0;
        }

        // Use a weighted average - highest match counts most
        $weights = [1.0, 0.6, 0.4, 0.3, 0.2];
        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($matches as $index => $match) {
            $weight = $weights[$index] ?? 0.1;
            $weightedSum += $match['similarity'] * $weight;
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? $weightedSum / $totalWeight : 0.0;
    }

    /**
     * Calculate drift direction based on historical scores.
     */
    protected function calculateDriftDirection(ContactNote $note, float $currentScore): string
    {
        // Get previous scores for this note
        $previousScores = StrategyDriftScore::where('contact_note_id', $note->id)
            ->orderByDesc('scored_at')
            ->limit(3)
            ->pluck('alignment_score')
            ->toArray();

        if (empty($previousScores)) {
            return StrategyDriftScore::DIRECTION_STABLE;
        }

        $avgPrevious = array_sum($previousScores) / count($previousScores);
        $diff = $currentScore - $avgPrevious;

        return match (true) {
            $diff > 0.05 => StrategyDriftScore::DIRECTION_IMPROVING,
            $diff < -0.05 => StrategyDriftScore::DIRECTION_DECLINING,
            default => StrategyDriftScore::DIRECTION_STABLE,
        };
    }

    /**
     * Get organization-wide drift summary.
     */
    public function getOrgDriftSummary(int $orgId, int $days = 30): array
    {
        // Handle case where table doesn't exist yet (pre-migration)
        try {
            $scores = StrategyDriftScore::forOrg($orgId)
                ->recent($days)
                ->get();
        } catch (\Illuminate\Database\QueryException $e) {
            // Table doesn't exist yet - return empty state
            return [
                'average_alignment' => null,
                'weak_count' => 0,
                'moderate_count' => 0,
                'strong_count' => 0,
                'total_count' => 0,
                'trend' => 'insufficient_data',
                'top_drift_areas' => [],
            ];
        }

        if ($scores->isEmpty()) {
            return [
                'average_alignment' => null,
                'weak_count' => 0,
                'moderate_count' => 0,
                'strong_count' => 0,
                'total_count' => 0,
                'trend' => 'insufficient_data',
                'top_drift_areas' => [],
            ];
        }

        return [
            'average_alignment' => round($scores->avg('alignment_score'), 4),
            'weak_count' => $scores->where('alignment_level', StrategyDriftScore::LEVEL_WEAK)->count(),
            'moderate_count' => $scores->where('alignment_level', StrategyDriftScore::LEVEL_MODERATE)->count(),
            'strong_count' => $scores->where('alignment_level', StrategyDriftScore::LEVEL_STRONG)->count(),
            'total_count' => $scores->count(),
            'trend' => $this->calculateOrgTrend($scores),
            'top_drift_areas' => $this->identifyDriftAreas($scores),
        ];
    }

    /**
     * Calculate organization trend over the period.
     */
    protected function calculateOrgTrend(Collection $scores): string
    {
        if ($scores->count() < 5) {
            return 'insufficient_data';
        }

        // Split into first half and second half
        $sorted = $scores->sortBy('scored_at')->values();
        $midpoint = (int) floor($sorted->count() / 2);

        $firstHalf = $sorted->take($midpoint);
        $secondHalf = $sorted->skip($midpoint);

        $firstAvg = $firstHalf->avg('alignment_score');
        $secondAvg = $secondHalf->avg('alignment_score');
        $diff = $secondAvg - $firstAvg;

        return match (true) {
            $diff > 0.05 => StrategyDriftScore::DIRECTION_IMPROVING,
            $diff < -0.05 => StrategyDriftScore::DIRECTION_DECLINING,
            default => StrategyDriftScore::DIRECTION_STABLE,
        };
    }

    /**
     * Identify areas with the most drift (weak alignment patterns).
     */
    protected function identifyDriftAreas(Collection $scores): array
    {
        // Find common elements in weak alignment scores
        $weakScores = $scores->where('alignment_level', StrategyDriftScore::LEVEL_WEAK);

        if ($weakScores->isEmpty()) {
            return [];
        }

        // Analyze matched context to find patterns
        $elementCounts = [];

        foreach ($weakScores as $score) {
            foreach ($score->matched_context ?? [] as $match) {
                $key = $match['type'].':'.$match['id'];
                if (! isset($elementCounts[$key])) {
                    $elementCounts[$key] = [
                        'type' => $match['type'],
                        'id' => $match['id'],
                        'title' => $match['title'],
                        'count' => 0,
                        'avg_similarity' => 0,
                        'similarities' => [],
                    ];
                }
                $elementCounts[$key]['count']++;
                $elementCounts[$key]['similarities'][] = $match['similarity'];
            }
        }

        // Calculate averages and sort by count
        foreach ($elementCounts as &$element) {
            $element['avg_similarity'] = round(
                array_sum($element['similarities']) / count($element['similarities']),
                4
            );
            unset($element['similarities']);
        }

        uasort($elementCounts, fn ($a, $b) => $b['count'] <=> $a['count']);

        return array_slice(array_values($elementCounts), 0, 5);
    }

    /**
     * Get notes that need drift scoring.
     */
    public function getNotesNeedingScoring(?int $orgId = null, int $limit = 100): Collection
    {
        $query = ContactNote::query()
            ->whereNotNull('embedding')
            ->where(function ($q) {
                $q->whereNull('drift_scored_at')
                    ->orWhereColumn('updated_at', '>', 'drift_scored_at');
            });

        if ($orgId) {
            $query->where('org_id', $orgId);
        }

        return $query->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Check if an organization has active strategic plans with embeddings.
     */
    public function hasStrategicContext(int $orgId): bool
    {
        return StrategicPlan::where('org_id', $orgId)
            ->whereNotNull('embedding')
            ->exists();
    }

    /**
     * Get plan-specific drift summary.
     */
    public function getPlanDriftSummary(int $planId, int $days = 30): array
    {
        try {
            $scores = StrategyDriftScore::where('strategic_plan_id', $planId)
                ->recent($days)
                ->get();
        } catch (\Illuminate\Database\QueryException $e) {
            return [
                'average_alignment' => null,
                'weak_count' => 0,
                'moderate_count' => 0,
                'strong_count' => 0,
                'total_count' => 0,
                'trend' => 'insufficient_data',
            ];
        }

        if ($scores->isEmpty()) {
            // Fall back to org-level scores if plan-specific doesn't exist
            $plan = StrategicPlan::find($planId);
            if ($plan) {
                return $this->getOrgDriftSummary($plan->org_id, $days);
            }

            return [
                'average_alignment' => null,
                'weak_count' => 0,
                'moderate_count' => 0,
                'strong_count' => 0,
                'total_count' => 0,
                'trend' => 'insufficient_data',
            ];
        }

        return [
            'average_alignment' => round($scores->avg('alignment_score'), 4),
            'weak_count' => $scores->where('alignment_level', StrategyDriftScore::LEVEL_WEAK)->count(),
            'moderate_count' => $scores->where('alignment_level', StrategyDriftScore::LEVEL_MODERATE)->count(),
            'strong_count' => $scores->where('alignment_level', StrategyDriftScore::LEVEL_STRONG)->count(),
            'total_count' => $scores->count(),
            'trend' => $this->calculateOrgTrend($scores),
        ];
    }
}
