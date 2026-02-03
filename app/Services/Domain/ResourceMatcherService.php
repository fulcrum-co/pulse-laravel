<?php

declare(strict_types=1);

namespace App\Services\Domain;

/**
 * ResourceMatcherService
 *
 * Encapsulates resource matching and scoring algorithms.
 * Provides reusable matching logic for providers, programs, and resources.
 *
 * @package App\Services\Domain
 */
class ResourceMatcherService
{
    /**
     * Provider specialty match scoring configuration
     */
    private const SPECIALTY_MATCH_WEIGHT = 50;
    private const SPECIALTY_MATCH_POINTS = 10;

    /**
     * Rating score configuration
     */
    private const RATING_WEIGHT = 15;
    private const MAX_RATING = 5;

    /**
     * Verification bonus
     */
    private const VERIFICATION_BONUS = 10;

    /**
     * Availability scoring
     */
    private const AVAILABILITY_WEIGHTS = [
        'both' => 15,      // Both remote and in-person
        'remote' => 10,    // Remote only
        'in_person' => 5,  // In-person only
    ];

    /**
     * Insurance discount bonus
     */
    private const INSURANCE_BONUS = 10;

    /**
     * Program cost structure weights
     */
    private const PROGRAM_COST_WEIGHTS = [
        'free' => 20,
        'sliding_scale' => 15,
        'insurance' => 15,
    ];

    /**
     * Program location type weights
     */
    private const PROGRAM_LOCATION_WEIGHTS = [
        'hybrid' => 15,
        'virtual' => 10,
        'in_person' => 5,
    ];

    /**
     * Program duration fit weights
     */
    private const PROGRAM_DURATION_WEIGHTS = [
        'short_term' => 15,   // <= 8 weeks
        'medium_term' => 10,  // <= 16 weeks
        'long_term' => 5,     // > 16 weeks
    ];

    /**
     * Calculate match score for a provider.
     */
    public function calculateProviderScore(object $provider, array $needs, object $learner): array
    {
        $score = 0;
        $factors = [];

        // Specialty match (up to 50 points)
        $specialtyMatch = $this->scoreSpecialtyMatch($provider, $needs);
        $score += $specialtyMatch;
        if ($specialtyMatch > 0) {
            $factors[] = 'Specializes in relevant areas';
        }

        // Rating bonus (up to 15 points)
        $ratingBonus = $this->scoreRating($provider);
        $score += $ratingBonus;
        if ($ratingBonus >= 10) {
            $factors[] = 'Highly rated by other learners';
        }

        // Verification bonus (10 points)
        if ($this->isVerified($provider)) {
            $score += self::VERIFICATION_BONUS;
            $factors[] = 'Verified credentials';
        }

        // Availability bonus
        $availabilityBonus = $this->scoreAvailability($provider);
        $score += $availabilityBonus;
        if ($availabilityBonus === self::AVAILABILITY_WEIGHTS['both']) {
            $factors[] = 'Flexible availability (remote and in-person)';
        } elseif ($availabilityBonus === self::AVAILABILITY_WEIGHTS['remote']) {
            $factors[] = 'Remote sessions available';
        } elseif ($availabilityBonus === self::AVAILABILITY_WEIGHTS['in_person']) {
            $factors[] = 'In-person sessions available';
        }

        // Insurance bonus
        if ($this->canInsuranceApply($provider, $learner)) {
            $score += self::INSURANCE_BONUS;
            $factors[] = 'Accepts insurance (cost-effective option)';
        }

        return [
            'total' => min(100, $score),
            'factors' => $factors,
            'specialty_score' => $specialtyMatch,
            'rating_score' => $ratingBonus,
            'availability_score' => $availabilityBonus,
        ];
    }

