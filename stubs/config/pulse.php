<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific features of the Pulse platform.
    |
    */
    'features' => [
        'voice_calls' => env('FEATURE_VOICE_CALLS', true),
        'sms_surveys' => env('FEATURE_SMS_SURVEYS', true),
        'whatsapp' => env('FEATURE_WHATSAPP', true),
        'auto_resource_matching' => env('FEATURE_AUTO_RESOURCE_MATCHING', true),
        'llm_reports' => env('FEATURE_LLM_REPORTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Organization Hierarchy Levels
    |--------------------------------------------------------------------------
    */
    'org_types' => [
        'consultant',
        'section',
        'organization',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Roles
    |--------------------------------------------------------------------------
    */
    'roles' => [
        'consultant' => [
            'label' => 'Consultant',
            'level' => 100,
            'permissions' => ['*'], // All permissions
        ],
        'admin' => [
            'label' => 'Administrator',
            'level' => 80,
            'permissions' => [
                'manage_users',
                'manage_surveys',
                'manage_resources',
                'manage_reports',
                'manage_triggers',
                'view_all_learners',
            ],
        ],
        'instructor' => [
            'label' => 'Instructor',
            'level' => 50,
            'permissions' => [
                'view_assigned_learners',
                'complete_surveys',
                'view_reports',
                'suggest_resources',
            ],
        ],
        'direct_supervisor' => [
            'label' => 'Direct Supervisor',
            'level' => 30,
            'permissions' => [
                'view_own_learners',
                'complete_surveys',
                'view_learner_reports',
            ],
        ],
        'participant' => [
            'label' => 'Participant',
            'level' => 20,
            'permissions' => [
                'view_own_data',
                'complete_self_surveys',
                'access_resources',
            ],
        ],
        'volunteer' => [
            'label' => 'Volunteer',
            'level' => 40,
            'permissions' => [
                'view_assigned_learners',
                'complete_surveys',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Survey Configuration
    |--------------------------------------------------------------------------
    */
    'survey' => [
        'max_call_duration_seconds' => 600, // 10 minutes
        'max_learners_per_call' => 15,
        'default_frequency' => 'weekly',
    ],

    /*
    |--------------------------------------------------------------------------
    | LLM Prompts
    |--------------------------------------------------------------------------
    */
    'prompts' => [
        'conversational_survey' => <<<'PROMPT'
You are Pulse, a friendly educational data collection assistant. Your role is to have natural conversations with instructors to gather information about their participants.

Guidelines:
- Be warm, professional, and efficient
- Ask about academics, behavior, social-emotional wellbeing, and any concerns
- Extract specific, factual information while filtering out emotional language
- Keep the conversation focused but allow instructors to share important context
- Summarize findings at the end of each participant discussion

For each participant, gather:
1. Academic performance (by subject if relevant)
2. Behavioral observations
3. Social-emotional status
4. Attendance notes
5. Any concerns or needs
6. Positive developments

Always maintain participant privacy and confidentiality.
PROMPT,

        'data_extraction' => <<<'PROMPT'
Extract structured data from the following instructor conversation about a participant. Return a JSON object with these fields:

{
  "academics": {
    "overall_rating": 1-5,
    "subjects": {
      "[subject]": { "rating": 1-5, "notes": "string" }
    },
    "homework_completion": 1-5,
    "class_participation": 1-5
  },
  "behavior": {
    "overall_rating": 1-5,
    "notes": "string",
    "incidents": ["string"]
  },
  "social_emotional": {
    "overall_rating": 1-5,
    "peer_relationships": 1-5,
    "emotional_regulation": 1-5,
    "concerns": ["string"]
  },
  "attendance": {
    "present_days": number,
    "absent_days": number,
    "tardy_count": number,
    "notes": "string"
  },
  "needs": ["string"],
  "positives": ["string"],
  "recommended_interventions": ["string"],
  "risk_level": "low" | "medium" | "high",
  "summary": "string"
}

Be objective. Remove emotional language. Focus on facts and observations.
PROMPT,

        'report_narrative' => <<<'PROMPT'
You are an educational data analyst. Write a clear, professional narrative report based on the provided data. The report should:

1. Highlight key trends (positive and negative)
2. Identify participants or groups needing attention
3. Compare current data to previous periods if available
4. Provide actionable recommendations
5. Use clear, jargon-free language appropriate for educators

Structure:
- Executive Summary (2-3 sentences)
- Key Findings (bullet points)
- Areas of Concern
- Positive Developments
- Recommendations

Keep the tone professional and constructive. Focus on data-driven insights.
PROMPT,
    ],

    /*
    |--------------------------------------------------------------------------
    | Risk Level Thresholds
    |--------------------------------------------------------------------------
    */
    'risk_thresholds' => [
        'high' => [
            'min_flags' => 3,
            'triggers_immediate_action' => true,
        ],
        'medium' => [
            'min_flags' => 1,
            'triggers_immediate_action' => false,
        ],
        'low' => [
            'min_flags' => 0,
            'triggers_immediate_action' => false,
        ],
    ],

];
