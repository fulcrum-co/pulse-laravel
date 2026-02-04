<?php

namespace Database\Seeders;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HelpContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default categories
        $categories = [
            [
                'name' => 'Getting Started',
                'slug' => 'getting-started',
                'description' => 'Learn the basics of using Pulse',
                'icon' => 'play-circle',
                'sort_order' => 0,
            ],
            [
                'name' => 'Contacts & Students',
                'slug' => 'contacts',
                'description' => 'Managing contacts and understanding risk levels',
                'icon' => 'users',
                'sort_order' => 1,
            ],
            [
                'name' => 'Data Collection',
                'slug' => 'data-collection',
                'description' => 'Setting up surveys and assessments',
                'icon' => 'clipboard-document-list',
                'sort_order' => 2,
            ],
            [
                'name' => 'Reports',
                'slug' => 'reports',
                'description' => 'Creating and sharing reports',
                'icon' => 'chart-bar',
                'sort_order' => 3,
            ],
            [
                'name' => 'Distribution',
                'slug' => 'distribution',
                'description' => 'Sending communications to recipients',
                'icon' => 'paper-airplane',
                'sort_order' => 4,
            ],
            [
                'name' => 'Resources & Courses',
                'slug' => 'resources',
                'description' => 'Managing educational resources and courses',
                'icon' => 'academic-cap',
                'sort_order' => 5,
            ],
            [
                'name' => 'Administration',
                'slug' => 'administration',
                'description' => 'Admin settings and organization management',
                'icon' => 'cog-6-tooth',
                'sort_order' => 6,
            ],
        ];

        foreach ($categories as $categoryData) {
            HelpCategory::updateOrCreate(
                ['org_id' => null, 'slug' => $categoryData['slug']],
                array_merge($categoryData, ['org_id' => null, 'is_active' => true])
            );
        }

        // Fetch categories for article assignment
        $gettingStarted = HelpCategory::where('slug', 'getting-started')->first();
        $contacts = HelpCategory::where('slug', 'contacts')->first();
        $dataCollection = HelpCategory::where('slug', 'data-collection')->first();
        $reports = HelpCategory::where('slug', 'reports')->first();
        $distribution = HelpCategory::where('slug', 'distribution')->first();
        $resources = HelpCategory::where('slug', 'resources')->first();
        $administration = HelpCategory::where('slug', 'administration')->first();

        $articles = [
            // ===== GETTING STARTED =====
            [
                'category_id' => $gettingStarted?->id,
                'title' => 'Welcome to Pulse',
                'slug' => 'welcome-to-pulse',
                'content' => $this->getWelcomeContent(),
                'excerpt' => 'Get oriented with Pulse and learn what you can accomplish with the platform.',
                'is_published' => true,
                'is_featured' => true,
                'search_keywords' => ['welcome', 'intro', 'start', 'overview', 'first time'],
            ],
            [
                'category_id' => $gettingStarted?->id,
                'title' => 'Your First Week with Pulse',
                'slug' => 'first-week-checklist',
                'content' => $this->getFirstWeekContent(),
                'excerpt' => 'A step-by-step checklist to get up and running in your first week.',
                'is_published' => true,
                'is_featured' => true,
                'search_keywords' => ['checklist', 'setup', 'onboarding', 'first week'],
            ],
            [
                'category_id' => $gettingStarted?->id,
                'title' => 'Understanding Your Dashboard',
                'slug' => 'understanding-dashboard',
                'content' => $this->getDashboardContent(),
                'excerpt' => 'Learn to read and customize your dashboard for daily monitoring.',
                'is_published' => true,
                'is_featured' => false,
                'search_keywords' => ['dashboard', 'widgets', 'metrics', 'overview'],
            ],

            // ===== CONTACTS & STUDENTS =====
            [
                'category_id' => $contacts?->id,
                'title' => 'Understanding Risk Levels',
                'slug' => 'understanding-risk-levels',
                'content' => $this->getRiskLevelsContent(),
                'excerpt' => 'Learn what each risk level means and how to respond appropriately.',
                'is_published' => true,
                'is_featured' => true,
                'search_keywords' => ['risk', 'high risk', 'low risk', 'good standing', 'at risk', 'intervention'],
            ],
            [
                'category_id' => $contacts?->id,
                'title' => 'Managing Contact Lists',
                'slug' => 'managing-contact-lists',
                'content' => $this->getContactListsContent(),
                'excerpt' => 'Create and manage lists to organize contacts for surveys and communications.',
                'is_published' => true,
                'is_featured' => false,
                'search_keywords' => ['lists', 'contact lists', 'groups', 'segments', 'filter'],
            ],
            [
                'category_id' => $contacts?->id,
                'title' => 'Viewing Contact Profiles',
                'slug' => 'viewing-contact-profiles',
                'content' => $this->getContactProfilesContent(),
                'excerpt' => 'Access detailed information about individual contacts including history and trends.',
                'is_published' => true,
                'is_featured' => false,
                'search_keywords' => ['profile', 'contact details', 'student profile', 'history'],
            ],

            // ===== DATA COLLECTION =====
            [
                'category_id' => $dataCollection?->id,
                'title' => 'Creating Your First Survey',
                'slug' => 'creating-first-survey',
                'content' => $this->getCreateSurveyContent(),
                'excerpt' => 'Step-by-step guide to creating and distributing your first wellness survey.',
                'is_published' => true,
                'is_featured' => true,
                'search_keywords' => ['survey', 'create survey', 'questionnaire', 'assessment', 'form'],
            ],
            [
                'category_id' => $dataCollection?->id,
                'title' => 'Survey Question Types',
                'slug' => 'survey-question-types',
                'content' => $this->getQuestionTypesContent(),
                'excerpt' => 'Learn about different question types and when to use each one.',
                'is_published' => true,
                'is_featured' => false,
                'search_keywords' => ['questions', 'multiple choice', 'scale', 'open ended', 'question types'],
            ],
            [
                'category_id' => $dataCollection?->id,
                'title' => 'Reviewing Survey Responses',
                'slug' => 'reviewing-survey-responses',
                'content' => $this->getReviewResponsesContent(),
                'excerpt' => 'How to analyze survey responses and identify students who need support.',
                'is_published' => true,
                'is_featured' => false,
                'search_keywords' => ['responses', 'results', 'analyze', 'review', 'submissions'],
            ],

            // ===== REPORTS =====
            [
                'category_id' => $reports?->id,
                'title' => 'Building Custom Reports',
                'slug' => 'building-custom-reports',
                'content' => $this->getBuildReportsContent(),
                'excerpt' => 'Create professional reports with charts, metrics, and data visualizations.',
                'is_published' => true,
                'is_featured' => true,
                'search_keywords' => ['reports', 'report builder', 'create report', 'custom report'],
            ],
            [
                'category_id' => $reports?->id,
                'title' => 'Exporting and Sharing Reports',
                'slug' => 'exporting-sharing-reports',
                'content' => $this->getExportReportsContent(),
                'excerpt' => 'Export reports to PDF or share them with stakeholders.',
                'is_published' => true,
                'is_featured' => false,
                'search_keywords' => ['export', 'pdf', 'share', 'download', 'print'],
            ],
            [
                'category_id' => $reports?->id,
                'title' => 'Comparing Groups in Reports',
                'slug' => 'comparing-groups-reports',
                'content' => $this->getCompareGroupsContent(),
                'excerpt' => 'Compare data across different contact lists, grades, or time periods.',
                'is_published' => true,
                'is_featured' => false,
                'search_keywords' => ['compare', 'comparison', 'groups', 'trends', 'benchmark'],
            ],

            // ===== DISTRIBUTION =====
            [
                'category_id' => $distribution?->id,
                'title' => 'Sending Your First Communication',
                'slug' => 'sending-first-communication',
                'content' => $this->getSendCommunicationContent(),
                'excerpt' => 'Learn how to send emails and messages to contacts.',
                'is_published' => true,
                'is_featured' => false,
                'search_keywords' => ['send', 'email', 'message', 'communication', 'distribute'],
            ],
            [
                'category_id' => $distribution?->id,
                'title' => 'Scheduling Automated Messages',
                'slug' => 'scheduling-automated-messages',
                'content' => $this->getScheduleMessagesContent(),
                'excerpt' => 'Set up recurring communications and scheduled deliveries.',
                'is_published' => true,
                'is_featured' => false,
                'search_keywords' => ['schedule', 'automate', 'recurring', 'automatic'],
            ],

            // ===== RESOURCES =====
            [
                'category_id' => $resources?->id,
                'title' => 'Browsing the Resource Hub',
                'slug' => 'browsing-resource-hub',
                'content' => $this->getResourceHubContent(),
                'excerpt' => 'Find and share educational resources with your community.',
                'is_published' => true,
                'is_featured' => false,
                'search_keywords' => ['resources', 'library', 'materials', 'content'],
            ],
            [
                'category_id' => $resources?->id,
                'title' => 'Assigning Courses to Contacts',
                'slug' => 'assigning-courses',
                'content' => $this->getAssignCoursesContent(),
                'excerpt' => 'Assign educational courses and track completion progress.',
                'is_published' => true,
                'is_featured' => false,
                'search_keywords' => ['courses', 'assign', 'training', 'learning', 'completion'],
            ],

            // ===== ADMINISTRATION =====
            [
                'category_id' => $administration?->id,
                'title' => 'Managing Organization Settings',
                'slug' => 'managing-organization-settings',
                'content' => $this->getOrgSettingsContent(),
                'excerpt' => 'Configure your organization\'s branding, terminology, and preferences.',
                'is_published' => true,
                'is_featured' => false,
                'search_keywords' => ['settings', 'organization', 'configure', 'admin', 'preferences'],
            ],
            [
                'category_id' => $administration?->id,
                'title' => 'User Roles and Permissions',
                'slug' => 'user-roles-permissions',
                'content' => $this->getRolesPermissionsContent(),
                'excerpt' => 'Understand different user roles and what each can access.',
                'is_published' => true,
                'is_featured' => false,
                'search_keywords' => ['roles', 'permissions', 'access', 'admin', 'user management'],
            ],
        ];

        foreach ($articles as $articleData) {
            HelpArticle::updateOrCreate(
                ['org_id' => null, 'slug' => $articleData['slug']],
                array_merge($articleData, [
                    'org_id' => null,
                    'published_at' => now(),
                    'search_keywords' => $articleData['search_keywords'] ?? null,
                ])
            );
        }
    }

    // ===== CONTENT METHODS =====

    private function getWelcomeContent(): string
    {
        return <<<'MARKDOWN'
# Welcome to Pulse

Pulse helps you monitor and support the wellbeing of your community through data-driven insights and proactive interventions.

## What Pulse Does

Pulse brings together several key capabilities:

- **Wellness Monitoring** — Collect regular check-ins and assessments to understand how your community is doing
- **Risk Identification** — Automatically flag individuals who may need additional support based on their responses
- **Trend Analysis** — Track changes over time to see if interventions are working
- **Communication** — Reach out to individuals or groups with targeted resources and support
- **Reporting** — Generate professional reports for stakeholders and leadership

## Key Concepts

### Risk Levels

Every contact in Pulse is assigned a risk level based on their survey responses:

- **Good Standing** — No concerns identified
- **Low Risk** — Some indicators present, worth monitoring
- **High Risk** — Immediate attention recommended

### Contact Lists

Organize your contacts into lists for easier management. Lists help you:
- Target specific groups with surveys
- Send communications to the right people
- Compare data across different populations

### The Dashboard

Your dashboard provides an at-a-glance view of your community's wellbeing. You'll see:
- Overall risk distribution
- Recent survey responses
- Contacts requiring attention
- Trend indicators

## Getting Help

If you ever get stuck, look for the **?** icon in the bottom-right corner of any page. You can also:
- Take a guided tour of any page
- Search the help center
- Contact support directly

Ready to get started? Check out [Your First Week with Pulse](/help/article/first-week-checklist) for a step-by-step onboarding guide.
MARKDOWN;
    }

    private function getFirstWeekContent(): string
    {
        return <<<'MARKDOWN'
# Your First Week with Pulse

This checklist will help you get fully set up and comfortable with Pulse in your first week.

## Day 1: Get Oriented

- [ ] **Log in and explore the dashboard** — Take a few minutes to click around and get familiar with the layout
- [ ] **Take the guided tour** — Click "Start Tour" on the dashboard to get a walkthrough of key features
- [ ] **Review your contact list** — Go to Contacts and see who's already in the system
- [ ] **Check your notification settings** — Go to Settings > Notifications to configure how you want to be alerted

## Day 2: Understand Your Data

- [ ] **Learn about risk levels** — Read [Understanding Risk Levels](/help/article/understanding-risk-levels)
- [ ] **Review any existing survey responses** — Check the Collect section for recent submissions
- [ ] **Identify high-risk contacts** — Use the filter on the Contacts page to see anyone flagged as high risk

## Day 3: Create Your First Survey

- [ ] **Browse survey templates** — Go to Collect > Templates to see pre-built options
- [ ] **Create or customize a survey** — Follow [Creating Your First Survey](/help/article/creating-first-survey)
- [ ] **Preview your survey** — Always preview before sending to catch any issues
- [ ] **Send to a small test group** — Start with a small list to make sure everything works

## Day 4: Analyze Results

- [ ] **Review survey responses** — Check Collect > Responses to see submissions
- [ ] **Identify anyone needing support** — Look for concerning responses that may need follow-up
- [ ] **Add notes to contact profiles** — Document any outreach or observations

## Day 5: Report and Communicate

- [ ] **Build your first report** — Go to Reports and create a summary of your data
- [ ] **Share with your team** — Export the report or share a link
- [ ] **Send a follow-up communication** — Use Distribute to send resources to your contacts

## Tips for Success

1. **Start small** — Don't try to survey everyone at once. Begin with a pilot group.
2. **Check in daily** — Make reviewing the dashboard part of your morning routine.
3. **Document everything** — Use contact notes to track your interactions and observations.
4. **Ask for help** — The support team is here to help you succeed.

## What's Next?

Once you're comfortable with the basics, explore:
- Creating custom contact lists for different groups
- Setting up automated survey schedules
- Building more detailed reports for stakeholders
MARKDOWN;
    }

    private function getDashboardContent(): string
    {
        return <<<'MARKDOWN'
# Understanding Your Dashboard

Your dashboard is your command center for monitoring community wellbeing at a glance.

## Dashboard Overview

When you log in, you'll see several key areas:

### Risk Distribution

The risk distribution widget shows how your contacts are currently distributed across risk levels:

- **Green (Good Standing)** — The majority of your community should ideally be here
- **Yellow (Low Risk)** — Keep an eye on these individuals
- **Red (High Risk)** — These contacts need your attention

### Recent Activity

This section shows:
- Recent survey submissions
- New high-risk flags
- Follow-up reminders
- System notifications

### Key Metrics

Quick stats including:
- Total contacts
- Survey response rates
- Average wellness scores
- Week-over-week changes

## Customizing Your View

### Adding Widgets

1. Click **Customize** in the top-right corner
2. Browse available widgets
3. Drag widgets to add them to your dashboard
4. Arrange by dragging to reorder

### Filtering by Date

Use the date picker to focus on specific time periods:
- **Today** — See what's happened today
- **This Week** — Current week's activity
- **This Month** — Monthly overview
- **Custom Range** — Select specific dates

### Filtering by Group

If you manage multiple groups, use the filter dropdown to focus on:
- Specific grade levels
- Contact lists
- Locations or buildings

## Reading the Trends

Look for these indicators:

**Positive Signs:**
- Green arrows (improvement)
- Increasing response rates
- Fewer high-risk flags over time

**Warning Signs:**
- Red arrows (decline)
- Sudden spikes in risk levels
- Dropping response rates

## Daily Workflow

We recommend this daily routine:

1. **Morning check** — Review overnight submissions and new high-risk flags
2. **Midday scan** — Check response rates on active surveys
3. **End of day** — Document any follow-ups and plan tomorrow's priorities
MARKDOWN;
    }

    private function getRiskLevelsContent(): string
    {
        return <<<'MARKDOWN'
# Understanding Risk Levels

Risk levels help you quickly identify who needs support and prioritize your outreach efforts.

## The Three Risk Levels

### Good Standing (Green)

Contacts in good standing show no current concerns based on their survey responses.

**What this means:**
- Their responses indicate positive wellbeing
- No immediate intervention needed
- Continue regular check-ins to maintain connection

**Your action:** No immediate action required, but stay connected through regular surveys.

---

### Low Risk (Yellow)

Low risk indicates some concerning signals that warrant monitoring.

**What this means:**
- Some responses suggest mild concerns
- The situation may improve on its own
- Proactive support could prevent escalation

**Your action:**
- Make a note in their profile
- Consider a casual check-in
- Ensure they know about available resources
- Monitor their next survey responses closely

---

### High Risk (Red)

High risk contacts need your attention as soon as possible.

**What this means:**
- Responses indicate significant concerns
- Intervention is recommended
- Time-sensitive follow-up may be needed

**Your action:**
- Review their recent responses immediately
- Follow your organization's intervention protocol
- Document your outreach in their profile
- Consider involving additional support staff

## How Risk Levels Are Calculated

Risk levels are determined by survey responses using these factors:

1. **Response scores** — Answers to scaled questions are weighted and summed
2. **Flag triggers** — Certain responses automatically elevate risk
3. **Trend analysis** — Declining scores over time may increase risk level
4. **Manual overrides** — Staff can adjust risk levels based on their knowledge

## Responding to Risk Level Changes

### When Someone Moves to High Risk

1. **Review immediately** — Look at the specific responses that triggered the change
2. **Check history** — Has this person been flagged before?
3. **Reach out** — Make contact within your organization's required timeframe
4. **Document** — Record your outreach and any outcomes
5. **Follow up** — Schedule a check-in to reassess

### When Someone Improves

Celebrate improvement! When someone moves from high risk to good standing:
- Note the positive change in their profile
- Consider what interventions helped
- Share success patterns with your team

## Best Practices

- **Don't rely solely on risk levels** — Use your professional judgment too
- **Look at the full picture** — Review complete survey responses, not just the score
- **Document everything** — Notes help colleagues understand the situation
- **Follow protocols** — Your organization may have specific procedures for each risk level
MARKDOWN;
    }

    private function getContactListsContent(): string
    {
        return <<<'MARKDOWN'
# Managing Contact Lists

Contact lists help you organize people into groups for surveys, communications, and reporting.

## Why Use Lists?

Lists let you:
- **Target surveys** to specific groups
- **Send communications** to the right people
- **Compare data** across different populations
- **Filter reports** by group

## Creating a List

1. Go to **Contacts** in the sidebar
2. Click **Manage Lists**
3. Click **Create New List**
4. Enter a name (e.g., "9th Grade", "Building A", "Soccer Team")
5. Add a description to help others understand the list's purpose
6. Click **Create**

## Adding Contacts to Lists

### Add Individual Contacts

1. Open a contact's profile
2. Click **Add to List**
3. Select one or more lists
4. Click **Save**

### Bulk Add from Filters

1. Go to **Contacts**
2. Use filters to find the contacts you want (e.g., all 10th graders)
3. Select contacts using checkboxes
4. Click **Add to List**
5. Choose the destination list

### Import from File

1. Go to **Manage Lists**
2. Select a list
3. Click **Import**
4. Upload a CSV file with contact identifiers
5. Map the columns and confirm

## Managing List Membership

### View List Members

1. Go to **Contacts > Manage Lists**
2. Click on a list name
3. View all members and their current status

### Remove from List

1. Open the list
2. Select contacts to remove
3. Click **Remove from List**

Note: Removing from a list doesn't delete the contact from Pulse.

## Smart Lists (Dynamic)

Some lists update automatically based on criteria:

- **By Risk Level** — All high-risk contacts
- **By Grade** — All students in a specific grade
- **By Response** — Contacts who gave specific survey answers

Smart lists always show current data without manual updates.

## Tips for Organizing Lists

1. **Use clear names** — "Fall 2024 Survey Group" is better than "Group 1"
2. **Add descriptions** — Help others understand each list's purpose
3. **Review regularly** — Remove outdated lists to keep things tidy
4. **Nest when needed** — Create sub-lists for more granular targeting
MARKDOWN;
    }

    private function getContactProfilesContent(): string
    {
        return <<<'MARKDOWN'
# Viewing Contact Profiles

Each contact has a detailed profile page showing their information, history, and wellness trends.

## Accessing a Profile

1. Go to **Contacts**
2. Search or filter to find the contact
3. Click their name to open the profile

## Profile Sections

### Overview

The top section shows at-a-glance information:
- **Name and contact details**
- **Current risk level** with color indicator
- **Grade level** (if applicable)
- **List memberships**

### Wellness Trend

A visual chart showing how their wellness scores have changed over time:
- **Green line** indicates improvement
- **Red line** indicates decline
- **Hover** over points to see specific scores

### Survey History

All surveys this contact has completed:
- Survey name and date
- Response summary
- Any flags triggered
- Click to view full responses

### Notes

Staff notes and documentation:
- Date and author of each note
- Important observations
- Follow-up reminders
- Click **Add Note** to document interactions

### Assigned Resources

Courses and resources assigned to this contact:
- Assignment date
- Completion status
- Due dates if applicable

## Taking Action from a Profile

### Quick Actions

The profile toolbar lets you:
- **Send Message** — Email or message this contact directly
- **Assign Resource** — Send them a course or material
- **Add to List** — Include them in a contact list
- **Add Note** — Document an observation or interaction

### Changing Risk Level

If you need to manually adjust risk level:
1. Click the risk level badge
2. Select the new level
3. Add a note explaining why (required for auditing)

## Privacy and Access

- Only authorized staff can view contact profiles
- All profile views are logged for compliance
- Some fields may be hidden based on your role
MARKDOWN;
    }

    private function getCreateSurveyContent(): string
    {
        return <<<'MARKDOWN'
# Creating Your First Survey

Surveys are your primary tool for collecting wellness data. This guide walks you through creating and distributing your first survey.

## Starting a New Survey

1. Go to **Collect** in the sidebar
2. Click **Create Survey**
3. Choose: **Start from scratch** or **Use a template**

For your first survey, we recommend starting with a template.

## Using a Template

1. Click **Browse Templates**
2. Preview available templates
3. Select one that fits your needs
4. Click **Use Template**
5. Customize as needed

Popular templates include:
- **Weekly Wellness Check** — Quick 5-question check-in
- **Comprehensive Assessment** — Detailed wellness evaluation
- **Follow-Up Survey** — Post-intervention check-in

## Building from Scratch

### Basic Information

1. **Survey Title** — Clear, simple name (e.g., "Weekly Check-In")
2. **Description** — Brief explanation for respondents
3. **Category** — Helps organize your surveys

### Adding Questions

Click **Add Question** and choose a type:
- **Multiple Choice** — Select one option
- **Checkbox** — Select multiple options
- **Scale** — Rate on a numbered scale (1-5, 1-10)
- **Text** — Open-ended written response
- **Yes/No** — Simple binary choice

For each question:
1. Enter the question text
2. Add answer options (if applicable)
3. Mark as required or optional
4. Set any scoring weights for risk calculation

### Organizing Questions

- **Drag to reorder** questions
- **Group related questions** into sections
- **Add page breaks** for longer surveys
- **Preview often** to see the respondent experience

## Configuring Survey Settings

### Response Settings

- **Anonymous responses** — Hide respondent identity
- **Allow multiple submissions** — Let people respond more than once
- **Show progress bar** — Help respondents know how far along they are

### Risk Flagging

Configure which responses trigger risk level changes:
1. Go to **Risk Settings** tab
2. Set thresholds for low and high risk
3. Mark specific answers as automatic flags
4. Preview the risk calculation

### Scheduling

- **Open immediately** or set a future start date
- **Set a deadline** or leave open indefinitely
- **Send reminders** to non-respondents

## Testing Your Survey

**Always preview before sending:**

1. Click **Preview** to see the survey as respondents will
2. Submit a test response
3. Check that risk scoring works as expected
4. Review the confirmation message

## Distributing the Survey

When ready:

1. Click **Distribute**
2. Select your audience:
   - A contact list
   - All contacts
   - Specific filters
3. Choose delivery method (email, link, both)
4. Customize the invitation message
5. Click **Send**

## After Sending

Monitor your survey from the **Responses** tab:
- Track response rate
- See real-time submissions
- Send reminders to non-respondents
- Close early if needed
MARKDOWN;
    }

    private function getQuestionTypesContent(): string
    {
        return <<<'MARKDOWN'
# Survey Question Types

Choosing the right question type helps you collect better data. Here's when to use each type.

## Multiple Choice

**Best for:** Questions with one clear answer from a defined set of options.

**Example:**
> How would you describe your sleep last night?
> - Great, I feel well-rested
> - Okay, about average
> - Poor, I didn't sleep well
> - I barely slept at all

**Tips:**
- Keep options mutually exclusive
- Include 4-6 options typically
- Consider an "Other" option if needed
- Order options logically

---

## Checkbox (Multi-Select)

**Best for:** Questions where multiple answers apply.

**Example:**
> Which of the following have you experienced this week? (Select all that apply)
> - [ ] Stress about schoolwork
> - [ ] Conflict with friends
> - [ ] Feeling tired
> - [ ] Difficulty concentrating
> - [ ] None of these

**Tips:**
- Make clear that multiple selections are allowed
- Include "None of the above" option
- Don't include too many options (cognitive overload)

---

## Rating Scale

**Best for:** Measuring intensity, frequency, or agreement.

**Example:**
> On a scale of 1-5, how supported do you feel at school?
>
> 1 (Not at all) ——— 5 (Very supported)

**Tips:**
- Use consistent scales throughout (don't mix 1-5 and 1-10)
- Label the endpoints clearly
- 5-point scales work well for most questions
- Consider including a midpoint for neutral responses

---

## Open Text

**Best for:** Gathering detailed feedback, stories, or explanations.

**Example:**
> Is there anything else you'd like to share about how you're feeling?
>
> [Text box]

**Tips:**
- Use sparingly — text responses require manual review
- Make optional unless truly necessary
- Place at the end of sections or surveys
- Provide enough space for meaningful responses

---

## Yes/No

**Best for:** Simple binary questions, screening questions.

**Example:**
> Have you talked to a trusted adult about how you're feeling?
> - Yes
> - No

**Tips:**
- Good for screening and branching logic
- Can trigger follow-up questions based on answer
- Consider adding "Prefer not to answer" option

---

## Matrix/Grid

**Best for:** Rating multiple items on the same scale.

**Example:**
> Rate how often you've experienced each of the following:
>
> |                    | Never | Sometimes | Often | Always |
> |--------------------|-------|-----------|-------|--------|
> | Feeling anxious    |   ○   |     ○     |   ○   |   ○    |
> | Trouble sleeping   |   ○   |     ○     |   ○   |   ○    |
> | Loss of appetite   |   ○   |     ○     |   ○   |   ○    |

**Tips:**
- Limit to 5-7 rows maximum
- Keep scale labels visible
- Mobile users may find grids harder to use

---

## Best Practices

1. **Start easy** — Begin with simple, non-threatening questions
2. **Group logically** — Keep related questions together
3. **Watch survey length** — Shorter surveys get better completion rates
4. **Test on mobile** — Many respondents use phones
5. **Review for bias** — Avoid leading questions
MARKDOWN;
    }

    private function getReviewResponsesContent(): string
    {
        return <<<'MARKDOWN'
# Reviewing Survey Responses

After collecting responses, you'll need to review them and take appropriate action.

## Accessing Responses

1. Go to **Collect** in the sidebar
2. Find your survey
3. Click **View Responses**

## Response Overview

The overview shows:
- **Total responses** and response rate
- **Risk distribution** of respondents
- **Completion timeline** graph
- **Key metrics** summary

## Viewing Individual Responses

### Response List

The list shows each submission with:
- Respondent name (unless anonymous)
- Submission date/time
- Risk level assigned
- Quick action buttons

Click any row to see the full response.

### Response Detail

The detail view shows:
- All questions and answers
- Scores for each section
- Any flags triggered
- Risk level explanation

### Taking Action

From a response, you can:
- **Add note** — Document your observations
- **Assign resource** — Send helpful materials
- **Contact** — Reach out directly
- **Change risk level** — Override if needed

## Filtering Responses

Use filters to find specific responses:

- **By risk level** — Focus on high-risk first
- **By date** — Recent vs. older responses
- **By completion** — Complete vs. partial
- **By flag** — Specific concerning answers
- **By contact list** — Specific groups

## Prioritizing Review

We recommend this review order:

1. **High risk first** — These need immediate attention
2. **New flags** — Newly triggered concerns
3. **Declining trends** — People getting worse over time
4. **Incomplete surveys** — May indicate disengagement

## Bulk Actions

For efficiency with many responses:

1. Select multiple responses using checkboxes
2. Choose an action:
   - Export selected
   - Add all to a list
   - Send bulk message

## Exporting Data

Export responses for external analysis:

1. Click **Export**
2. Choose format (CSV or Excel)
3. Select which fields to include
4. Include or exclude identifying information
5. Download the file

## Best Practices

- **Review within 24 hours** — Timely response matters
- **Document everything** — Add notes for future reference
- **Look for patterns** — Multiple similar responses may indicate systemic issues
- **Close the loop** — Follow up on interventions to see if they helped
MARKDOWN;
    }

    private function getBuildReportsContent(): string
    {
        return <<<'MARKDOWN'
# Building Custom Reports

Create professional reports to share insights with stakeholders, leadership, and your team.

## Starting a New Report

1. Go to **Reports** in the sidebar
2. Click **Create Report**
3. Choose a starting point:
   - **Blank report** — Start from scratch
   - **Template** — Use a pre-built format
   - **Duplicate existing** — Copy and modify

## The Report Builder

### Canvas

The main area where you build your report:
- Drag elements from the sidebar
- Click to select and edit
- Resize and reposition freely

### Element Types

**Charts**
- Bar charts
- Line graphs
- Pie charts
- Trend visualizations

**Metrics**
- Single number displays
- Comparison metrics (vs. last period)
- Progress indicators

**Text**
- Headings
- Body text
- Callout boxes

**Tables**
- Data grids
- Contact lists
- Response summaries

### Adding Elements

1. Click an element type in the sidebar
2. It appears on your canvas
3. Click to select and configure
4. Drag to reposition

### Configuring Elements

Select an element to see its options:

**For charts:**
- Data source (which survey/metric)
- Date range
- Grouping (by grade, list, etc.)
- Colors and style

**For metrics:**
- Which metric to display
- Comparison period
- Format (number, percent, etc.)

## Filtering Report Data

Set global filters that apply to all elements:

1. Click **Filters** in the toolbar
2. Set date range
3. Choose contact lists to include
4. Select grade levels
5. Apply other criteria

## Report Settings

### Page Setup

- **Size** — Letter, A4, or custom
- **Orientation** — Portrait or landscape
- **Margins** — Adjust spacing

### Branding

- **Logo** — Add your organization's logo
- **Colors** — Match your brand colors
- **Footer** — Add custom footer text

### Multi-Page Reports

For longer reports:
1. Click **Add Page**
2. Navigate between pages
3. Each page can have different elements

## Saving and Organizing

- **Save often** — Click Save or use Ctrl+S
- **Name clearly** — "Q1 2024 Wellness Summary" not "Report 1"
- **Use folders** — Organize reports by type or date
- **Version notes** — Document what changed

## Tips for Great Reports

1. **Know your audience** — Executive summaries vs. detailed analysis
2. **Lead with insights** — Put key findings first
3. **Use visuals wisely** — Charts are great, but don't overdo it
4. **Add context** — Explain what the numbers mean
5. **Include actions** — What should readers do with this information?
MARKDOWN;
    }

    private function getExportReportsContent(): string
    {
        return <<<'MARKDOWN'
# Exporting and Sharing Reports

Once your report is ready, share it with stakeholders in the format that works best for them.

## Export to PDF

The most common way to share reports:

1. Open your report
2. Click **Export** in the toolbar
3. Select **PDF**
4. Choose options:
   - Include cover page
   - Add page numbers
   - Set quality (standard/high)
5. Click **Download**

The PDF preserves your formatting exactly as designed.

## Share via Link

Give others access to view the report online:

1. Click **Share** in the toolbar
2. Toggle **Enable sharing link**
3. Set permissions:
   - **View only** — Can see but not edit
   - **Can comment** — Add feedback
4. Copy the link
5. Send to recipients

### Link Settings

- **Expire after** — Auto-disable after a time period
- **Require login** — Only logged-in users can view
- **Password protect** — Add an extra security layer

## Schedule Email Delivery

Automatically send reports on a schedule:

1. Click **Share > Schedule**
2. Set frequency (daily, weekly, monthly)
3. Choose day and time
4. Add recipient emails
5. Customize the email message
6. Enable the schedule

Scheduled reports always use the latest data.

## Embed in Presentations

For PowerPoint or Google Slides:

1. Export specific charts as images
2. Or export the full report as PDF
3. Insert into your presentation
4. Link back to live report for updates

## Printing

For physical copies:

1. Export to PDF first (best quality)
2. Print from your PDF viewer
3. Or use **File > Print** directly
4. Adjust print settings as needed

## Best Practices

- **Check before sharing** — Preview to catch errors
- **Use consistent naming** — Help recipients identify reports
- **Set appropriate access** — Not everyone needs to see everything
- **Track who viewed** — Some reports may need audit trails
- **Update regularly** — Don't let shared reports get stale
MARKDOWN;
    }

    private function getCompareGroupsContent(): string
    {
        return <<<'MARKDOWN'
# Comparing Groups in Reports

Comparison reports help you identify differences between groups and track relative performance.

## Why Compare?

Comparisons help you:
- Identify groups that need more support
- Measure intervention effectiveness
- Spot trends that affect specific populations
- Allocate resources appropriately

## Setting Up Comparisons

### Compare Contact Lists

1. In the report builder, add a chart
2. Click **Configure**
3. Under **Group by**, select **Contact List**
4. Choose which lists to compare
5. The chart shows each list side-by-side

### Compare Time Periods

1. Add a chart to your report
2. Set the primary date range
3. Enable **Compare to previous period**
4. Choose comparison (vs. last week, month, year)
5. See current vs. past performance

### Compare Grade Levels

1. Configure a chart element
2. Group by **Grade Level**
3. Select which grades to include
4. View breakdown by grade

## Reading Comparison Charts

### Bar Charts

- Each group gets a different color
- Taller bars = higher values
- Look for significant differences

### Line Charts

- Each group is a separate line
- Parallel lines = similar trends
- Diverging lines = different trajectories

### Tables

- Rows for each group
- Columns for metrics
- Highlight cells that need attention

## Adding Context

Help readers understand what they're seeing:

1. **Add text annotations** — Explain significant differences
2. **Include benchmarks** — Show targets or averages
3. **Note sample sizes** — Smaller groups have more variability
4. **Explain methodology** — How were groups defined?

## Common Comparisons

### Before vs. After

Measure intervention impact:
- Select contacts who received intervention
- Compare their scores before and after
- Show improvement (or lack thereof)

### High Risk vs. General Population

Understand the high-risk group:
- Create a list of high-risk contacts
- Compare to overall population
- Identify distinguishing factors

### Year-over-Year

Track long-term progress:
- Same survey, same time of year
- Current year vs. previous year
- Show progress or areas of concern

## Tips

- **Compare like with like** — Similar group sizes, same time periods
- **Don't over-compare** — Too many groups makes charts unreadable
- **Statistical significance** — Small differences may not be meaningful
- **Tell the story** — What action should readers take?
MARKDOWN;
    }

    private function getSendCommunicationContent(): string
    {
        return <<<'MARKDOWN'
# Sending Your First Communication

The Distribute feature lets you send emails and messages to your contacts.

## Creating a Communication

1. Go to **Distribute** in the sidebar
2. Click **Create Message**
3. Choose a type:
   - **Email** — Traditional email delivery
   - **In-App Message** — Shows in Pulse when they log in

## Writing Your Message

### Subject Line

Keep it clear and relevant:
- Good: "Weekly Wellness Resources"
- Bad: "Important Message"

### Message Body

The editor supports:
- **Rich text** — Bold, italic, lists
- **Links** — To resources or external sites
- **Merge fields** — Personalize with {{first_name}}, etc.
- **Images** — Add visuals (use sparingly)

### Merge Fields Available

- `{{first_name}}` — Contact's first name
- `{{last_name}}` — Contact's last name
- `{{grade}}` — Grade level
- `{{risk_level}}` — Current risk status

## Selecting Recipients

Choose who receives your message:

### Send to a List

1. Click **Select Recipients**
2. Choose **Contact List**
3. Pick one or more lists
4. Review the count

### Send by Filter

1. Click **Select Recipients**
2. Choose **By Criteria**
3. Set filters:
   - Risk level
   - Grade
   - Survey status
4. Preview matched contacts

### Send to Individuals

1. Click **Select Recipients**
2. Choose **Specific Contacts**
3. Search and add individuals
4. Review your selection

## Before Sending

### Preview

1. Click **Preview**
2. See how the message looks
3. Check merge fields populate correctly
4. Review on mobile and desktop views

### Test Send

1. Click **Send Test**
2. Enter your email
3. Receive the test
4. Verify everything looks right

## Sending Options

### Send Now

Click **Send** to deliver immediately to all recipients.

### Schedule

1. Click **Schedule**
2. Choose date and time
3. Messages send automatically
4. You can cancel before the scheduled time

### Save as Draft

Not ready yet? Save and come back later.

## After Sending

Track your communication:
- **Sent count** — How many received it
- **Open rate** — How many opened (email only)
- **Click rate** — How many clicked links
- **Bounces** — Failed deliveries

## Best Practices

- **Personalize** — Use merge fields to make messages feel personal
- **Be concise** — Respect your readers' time
- **Clear call-to-action** — What should they do next?
- **Mobile-friendly** — Most people read on phones
- **Test first** — Always send yourself a test
MARKDOWN;
    }

    private function getScheduleMessagesContent(): string
    {
        return <<<'MARKDOWN'
# Scheduling Automated Messages

Set up messages to send automatically based on time or triggers.

## Scheduled Messages

Send at a specific future date/time:

1. Create your message in Distribute
2. Instead of **Send Now**, click **Schedule**
3. Pick the date and time
4. Click **Schedule Send**

The message waits in your queue until the scheduled time.

### Managing Scheduled Messages

View and manage scheduled messages:
1. Go to **Distribute > Scheduled**
2. See all pending messages
3. Edit, reschedule, or cancel

## Recurring Messages

Send the same message on a regular schedule:

1. Create your message
2. Click **Make Recurring**
3. Choose frequency:
   - Daily
   - Weekly (pick day)
   - Monthly (pick date)
4. Set start and end dates
5. Activate the schedule

### Examples

- **Weekly check-in reminder** — Every Monday at 8am
- **Monthly newsletter** — First of each month
- **End-of-day summary** — Daily at 4pm

## Triggered Messages

Send automatically when something happens:

### Available Triggers

- **New high-risk flag** — When someone becomes high risk
- **Survey completion** — When someone finishes a survey
- **Course completion** — When someone finishes a course
- **Birthday** — On contact's birthday

### Setting Up Triggers

1. Go to **Distribute > Automations**
2. Click **Create Automation**
3. Select a trigger
4. Write the message
5. Activate

### Example: High Risk Welcome

When someone is flagged high risk:
1. Trigger: Risk level changes to High
2. Message: "We noticed you might be going through a tough time. Here are some resources..."
3. Include: Links to support resources
4. Send: Immediately

## Managing Automations

### Pause/Resume

Toggle automations on/off without deleting:
- Click the toggle to pause
- Messages stop sending
- Click again to resume

### Edit

Update the message or trigger:
1. Click the automation
2. Make changes
3. Save

Changes apply to future sends only.

### View History

See what's been sent:
1. Click the automation
2. View **Send History**
3. See each triggered send

## Best Practices

- **Don't over-automate** — Too many messages = fatigue
- **Review regularly** — Make sure content is still relevant
- **Respect timing** — Don't send at odd hours
- **Test triggers** — Make sure they fire correctly
- **Monitor opt-outs** — If people unsubscribe, adjust your approach
MARKDOWN;
    }

    private function getResourceHubContent(): string
    {
        return <<<'MARKDOWN'
# Browsing the Resource Hub

The Resource Hub contains educational materials, guides, and support resources for your community.

## Accessing Resources

1. Go to **Resources** in the sidebar
2. Browse by category or search
3. Click any resource to view details

## Resource Types

### Documents

PDFs, guides, and handouts:
- Downloadable files
- Printable materials
- Reference guides

### Videos

Educational and instructional videos:
- Embedded player
- Playback controls
- Transcripts (when available)

### Links

External resources:
- Curated website links
- Third-party tools
- Partner resources

### Courses

Multi-step learning experiences:
- Sequential modules
- Progress tracking
- Completion certificates

## Finding Resources

### Browse by Category

Resources are organized into categories:
- Mental Health
- Physical Wellness
- Academic Support
- Social-Emotional
- Crisis Resources

Click a category to see all related resources.

### Search

1. Type keywords in the search box
2. Results show matching resources
3. Filter by type or category
4. Sort by relevance or date

### Featured Resources

The home page shows:
- Highlighted resources
- Recently added
- Most popular

## Using Resources

### View Details

Click a resource to see:
- Full description
- Author/source
- Date added
- Related resources

### Download or Open

- **Documents** — Download to your device
- **Videos** — Play in browser
- **Links** — Open in new tab
- **Courses** — Start the course

### Share

Send resources to contacts:
1. Click **Share**
2. Choose recipients
3. Add a personal message
4. Send

### Favorite

Save resources for quick access:
1. Click the heart icon
2. Find favorites in **My Favorites**

## For Administrators

### Adding Resources

1. Click **Add Resource**
2. Fill in details
3. Upload file or enter URL
4. Assign categories
5. Set visibility
6. Publish

### Managing Resources

- Edit existing resources
- Archive outdated content
- Track usage statistics
- Organize categories
MARKDOWN;
    }

    private function getAssignCoursesContent(): string
    {
        return <<<'MARKDOWN'
# Assigning Courses to Contacts

Courses are multi-step learning experiences you can assign to help contacts build skills and knowledge.

## What Are Courses?

Courses include:
- **Modules** — Individual lessons or topics
- **Content** — Videos, readings, activities
- **Assessments** — Quizzes or reflections
- **Completion tracking** — Progress monitoring

## Assigning a Course

### To an Individual

1. Find the course in **Resources > Courses**
2. Click **Assign**
3. Search for the contact
4. Set optional due date
5. Add a personal message
6. Click **Assign**

### To a Group

1. Find the course
2. Click **Assign**
3. Select **Contact List**
4. Choose one or more lists
5. Set optional due date
6. Click **Assign to All**

### From a Contact Profile

1. Open the contact's profile
2. Click **Assign Resource**
3. Select a course
4. Set due date if needed
5. Confirm assignment

## Managing Assignments

### View All Assignments

Go to **Resources > Assignments** to see:
- All active assignments
- Completion status
- Due dates
- Progress percentages

### Track Progress

For each assignment:
- **Not Started** — Haven't begun
- **In Progress** — Partially complete
- **Completed** — Finished all modules
- **Overdue** — Past due date, not complete

### Send Reminders

For incomplete assignments:
1. Select the assignment
2. Click **Send Reminder**
3. Customize message (optional)
4. Send

### Extend Due Dates

If someone needs more time:
1. Find their assignment
2. Click **Edit**
3. Change the due date
4. Save

## Assignment Settings

When assigning, configure:

- **Due date** — When should they complete it?
- **Required** — Must complete vs. optional
- **Notification** — Email them about the assignment?
- **Message** — Personal note to include

## Completion Reports

Track course completion rates:

1. Go to **Resources > Reports**
2. Select a course
3. View completion statistics:
   - Overall completion rate
   - Average time to complete
   - Module-by-module breakdown
   - List of incomplete contacts

## Tips

- **Set realistic due dates** — Give enough time to complete
- **Send reminders** — Gentle nudges help completion
- **Make it relevant** — Assign courses that match needs
- **Follow up** — Check in after completion
- **Track patterns** — Low completion may indicate issues with the course
MARKDOWN;
    }

    private function getOrgSettingsContent(): string
    {
        return <<<'MARKDOWN'
# Managing Organization Settings

Configure your organization's settings to customize Pulse for your needs.

## Accessing Settings

1. Click your profile icon (top right)
2. Select **Settings**
3. Navigate through setting categories

## General Settings

### Organization Profile

- **Name** — Your organization's display name
- **Logo** — Appears in reports and communications
- **Time zone** — For scheduling and timestamps
- **Academic year** — Define your year boundaries

### Terminology

Customize the labels used throughout Pulse:

- **Student** → Participant, Client, Member, etc.
- **Grade** → Level, Year, Cohort, etc.
- **Teacher** → Counselor, Advisor, Coach, etc.

This helps Pulse match your organization's language.

## Notification Settings

### Email Notifications

Configure when you receive emails:
- High-risk alerts
- Survey completions
- Daily/weekly digests
- System announcements

### Alert Thresholds

Set when alerts trigger:
- Risk level changes
- Response rate drops
- Specific survey answers

## Privacy & Security

### Data Retention

Configure how long data is kept:
- Survey responses
- User activity logs
- Deleted records

### Access Controls

Manage who can see what:
- Role-based permissions
- Data visibility rules
- Feature access

### Audit Logs

View system activity:
- Who accessed what
- Configuration changes
- Export history

## Branding

### Colors

Customize the color scheme:
- Primary color
- Accent color
- Button styles

### Logo & Images

Upload your branding:
- Main logo
- Favicon
- Report headers

### Email Templates

Customize system emails:
- Header/footer
- Color scheme
- Contact information

## Integrations

### Connected Systems

Manage external connections:
- Student Information Systems
- Single Sign-On providers
- Email services

### API Access

For technical integrations:
- API keys
- Webhook configurations
- Data sync settings

## Saving Changes

Most settings save automatically. Look for:
- ✓ Saved indicator
- Or click **Save Changes** button

Some changes require confirmation or may affect existing data.
MARKDOWN;
    }

    private function getRolesPermissionsContent(): string
    {
        return <<<'MARKDOWN'
# User Roles and Permissions

Different users have different levels of access in Pulse based on their role.

## Available Roles

### Administrator

Full access to all features:
- Manage all users and settings
- Access all data
- Configure organization settings
- View audit logs
- Delete records

### Counselor / Advisor

Primary user role for support staff:
- View and manage assigned contacts
- Create and send surveys
- Build reports
- Send communications
- Access resource hub

### Teacher / Staff

Limited access for general staff:
- View assigned contacts only
- Complete surveys
- Access resources
- View (not create) reports

### Student / Participant

Self-service access:
- Complete assigned surveys
- View own data
- Access assigned resources
- Update own profile

## Permission Details

### Contact Access

| Action | Admin | Counselor | Teacher | Student |
|--------|-------|-----------|---------|---------|
| View all contacts | ✓ | ✓ | — | — |
| View assigned only | ✓ | ✓ | ✓ | — |
| Edit contacts | ✓ | ✓ | — | — |
| Delete contacts | ✓ | — | — | — |
| Add notes | ✓ | ✓ | ✓ | — |

### Survey Access

| Action | Admin | Counselor | Teacher | Student |
|--------|-------|-----------|---------|---------|
| Create surveys | ✓ | ✓ | — | — |
| Send surveys | ✓ | ✓ | — | — |
| View responses | ✓ | ✓ | Limited | Own only |
| Delete surveys | ✓ | — | — | — |

### Report Access

| Action | Admin | Counselor | Teacher | Student |
|--------|-------|-----------|---------|---------|
| Create reports | ✓ | ✓ | — | — |
| View all reports | ✓ | ✓ | ✓ | — |
| Export data | ✓ | ✓ | — | — |
| Delete reports | ✓ | — | — | — |

## Managing Users

### Invite New Users

1. Go to **Settings > Users**
2. Click **Invite User**
3. Enter email address
4. Select role
5. Send invitation

### Change User Role

1. Find the user in **Settings > Users**
2. Click **Edit**
3. Select new role
4. Save changes

### Deactivate Users

When someone leaves:
1. Find their account
2. Click **Deactivate**
3. They lose access immediately
4. Their data is preserved

### Reactivate Users

If someone returns:
1. Find them in inactive users
2. Click **Reactivate**
3. They regain previous access

## Best Practices

- **Principle of least privilege** — Give minimum access needed
- **Review regularly** — Audit user access periodically
- **Offboard promptly** — Deactivate departed users quickly
- **Document decisions** — Note why users have specific roles
- **Train users** — Make sure people understand their access level
MARKDOWN;
    }
}