    /**
     * Calculate match score for a program.
     */
    public function calculateProgramScore(object $program, array $needs, object $learner): array
    {
        $score = 0;
        $factors = [];

        // Target needs match (up to 50 points)
        $needMatch = $this->scoreNeedMatch($program, $needs);
        $score += $needMatch;
        if ($needMatch > 0) {
            $factors[] = 'Addresses relevant needs';
        }

        // Cost structure bonus
        $costBonus = $this->scoreCostStructure($program, $learner);
        $score += $costBonus;
        if ($costBonus > 0) {
            $factors[] = $this->getCostLabel($program);
        }

        // Location type bonus
        $locationBonus = $this->scoreLocationStyle($program);
        $score += $locationBonus;
        if ($locationBonus > 0) {
            $factors[] = $this->getLocationLabel($program);
        }

        // Duration fit bonus
        $durationBonus = $this->scoreDuration($program);
        $score += $durationBonus;
        if ($durationBonus > 0) {
            $factors[] = $this->getDurationLabel($program);
        }

        return [
            'total' => min(100, $score),
            'factors' => $factors,
            'need_match_score' => $needMatch,
            'cost_score' => $costBonus,
            'location_score' => $locationBonus,
            'duration_score' => $durationBonus,
        ];
    }

    /**
     * Perform fuzzy string matching for needs/specialties.
     */
    public function fuzzyMatch(string $needle, string $haystack): bool
    {
        $needle = strtolower(trim($needle));
        $haystack = strtolower(trim($haystack));

        // Direct match
        if ($needle === $haystack) {
            return true;
        }

        // Contains match
        if (str_contains($haystack, $needle) || str_contains($needle, $haystack)) {
            return true;
        }

        // Synonym matching
        return $this->checkSynonymMatch($needle, $haystack);
    }

    private function scoreSpecialtyMatch(object $provider, array $needs): int
    {
        $specialties = $this->getProviderSpecialties($provider);

        if (empty($specialties)) {
            return 0;
        }

        $matchCount = 0;
        foreach ($needs as $need) {
            foreach ($specialties as $specialty) {
                if ($this->fuzzyMatch($need, $specialty)) {
                    $matchCount++;
                }
            }
        }

        return min(self::SPECIALTY_MATCH_WEIGHT, $matchCount * self::SPECIALTY_MATCH_POINTS);
    }

    private function scoreRating(object $provider): int
    {
        $rating = $this->getProviderRating($provider);

        if ($rating <= 0) {
            return 0;
        }

        return (int) (($rating / self::MAX_RATING) * self::RATING_WEIGHT);
    }

    private function scoreAvailability(object $provider): int
    {
        $servesRemote = $this->servesRemote($provider);
        $servesInPerson = $this->servesInPerson($provider);

        if ($servesRemote && $servesInPerson) {
            return self::AVAILABILITY_WEIGHTS['both'];
        } elseif ($servesRemote) {
            return self::AVAILABILITY_WEIGHTS['remote'];
        } elseif ($servesInPerson) {
            return self::AVAILABILITY_WEIGHTS['in_person'];
        }

        return 0;
    }

    private function scoreNeedMatch(object $program, array $needs): int
    {
        $programNeeds = $this->getProgramNeeds($program);

        if (empty($programNeeds)) {
            return 0;
        }

        $matchCount = 0;
        foreach ($needs as $need) {
            foreach ($programNeeds as $programNeed) {
                if ($this->fuzzyMatch($need, $programNeed)) {
                    $matchCount++;
                }
            }
        }

        return min(self::SPECIALTY_MATCH_WEIGHT, $matchCount * self::SPECIALTY_MATCH_POINTS);
    }

    private function scoreCostStructure(object $program, object $learner): int
    {
        $costStructure = $this->getProgramCostStructure($program);

        if ($costStructure === 'free') {
            return self::PROGRAM_COST_WEIGHTS['free'];
        }

        if ($costStructure === 'sliding_scale') {
            return self::PROGRAM_COST_WEIGHTS['sliding_scale'];
        }

        if ($costStructure === 'insurance' && $this->hasFinancialNeed($learner)) {
            return self::PROGRAM_COST_WEIGHTS['insurance'];
        }

        return 0;
    }

