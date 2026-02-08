<?php

namespace Database\Seeders;

use App\Models\MiniCourse;
use App\Models\MiniCourseEnrollment;
use App\Models\MiniCourseStep;
use App\Models\Organization;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class MiniCourseEnhancedSeeder extends Seeder
{
    /**
     * Create 12-15 courses with steps, YouTube videos, and enrollments.
     */
    public function run(): void
    {
        $school = Organization::where('org_type', 'school')->first();
        if (! $school) {
            $this->command->error('No school organization found!');
            return;
        }

        $admin = User::where('primary_role', 'admin')->where('org_id', $school->id)->first();
        $students = Student::where('org_id', $school->id)->get();

        if ($students->isEmpty()) {
            $this->command->error('No students found!');
            return;
        }

        $courses = $this->createCoursesWithSteps($school->id, $admin->id);
        $totalEnrollments = 0;
        $totalSteps = 0;

        foreach ($courses as $courseData) {
            $course = $courseData['course'];
            $totalSteps += count($courseData['steps']);

            $numEnrollments = rand(5, 8);
            $selectedStudents = $students->random(min($numEnrollments, $students->count()));

            foreach ($selectedStudents as $student) {
                $status = $this->weightedRandom(['completed' => 50, 'in_progress' => 30, 'enrolled' => 20]);
                $createdAt = now()->subDays(rand(10, 90));

                MiniCourseEnrollment::firstOrCreate(
                    [
                        'mini_course_id' => $course->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'status' => $status,
                        'progress_percent' => match($status) { 'completed' => 100, 'in_progress' => rand(20, 80), default => 0 },
                        'started_at' => $status !== 'enrolled' ? $createdAt->copy()->addDays(rand(0, 2)) : null,
                        'completed_at' => $status === 'completed' ? now()->subDays(rand(0, 30)) : null,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]
                );
                $totalEnrollments++;
            }
        }

        $this->command->info("Created " . count($courses) . " courses with {$totalSteps} steps (including YouTube videos) and {$totalEnrollments} enrollments");
    }

    private function createCoursesWithSteps(int $orgId, int $adminId): array
    {
        $coursesData = [
            [
                'course' => [
                    'title' => 'Stress Management Fundamentals',
                    'desc' => 'Learn effective stress coping strategies through proven techniques and mindfulness practices.',
                    'course_type' => 'wellness',
                    'duration' => 120,
                ],
                'steps' => [
                    ['title' => 'Understanding Stress', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=RcGyVTAoXEU', 'desc' => 'Learn what stress is and how it affects your body and mind', 'duration' => 15],
                    ['title' => 'Breathing Techniques', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=tybOi4hjZFQ', 'desc' => 'Practice deep breathing exercises to reduce stress instantly', 'duration' => 20],
                    ['title' => 'Daily Stress Management', 'type' => 'content', 'desc' => 'Create your personal stress management action plan', 'duration' => 30],
                    ['title' => 'Building Resilience', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=M1CHPnZfFmU', 'desc' => 'Develop long-term resilience to handle future stressors', 'duration' => 25],
                ],
            ],
            [
                'course' => [
                    'title' => 'Mindfulness & Meditation',
                    'desc' => 'Introduction to mindfulness practices for mental clarity and emotional balance.',
                    'course_type' => 'wellness',
                    'duration' => 100,
                ],
                'steps' => [
                    ['title' => 'What is Mindfulness?', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=6p_yaNFSYao', 'desc' => 'Introduction to mindfulness and its benefits', 'duration' => 12],
                    ['title' => 'Guided Meditation', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=inpok4MKVLM', 'desc' => 'Follow along with a guided meditation session', 'duration' => 20],
                    ['title' => 'Mindful Breathing', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=wfDTp2GogaQ', 'desc' => 'Learn breath-focused meditation techniques', 'duration' => 15],
                    ['title' => 'Daily Practice', 'type' => 'content', 'desc' => 'Set up your daily mindfulness routine', 'duration' => 20],
                ],
            ],
            [
                'course' => [
                    'title' => 'Building Resilience',
                    'desc' => 'Develop mental toughness and resilience to overcome life\'s challenges.',
                    'course_type' => 'wellness',
                    'duration' => 130,
                ],
                'steps' => [
                    ['title' => 'The Science of Resilience', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=TTXwnKm-HHg', 'desc' => 'Understand what makes people resilient', 'duration' => 18],
                    ['title' => 'Growth Mindset', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=hiiEeMN7vbQ', 'desc' => 'Cultivate a growth mindset for resilience', 'duration' => 22],
                    ['title' => 'Overcoming Setbacks', 'type' => 'content', 'desc' => 'Strategies for bouncing back from challenges', 'duration' => 25],
                    ['title' => 'Building Your Support Network', 'type' => 'content', 'desc' => 'Create a resilience support system', 'duration' => 20],
                ],
            ],
            [
                'course' => [
                    'title' => 'Emotional Intelligence',
                    'desc' => 'Understand and manage your emotions for better relationships and decision-making.',
                    'course_type' => 'wellness',
                    'duration' => 125,
                ],
                'steps' => [
                    ['title' => 'Understanding Emotions', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=Y7m9eNoB3NU', 'desc' => 'Learn about emotional intelligence and its importance', 'duration' => 16],
                    ['title' => 'Self-Awareness', 'type' => 'content', 'desc' => 'Recognize and understand your own emotions', 'duration' => 25],
                    ['title' => 'Empathy Skills', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=1Evwgu369Jw', 'desc' => 'Develop empathy and understanding of others', 'duration' => 20],
                    ['title' => 'Managing Emotions', 'type' => 'content', 'desc' => 'Practical techniques for emotional regulation', 'duration' => 30],
                ],
            ],
            [
                'course' => [
                    'title' => 'Study Skills Mastery',
                    'desc' => 'Comprehensive guide to effective studying and academic success.',
                    'course_type' => 'academic',
                    'duration' => 180,
                ],
                'steps' => [
                    ['title' => 'Effective Study Techniques', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=IlU-zDU6aQ0', 'desc' => 'Science-backed study methods that actually work', 'duration' => 18],
                    ['title' => 'Note-Taking Strategies', 'type' => 'content', 'desc' => 'Master different note-taking methods for better retention', 'duration' => 30],
                    ['title' => 'Memory Techniques', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=mzCEJVtED0U', 'desc' => 'Improve your memory and recall', 'duration' => 22],
                    ['title' => 'Test Preparation', 'type' => 'content', 'desc' => 'Prepare effectively for exams and reduce test anxiety', 'duration' => 35],
                ],
            ],
            [
                'course' => [
                    'title' => 'Social Skills Development',
                    'desc' => 'Building healthy relationships and effective communication skills.',
                    'course_type' => 'social',
                    'duration' => 150,
                ],
                'steps' => [
                    ['title' => 'Communication Basics', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=wfd2Fz_ZIYA', 'desc' => 'Master the fundamentals of effective communication', 'duration' => 20],
                    ['title' => 'Active Listening', 'type' => 'content', 'desc' => 'Learn to truly listen and understand others', 'duration' => 25],
                    ['title' => 'Building Friendships', 'type' => 'content', 'desc' => 'Develop and maintain healthy friendships', 'duration' => 30],
                    ['title' => 'Conflict Resolution', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=KY5TWVz5ZDU', 'desc' => 'Handle disagreements constructively', 'duration' => 20],
                ],
            ],
            [
                'course' => [
                    'title' => 'Time Management Mastery',
                    'desc' => 'Master your schedule and priorities for maximum productivity.',
                    'course_type' => 'life_skills',
                    'duration' => 110,
                ],
                'steps' => [
                    ['title' => 'Time Management Principles', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=iDbdXTMnOmE', 'desc' => 'Core principles of effective time management', 'duration' => 15],
                    ['title' => 'Priority Matrix', 'type' => 'content', 'desc' => 'Use the Eisenhower Matrix to prioritize tasks', 'duration' => 25],
                    ['title' => 'Overcoming Procrastination', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=arj7oStGLkU', 'desc' => 'Beat procrastination with proven strategies', 'duration' => 18],
                    ['title' => 'Building Productive Habits', 'type' => 'content', 'desc' => 'Create systems and habits for lasting change', 'duration' => 20],
                ],
            ],
            [
                'course' => [
                    'title' => 'Goal Setting & Achievement',
                    'desc' => 'Set and accomplish your goals with proven strategies.',
                    'course_type' => 'life_skills',
                    'duration' => 90,
                ],
                'steps' => [
                    ['title' => 'SMART Goals', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=1-SvuFIQjK8', 'desc' => 'Learn to set SMART goals that work', 'duration' => 12],
                    ['title' => 'Action Planning', 'type' => 'content', 'desc' => 'Break down goals into actionable steps', 'duration' => 20],
                    ['title' => 'Staying Motivated', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=Lp7E973zozc', 'desc' => 'Maintain motivation through challenges', 'duration' => 15],
                    ['title' => 'Tracking Progress', 'type' => 'content', 'desc' => 'Monitor and celebrate your achievements', 'duration' => 18],
                ],
            ],
            [
                'course' => [
                    'title' => 'Digital Citizenship & Safety',
                    'desc' => 'Navigate the digital world safely and responsibly.',
                    'course_type' => 'life_skills',
                    'duration' => 85,
                ],
                'steps' => [
                    ['title' => 'Online Safety Basics', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=WW2VDn8EHuA', 'desc' => 'Protect yourself online', 'duration' => 15],
                    ['title' => 'Digital Footprint', 'type' => 'content', 'desc' => 'Manage your online presence', 'duration' => 20],
                    ['title' => 'Cyberbullying Prevention', 'type' => 'content', 'desc' => 'Recognize and respond to cyberbullying', 'duration' => 18],
                    ['title' => 'Being a Good Digital Citizen', 'type' => 'content', 'desc' => 'Use technology responsibly and ethically', 'duration' => 15],
                ],
            ],
            [
                'course' => [
                    'title' => 'Conflict Resolution Skills',
                    'desc' => 'Handle conflicts effectively and constructively.',
                    'course_type' => 'social',
                    'duration' => 95,
                ],
                'steps' => [
                    ['title' => 'Understanding Conflict', 'type' => 'content', 'desc' => 'Recognize different types of conflicts', 'duration' => 15],
                    ['title' => 'Communication in Conflict', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=KY5TWVz5ZDU', 'desc' => 'Use "I" statements and active listening', 'duration' => 20],
                    ['title' => 'Finding Win-Win Solutions', 'type' => 'content', 'desc' => 'Negotiate and compromise effectively', 'duration' => 25],
                    ['title' => 'Practice & Reflection', 'type' => 'content', 'desc' => 'Apply conflict resolution in real situations', 'duration' => 20],
                ],
            ],
            [
                'course' => [
                    'title' => 'Growth Mindset Development',
                    'desc' => 'Cultivate a growth-oriented mindset for success.',
                    'course_type' => 'wellness',
                    'duration' => 105,
                ],
                'steps' => [
                    ['title' => 'Fixed vs Growth Mindset', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=hiiEeMN7vbQ', 'desc' => 'Understand the power of growth mindset', 'duration' => 12],
                    ['title' => 'Embracing Challenges', 'type' => 'content', 'desc' => 'See challenges as opportunities to grow', 'duration' => 22],
                    ['title' => 'Learning from Failure', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=_X0mgOOSpLU', 'desc' => 'Treat failure as feedback and keep improving', 'duration' => 18],
                    ['title' => 'Developing Your Growth Mindset', 'type' => 'content', 'desc' => 'Build habits that reinforce growth thinking', 'duration' => 20],
                ],
            ],
            [
                'course' => [
                    'title' => 'Financial Literacy for Teens',
                    'desc' => 'Money management basics for young adults.',
                    'course_type' => 'life_skills',
                    'duration' => 140,
                ],
                'steps' => [
                    ['title' => 'Money Basics', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=WEDIj9JBTC8', 'desc' => 'Understanding income, expenses, and budgeting', 'duration' => 18],
                    ['title' => 'Creating a Budget', 'type' => 'content', 'desc' => 'Build your first personal budget', 'duration' => 30],
                    ['title' => 'Saving & Investing', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=1BwhPJSPSJI', 'desc' => 'Start building wealth early', 'duration' => 25],
                    ['title' => 'Credit & Debt', 'type' => 'content', 'desc' => 'Understand credit cards and managing debt', 'duration' => 28],
                ],
            ],
            [
                'course' => [
                    'title' => 'Career Exploration',
                    'desc' => 'Explore potential career paths and plan your future.',
                    'course_type' => 'career',
                    'duration' => 200,
                ],
                'steps' => [
                    ['title' => 'Self-Assessment', 'type' => 'content', 'desc' => 'Discover your interests, values, and strengths', 'duration' => 35],
                    ['title' => 'Career Research', 'type' => 'content', 'desc' => 'Explore different career options', 'duration' => 40],
                    ['title' => 'Building Your Skills', 'type' => 'video', 'youtube' => 'https://www.youtube.com/watch?v=YMC1vJSIHjc', 'desc' => 'Develop skills employers want', 'duration' => 25],
                    ['title' => 'Planning Your Path', 'type' => 'content', 'desc' => 'Create your personalized career roadmap', 'duration' => 35],
                ],
            ],
        ];

        $results = [];
        foreach ($coursesData as $courseData) {
            $course = MiniCourse::firstOrCreate(
                [
                    'org_id' => $orgId,
                    'title' => $courseData['course']['title'],
                ],
                [
                    'description' => $courseData['course']['desc'],
                    'course_type' => $courseData['course']['course_type'],
                    'estimated_duration_minutes' => $courseData['course']['duration'],
                    'status' => MiniCourse::STATUS_ACTIVE,
                    'approval_status' => 'approved',
                    'published_at' => now(),
                    'created_by' => $adminId,
                    'approved_by' => $adminId,
                    'approved_at' => now(),
                ]
            );

            $steps = [];
            foreach ($courseData['steps'] as $index => $stepData) {
                $contentData = [];
                if (isset($stepData['youtube'])) {
                    $contentData = [
                        'video_url' => $stepData['youtube'],
                        'video_type' => 'youtube',
                    ];
                }

                $step = MiniCourseStep::firstOrCreate(
                    [
                        'mini_course_id' => $course->id,
                        'sort_order' => $index + 1,
                    ],
                    [
                        'step_type' => 'content',
                        'title' => $stepData['title'],
                        'description' => $stepData['desc'],
                        'content_type' => $stepData['type'] === 'video' ? 'video' : 'text',
                        'content_data' => $contentData,
                        'estimated_duration_minutes' => $stepData['duration'],
                        'is_required' => true,
                    ]
                );
                $steps[] = $step;
            }

            $results[] = ['course' => $course, 'steps' => $steps];
        }

        return $results;
    }

    private function weightedRandom(array $weights): string
    {
        $total = array_sum($weights);
        $random = rand(1, $total);
        $sum = 0;
        foreach ($weights as $key => $weight) {
            $sum += $weight;
            if ($random <= $sum) return $key;
        }
        return array_key_first($weights);
    }
}
