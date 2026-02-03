<?php

declare(strict_types=1);

namespace App\Services\Domain;

use App\Models\MiniCourse;

/**
 * Domain service for course generation business rules.
 * Handles all course focus determination logic and level conversion rules.
 */
class CourseGenerationRuleDomainService
{
    /**
     * Determine course focus for a participant based on context.
     */
    public function determineLearnerCourseFocus(array $context): array
    {
        $improvementAreas = $context['improvement_areas'] ?? [];
        $riskLevel = $context['behavioral_profile']['risk_level'] ?? 'unknown';
        $basicInfo = $context['basic_info'] ?? [];

        // Default focus
        $topic = 'Building Academic Success Skills';
        $courseType = MiniCourse::TYPE_SKILL_BUILDING;
        $objectives = ['Develop effective study habits', 'Build self-confidence'];
        $duration = 30;

        // Customize based on needs
        if (in_array($riskLevel, ['high', 'critical'])) {
            $topic = 'Getting Back on Track: A Personal Success Plan';
            $courseType = MiniCourse::TYPE_INTERVENTION;
            $objectives = [
                'Identify personal barriers to success',
                'Create actionable improvement goals',
                'Build resilience and coping strategies',
            ];
            $duration = 45;
        } elseif ($basicInfo['has_iep'] ?? false) {
            $topic = 'Learning Strategies for Your Success';
            $courseType = MiniCourse::TYPE_SKILL_BUILDING;
            $objectives = [
                'Understand your learning style',
                'Apply personalized study techniques',
                'Advocate for your learning needs',
            ];
        } elseif ($basicInfo['is_ell'] ?? false) {
            $topic = 'Building Language and Academic Confidence';
            $courseType = MiniCourse::TYPE_ACADEMIC;
            $objectives = [
                'Strengthen academic vocabulary',
                'Improve reading comprehension strategies',
                'Build confidence in learning_group participation',
            ];
        }

        // Check for specific improvement areas
        foreach ($improvementAreas as $area) {
            if (str_contains(strtolower($area), 'behavioral')) {
                $topic = 'Managing Emotions and Building Positive Habits';
                $courseType = MiniCourse::TYPE_BEHAVIORAL;
                $objectives = [
                    'Recognize emotional triggers',
                    'Practice self-regulation techniques',
                    'Build positive relationships',
                ];
                break;
            }
            if (str_contains(strtolower($area), 'attendance')) {
                $topic = 'Making Organization Matter: Building Your Path to Success';
                $courseType = MiniCourse::TYPE_INTERVENTION;
                $objectives = [
                    'Understand the value of consistent attendance',
                    'Identify and address barriers to attendance',
                    'Create a personal attendance improvement plan',
                ];
                break;
            }
        }

        return [
            'topic' => $topic,
            'course_type' => $courseType,
            'objectives' => $objectives,
            'duration' => $duration,
        ];
    }

    /**
     * Determine course focus for a instructor based on context.
     */
    public function determineTeacherCourseFocus(array $context): array
    {
        $improvementAreas = $context['improvement_areas'] ?? [];
        $learnerOutcomes = $context['learner_outcomes'] ?? [];

        // Default focus
        $terminology = app(\App\Services\TerminologyService::class);
        $topic = $terminology->get('auto_course_topic_engagement_label');
        $courseType = MiniCourse::TYPE_SKILL_BUILDING;
        $objectives = [
            'Implement evidence-based engagement strategies',
            'Differentiate instruction for diverse participants',
            'Use data to drive instructional decisions',
        ];
        $duration = 45;

        // Customize based on participant outcomes
        foreach ($learnerOutcomes as $type => $data) {
            if (($data['trend'] ?? '') === 'declining') {
                if (str_contains($type, 'behavioral') || str_contains($type, 'social')) {
                    $topic = 'Building a Positive LearningGroup Environment';
                    $courseType = MiniCourse::TYPE_BEHAVIORAL;
                    $objectives = [
                        'Implement proactive learning_group management strategies',
                        'Build positive participant-instructor relationships',
                        'Address challenging behaviors constructively',
                    ];
                    break;
                }
                if (str_contains($type, 'academic') || str_contains($type, 'level')) {
                    $topic = $terminology->get('auto_course_topic_performance_label');
                    $courseType = MiniCourse::TYPE_ACADEMIC;
                    $objectives = [
                        'Identify root causes of academic struggles',
                        'Implement targeted intervention strategies',
                        'Monitor and adjust instruction based on data',
                    ];
                    break;
                }
            }
        }

        return [
            'topic' => $topic,
            'course_type' => $courseType,
            'objectives' => $objectives,
            'duration' => $duration,
        ];
    }

    /**
     * Determine course focus for a department.
     */
    public function determineDepartmentCourseFocus(array $context, array $criteria): array
    {
        $challenges = $context['common_challenges'] ?? [];
        $priorities = $context['improvement_priorities'] ?? [];

        $department = $criteria['department'] ?? 'General';

        // Default focus
        $topic = "$department Department: Building Excellence Together";
        $courseType = MiniCourse::TYPE_SKILL_BUILDING;
        $objectives = [
            'Align on departmental goals and priorities',
            'Share best practices across the team',
            'Develop collaborative improvement strategies',
        ];
        $duration = 60;

        // Customize based on challenges
        if (!empty($challenges)) {
            $primaryChallenge = $challenges[0] ?? '';

            if (str_contains(strtolower($primaryChallenge), 'behavioral')) {
                $topic = "$department Department: Collaborative Behavior Management";
                $courseType = MiniCourse::TYPE_BEHAVIORAL;
                $objectives = [
                    'Establish consistent behavior expectations',
                    'Implement department-wide PBIS strategies',
                    'Support each other with challenging participants',
                ];
            } elseif (str_contains(strtolower($primaryChallenge), 'academic')) {
                $topic = "$department Department: Raising Academic Standards Together";
                $courseType = MiniCourse::TYPE_ACADEMIC;
                $objectives = [
                    'Analyze participant performance data as a team',
                    'Develop targeted intervention strategies',
                    'Monitor progress and adjust approaches',
                ];
            }
        }

        return [
            'topic' => $topic,
            'course_type' => $courseType,
            'objectives' => $objectives,
            'duration' => $duration,
        ];
    }

    /**
     * Convert level level number to level range string.
     * Business rule for level-level grouping.
     */
    public function gradeToRange(?int $level): ?string
    {
        if ($level === null) {
            return null;
        }

        if ($level <= 2) {
            return 'K-2';
        }
        if ($level <= 5) {
            return '3-5';
        }
        if ($level <= 8) {
            return '6-8';
        }

        return '9-12';
    }
}