    private function scoreLocationStyle(object $program): int
    {
        $locationType = $this->getProgramLocationType($program);

        return self::PROGRAM_LOCATION_WEIGHTS[$locationType] ?? 0;
    }

    private function scoreDuration(object $program): int
    {
        $weeks = $this->getProgramDurationWeeks($program);

        if ($weeks === null) {
            return 0;
        }

        if ($weeks <= 8) {
            return self::PROGRAM_DURATION_WEIGHTS['short_term'];
        }

        if ($weeks <= 16) {
            return self::PROGRAM_DURATION_WEIGHTS['medium_term'];
        }

        return self::PROGRAM_DURATION_WEIGHTS['long_term'];
    }

    private function getProviderSpecialties(object $provider): array
    {
        return (array) ($provider->specialty_areas ?? $provider->specialties ?? []);
    }

    private function getProgramNeeds(object $program): array
    {
        return (array) ($program->target_needs ?? []);
    }

    private function getProviderRating(object $provider): float
    {
        return (float) ($provider->ratings_average ?? $provider->rating ?? 0);
    }

    private function isVerified(object $provider): bool
    {
        return !empty($provider->verified_at) || ($provider->verified ?? false);
    }

    private function servesRemote(object $provider): bool
    {
        return (bool) ($provider->serves_remote ?? false);
    }

    private function servesInPerson(object $provider): bool
    {
        return (bool) ($provider->serves_in_person ?? false);
    }

    private function canInsuranceApply(object $provider, object $learner): bool
    {
        $providerAccepts = (bool) ($provider->accepts_insurance ?? false);
        $learnerNeed = (bool) ($learner->free_reduced_lunch ?? false);

        return $providerAccepts && $learnerNeed;
    }

    private function hasFinancialNeed(object $learner): bool
    {
        return (bool) ($learner->free_reduced_lunch ?? false);
    }

    private function getProgramCostStructure(object $program): string
    {
        return (string) ($program->cost_structure ?? 'unknown');
    }

    private function getProgramLocationType(object $program): string
    {
        return (string) ($program->location_type ?? 'in_person');
    }

    private function getProgramDurationWeeks(object $program): ?int
    {
        return (int) ($program->duration_weeks ?? null) ?: null;
    }

    private function getCostLabel(object $program): string
    {
        return match ($this->getProgramCostStructure($program)) {
            'free' => 'Free program',
            'sliding_scale' => 'Sliding scale pricing available',
            'insurance' => 'Insurance accepted',
            default => 'Cost information available',
        };
    }

    private function getLocationLabel(object $program): string
    {
        return match ($this->getProgramLocationType($program)) {
            'hybrid' => 'Flexible format (in-person and virtual)',
            'virtual' => 'Virtual program (accessible from anywhere)',
            'in_person' => 'In-person program',
            default => 'Program available',
        };
    }

    private function getDurationLabel(object $program): string
    {
        $weeks = $this->getProgramDurationWeeks($program);

        if ($weeks === null) {
            return 'Program duration available';
        }

        if ($weeks <= 8) {
            return 'Short-term commitment';
        }

        if ($weeks <= 16) {
            return 'Medium-term program';
        }

        return 'Long-term support';
    }

    private function checkSynonymMatch(string $needle, string $haystack): bool
    {
        $synonyms = [
            'therapy' => ['therapist', 'therapeutic', 'counseling'],
            'counseling' => ['counselor', 'therapy', 'support'],
            'tutoring' => ['tutor', 'academic support', 'homework help'],
            'mental health' => ['therapy', 'counseling', 'emotional', 'psychological'],
            'anxiety' => ['stress', 'worry', 'nervous'],
            'depression' => ['sad', 'mood', 'emotional'],
            'adhd' => ['attention', 'focus', 'executive function'],
            'learning' => ['academic', 'educational', 'study'],
        ];

        foreach ($synonyms as $key => $related) {
            if (str_contains($needle, $key) || str_contains($key, $needle)) {
                foreach ($related as $synonym) {
                    if (str_contains($haystack, $synonym)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
