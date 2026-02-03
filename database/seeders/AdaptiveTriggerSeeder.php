<?php

namespace Database\Seeders;

use App\Models\AdaptiveTrigger;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdaptiveTriggerSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('org_type', 'organization')->first();
        if (! $organization) {
            $organization = Organization::first();
        }
        if (! $organization) {
            $this->command->error('No organization found. Please seed organizations first.');

            return;
        }

        $admin = User::where('org_id', $organization->id)->first();
        if (! $admin) {
            $admin = User::first();
        }
        if (! $admin) {
            $this->command->error('No user found. Please seed users first.');

            return;
        }

        $triggers = [
            [
                'name' => 'High Risk Wellness Alert',
                'description' => 'Automatically suggests wellness courses when a learner\'s risk level increases to high and recent survey scores indicate stress or anxiety.',
                'trigger_type' => AdaptiveTrigger::TYPE_COURSE_SUGGESTION,
                'input_sources' => [
                    AdaptiveTrigger::INPUT_QUANTITATIVE,
                    AdaptiveTrigger::INPUT_QUALITATIVE,
                ],
                'conditions' => [
                    'all' => [
                        ['field' => 'risk_level', 'operator' => 'equals', 'value' => 'high'],
                        ['field' => 'latest_survey.wellness_score', 'operator' => 'less_than', 'value' => 5],
                    ],
                ],
                'ai_interpretation_enabled' => true,
                'ai_prompt_context' => 'Analyze the learner\'s survey responses and behavioral patterns to determine if they would benefit from a stress management or wellness intervention. Consider any recent life changes or expressed concerns.',
                'output_action' => AdaptiveTrigger::ACTION_SUGGEST_FOR_REVIEW,
                'output_config' => [
                    'course_types' => ['wellness', 'intervention'],
                    'notification_recipients' => ['counselor', 'assigned_teacher'],
                    'priority' => 'high',
                ],
                'cooldown_hours' => 168, // 1 week
                'active' => true,
            ],
            [
                'name' => 'Academic Support Trigger',
                'description' => 'Suggests study skills courses when GPA drops by 0.5 or more points in a grading period.',
                'trigger_type' => AdaptiveTrigger::TYPE_COURSE_SUGGESTION,
                'input_sources' => [
                    AdaptiveTrigger::INPUT_QUANTITATIVE,
                ],
                'conditions' => [
                    'any' => [
                        ['field' => 'gpa_change', 'operator' => 'less_than', 'value' => -0.5],
                        ['field' => 'failing_classes_count', 'operator' => 'greater_than', 'value' => 0],
                    ],
                ],
                'ai_interpretation_enabled' => false,
                'output_action' => AdaptiveTrigger::ACTION_SUGGEST_FOR_REVIEW,
                'output_config' => [
                    'course_types' => ['academic', 'skill_building'],
                    'notification_recipients' => ['counselor'],
                    'priority' => 'medium',
                ],
                'cooldown_hours' => 336, // 2 weeks
                'active' => true,
            ],
            [
                'name' => 'Attendance Concern Intervention',
                'description' => 'Triggers intervention suggestions when attendance drops below 85% in a rolling 30-day window.',
                'trigger_type' => AdaptiveTrigger::TYPE_INTERVENTION_ALERT,
                'input_sources' => [
                    AdaptiveTrigger::INPUT_BEHAVIORAL,
                    AdaptiveTrigger::INPUT_QUANTITATIVE,
                ],
                'conditions' => [
                    'all' => [
                        ['field' => 'attendance_rate_30d', 'operator' => 'less_than', 'value' => 85],
                        ['field' => 'unexcused_absences_30d', 'operator' => 'greater_than', 'value' => 3],
                    ],
                ],
                'ai_interpretation_enabled' => true,
                'ai_prompt_context' => 'Review the learner\'s attendance patterns, any documented reasons for absences, and correlate with other data points (grades, behavior) to recommend appropriate interventions.',
                'output_action' => AdaptiveTrigger::ACTION_NOTIFY,
                'output_config' => [
                    'notification_recipients' => ['counselor', 'admin'],
                    'notification_template' => 'attendance_concern',
                    'include_ai_analysis' => true,
                ],
                'cooldown_hours' => 72, // 3 days
                'active' => true,
            ],
            [
                'name' => 'Behavioral Pattern Alert',
                'description' => 'Notifies staff when behavioral incident patterns suggest need for additional support.',
                'trigger_type' => AdaptiveTrigger::TYPE_INTERVENTION_ALERT,
                'input_sources' => [
                    AdaptiveTrigger::INPUT_BEHAVIORAL,
                    AdaptiveTrigger::INPUT_QUALITATIVE,
                ],
                'conditions' => [
                    'any' => [
                        ['field' => 'behavior_incidents_30d', 'operator' => 'greater_than', 'value' => 2],
                        ['field' => 'discipline_referrals_30d', 'operator' => 'greater_than', 'value' => 1],
                    ],
                ],
                'ai_interpretation_enabled' => true,
                'ai_prompt_context' => 'Analyze the pattern of behavioral incidents, looking for common triggers, times of day, or circumstances. Consider any documented context from notes or previous interventions.',
                'output_action' => AdaptiveTrigger::ACTION_SUGGEST_FOR_REVIEW,
                'output_config' => [
                    'course_types' => ['behavioral', 'intervention'],
                    'suggest_provider_types' => ['counselor', 'therapist'],
                    'notification_recipients' => ['counselor'],
                ],
                'cooldown_hours' => 168, // 1 week
                'active' => true,
            ],
            [
                'name' => 'Positive Progress Recognition',
                'description' => 'Suggests enrichment opportunities when learners show sustained improvement.',
                'trigger_type' => AdaptiveTrigger::TYPE_COURSE_SUGGESTION,
                'input_sources' => [
                    AdaptiveTrigger::INPUT_QUANTITATIVE,
                    AdaptiveTrigger::INPUT_BEHAVIORAL,
                ],
                'conditions' => [
                    'all' => [
                        ['field' => 'gpa_change', 'operator' => 'greater_than', 'value' => 0.3],
                        ['field' => 'attendance_rate_30d', 'operator' => 'greater_than', 'value' => 95],
                        ['field' => 'behavior_incidents_30d', 'operator' => 'equals', 'value' => 0],
                    ],
                ],
                'ai_interpretation_enabled' => false,
                'output_action' => AdaptiveTrigger::ACTION_SUGGEST_FOR_REVIEW,
                'output_config' => [
                    'course_types' => ['enrichment', 'skill_building'],
                    'notification_recipients' => ['counselor'],
                    'priority' => 'low',
                    'message_template' => 'Consider offering enrichment opportunities to recognize this learner\'s positive progress.',
                ],
                'cooldown_hours' => 720, // 30 days
                'active' => true,
            ],
            [
                'name' => 'New Learner Onboarding',
                'description' => 'Automatically suggests orientation and goal-setting courses for newly enrolled learners.',
                'trigger_type' => AdaptiveTrigger::TYPE_COURSE_SUGGESTION,
                'input_sources' => [
                    AdaptiveTrigger::INPUT_EXPLICIT,
                ],
                'conditions' => [
                    'all' => [
                        ['field' => 'days_since_enrollment', 'operator' => 'less_than', 'value' => 30],
                        ['field' => 'enrollment_status', 'operator' => 'equals', 'value' => 'active'],
                    ],
                ],
                'ai_interpretation_enabled' => false,
                'output_action' => AdaptiveTrigger::ACTION_AUTO_ENROLL,
                'output_config' => [
                    'course_tags' => ['onboarding', 'goal-setting'],
                    'auto_enroll_template_courses' => true,
                    'send_welcome_notification' => true,
                ],
                'cooldown_hours' => 8760, // 1 year (essentially once per enrollment)
                'active' => false, // Disabled by default - organization must opt-in
            ],
            [
                'name' => 'Survey Response Follow-up',
                'description' => 'Triggers course suggestions based on specific survey responses indicating need.',
                'trigger_type' => AdaptiveTrigger::TYPE_COURSE_SUGGESTION,
                'input_sources' => [
                    AdaptiveTrigger::INPUT_EXPLICIT,
                    AdaptiveTrigger::INPUT_QUALITATIVE,
                ],
                'conditions' => [
                    'any' => [
                        ['field' => 'survey.stress_level', 'operator' => 'greater_than', 'value' => 7],
                        ['field' => 'survey.help_needed', 'operator' => 'equals', 'value' => true],
                        ['field' => 'survey.concern_areas', 'operator' => 'contains_any', 'value' => ['anxiety', 'depression', 'self-harm']],
                    ],
                ],
                'ai_interpretation_enabled' => true,
                'ai_prompt_context' => 'Review the learner\'s full survey responses to understand context. Determine urgency level and recommend appropriate courses or interventions. Flag any responses that require immediate counselor attention.',
                'output_action' => AdaptiveTrigger::ACTION_SUGGEST_FOR_REVIEW,
                'output_config' => [
                    'course_types' => ['wellness', 'intervention'],
                    'suggest_provider_types' => ['therapist', 'counselor'],
                    'priority' => 'high',
                    'require_counselor_review' => true,
                ],
                'cooldown_hours' => 24,
                'active' => true,
            ],
            [
                'name' => 'Provider Recommendation Engine',
                'description' => 'Recommends appropriate providers when learner needs match provider specialties.',
                'trigger_type' => AdaptiveTrigger::TYPE_PROVIDER_RECOMMENDATION,
                'input_sources' => [
                    AdaptiveTrigger::INPUT_QUANTITATIVE,
                    AdaptiveTrigger::INPUT_QUALITATIVE,
                    AdaptiveTrigger::INPUT_EXPLICIT,
                ],
                'conditions' => [
                    'any' => [
                        ['field' => 'counselor_referral_requested', 'operator' => 'equals', 'value' => true],
                        ['field' => 'iep_status', 'operator' => 'equals', 'value' => true],
                        ['field' => 'identified_needs', 'operator' => 'is_not_empty'],
                    ],
                ],
                'ai_interpretation_enabled' => true,
                'ai_prompt_context' => 'Match learner needs with available providers based on specialty areas, availability, insurance acceptance, and location preferences. Prioritize providers with high ratings and relevant experience.',
                'output_action' => AdaptiveTrigger::ACTION_SUGGEST_FOR_REVIEW,
                'output_config' => [
                    'max_provider_suggestions' => 3,
                    'include_availability' => true,
                    'include_cost_info' => true,
                ],
                'cooldown_hours' => 168, // 1 week
                'active' => true,
            ],
        ];

        foreach ($triggers as $triggerData) {
            AdaptiveTrigger::create([
                'org_id' => $organization->id,
                'name' => $triggerData['name'],
                'description' => $triggerData['description'],
                'trigger_type' => $triggerData['trigger_type'],
                'input_sources' => $triggerData['input_sources'],
                'conditions' => $triggerData['conditions'],
                'ai_interpretation_enabled' => $triggerData['ai_interpretation_enabled'],
                'ai_prompt_context' => $triggerData['ai_prompt_context'] ?? null,
                'output_action' => $triggerData['output_action'],
                'output_config' => $triggerData['output_config'],
                'cooldown_hours' => $triggerData['cooldown_hours'],
                'active' => $triggerData['active'],
                'created_by' => $admin->id,
            ]);
        }
    }
}
