<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tables
        if (Schema::hasTable('classrooms') && ! Schema::hasTable('learning_groups')) {
            Schema::rename('classrooms', 'learning_groups');
        }

        if (Schema::hasTable('learners') && ! Schema::hasTable('participants')) {
            Schema::rename('learners', 'participants');
        }

        if (Schema::hasTable('classroom_learner') && ! Schema::hasTable('learning_group_participant')) {
            Schema::rename('classroom_learner', 'learning_group_participant');
        }

        // learning_groups columns
        if (Schema::hasTable('learning_groups')) {
            Schema::table('learning_groups', function (Blueprint $table) {
                if (Schema::hasColumn('learning_groups', 'teacher_user_id')) {
                    $table->renameColumn('teacher_user_id', 'instructor_user_id');
                }
                if (Schema::hasColumn('learning_groups', 'grade_level')) {
                    $table->renameColumn('grade_level', 'level');
                }
            });
        }

        // participants columns
        if (Schema::hasTable('participants')) {
            Schema::table('participants', function (Blueprint $table) {
                if (Schema::hasColumn('participants', 'learner_number')) {
                    $table->renameColumn('learner_number', 'participant_number');
                }
                if (Schema::hasColumn('participants', 'grade_level')) {
                    $table->renameColumn('grade_level', 'level');
                }
                if (Schema::hasColumn('participants', 'counselor_user_id')) {
                    $table->renameColumn('counselor_user_id', 'support_person_user_id');
                }
                if (Schema::hasColumn('participants', 'homeroom_classroom_id')) {
                    $table->renameColumn('homeroom_classroom_id', 'homeroom_learning_group_id');
                }
            });
        }

        // pivot columns
        if (Schema::hasTable('learning_group_participant')) {
            Schema::table('learning_group_participant', function (Blueprint $table) {
                if (Schema::hasColumn('learning_group_participant', 'classroom_id')) {
                    $table->renameColumn('classroom_id', 'learning_group_id');
                }
                if (Schema::hasColumn('learning_group_participant', 'learner_id')) {
                    $table->renameColumn('learner_id', 'participant_id');
                }
            });
        }

        // references to participants
        $renameMap = [
            'survey_attempts' => ['learner_id' => 'participant_id'],
            'resource_assignments' => ['learner_id' => 'participant_id'],
            'conversations' => ['learner_id' => 'participant_id'],
            'trigger_logs' => ['learner_id' => 'participant_id'],
            'mini_course_enrollments' => ['learner_id' => 'participant_id'],
            'provider_bookings' => ['learner_id' => 'participant_id'],
            'provider_conversations' => ['learner_id' => 'participant_id'],
            'provider_assignments' => ['learner_id' => 'participant_id'],
            'program_enrollments' => ['learner_id' => 'participant_id'],
        ];

        foreach ($renameMap as $tableName => $columns) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns) {
                foreach ($columns as $old => $new) {
                    if (Schema::hasColumn($tableName, $old)) {
                        $table->renameColumn($old, $new);
                    }
                }
            });
        }

        // grade/level target columns
        $gradeColumns = [
            'surveys' => [
                'target_grades' => 'target_levels',
                'target_classrooms' => 'target_learning_groups',
            ],
            'resources' => ['target_grades' => 'target_levels'],
            'mini_courses' => ['target_grades' => 'target_levels'],
            'course_templates' => ['target_grade_levels' => 'target_levels'],
            'content_blocks' => ['grade_levels' => 'levels'],
            'marketplace_items' => ['target_grades' => 'target_levels'],
        ];

        foreach ($gradeColumns as $tableName => $columns) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns) {
                foreach ($columns as $old => $new) {
                    if (Schema::hasColumn($tableName, $old)) {
                        $table->renameColumn($old, $new);
                    }
                }
            });
        }

        // data updates for roles/org types/list types (best-effort)
        if (Schema::hasTable('users')) {
            DB::table('users')->where('primary_role', 'teacher')->update(['primary_role' => 'instructor']);
            DB::table('users')->where('primary_role', 'learner')->update(['primary_role' => 'participant']);
            DB::table('users')->where('primary_role', 'student')->update(['primary_role' => 'participant']);
            DB::table('users')->where('primary_role', 'parent')->update(['primary_role' => 'direct_supervisor']);
            DB::table('users')->where('primary_role', 'counselor')->update(['primary_role' => 'support_person']);
            DB::table('users')->where('primary_role', 'superintendent')->update(['primary_role' => 'administrative_role']);
        }

        if (Schema::hasTable('organizations')) {
            DB::table('organizations')->where('org_type', 'district')->update(['org_type' => 'section']);
            DB::table('organizations')->where('org_type', 'school')->update(['org_type' => 'organization']);
        }

        if (Schema::hasTable('contact_lists')) {
            DB::table('contact_lists')->where('list_type', 'learner')->update(['list_type' => 'participant']);
            DB::table('contact_lists')->where('list_type', 'teacher')->update(['list_type' => 'instructor']);
        }

        if (Schema::hasTable('cohort_members')) {
            DB::table('cohort_members')->where('role', 'learner')->update(['role' => 'participant']);
        }
    }

    public function down(): void
    {
        // Reverse table renames
        if (Schema::hasTable('learning_group_participant') && ! Schema::hasTable('classroom_learner')) {
            Schema::rename('learning_group_participant', 'classroom_learner');
        }

        if (Schema::hasTable('participants') && ! Schema::hasTable('learners')) {
            Schema::rename('participants', 'learners');
        }

        if (Schema::hasTable('learning_groups') && ! Schema::hasTable('classrooms')) {
            Schema::rename('learning_groups', 'classrooms');
        }
    }
};
