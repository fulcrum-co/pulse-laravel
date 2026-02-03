<?php

namespace Database\Seeders;

use App\Models\MarketplaceItem;
use App\Models\MarketplacePricing;
use App\Models\MarketplaceReview;
use App\Models\Organization;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class MarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('org_type', 'organization')->first();
        if (! $organization) {
            $organization = Organization::first();
        }
        if (! $organization) {
            $this->log('No organization found. Please seed organizations first.', 'error');

            return;
        }

        // Create seller profiles
        $sellers = $this->createSellerProfiles($organization);

        // Create marketplace items
        $items = $this->createMarketplaceItems($sellers, $organization);

        // Create reviews for items
        $this->createReviews($items, $organization);

        $this->log('Marketplace seeded successfully!');
        $this->log('- '.count($sellers).' seller profiles created');
        $this->log('- '.count($items).' marketplace items created');
    }

    private function log(string $message, string $type = 'info'): void
    {
        if ($this->command) {
            $type === 'error' ? $this->command->error($message) : $this->command->info($message);
        }
    }

    private function createSellerProfiles(Organization $organization): array
    {
        $sellers = [];

        // Use existing users as sellers
        $existingUsers = User::where('org_id', $organization->id)
            ->whereIn('primary_role', ['admin', 'teacher'])
            ->limit(3)
            ->get();

        foreach ($existingUsers as $index => $user) {
            $sellers[] = SellerProfile::create([
                'user_id' => $user->id,
                'org_id' => $organization->id,
                'display_name' => $user->first_name.' '.$user->last_name,
                'bio' => 'Passionate educator sharing resources to help learners succeed.',
                'avatar_url' => $user->avatar_url,
                'expertise_areas' => $this->getRandomExpertise(),
                'credentials' => $this->getRandomCredentials(),
                'seller_type' => $index === 0 ? SellerProfile::TYPE_VERIFIED_EDUCATOR : SellerProfile::TYPE_INDIVIDUAL,
                'is_verified' => $index < 2,
                'verified_at' => $index < 2 ? now()->subMonths(rand(1, 6)) : null,
                'verification_badge' => $index === 0 ? SellerProfile::BADGE_EDUCATOR : ($index === 1 ? SellerProfile::BADGE_TOP_SELLER : null),
                'total_sales' => rand(50, 500),
                'total_items' => rand(5, 25),
                'lifetime_revenue' => rand(500, 5000),
                'ratings_average' => rand(42, 50) / 10,
                'ratings_count' => rand(20, 200),
                'active' => true,
            ]);
        }

        // Create additional external sellers
        $externalSellers = [
            [
                'display_name' => 'Dr. Rachel Martinez',
                'bio' => 'Clinical psychologist and SEL curriculum developer. 20+ years working with K-12 learners on social-emotional learning and mental health.',
                'avatar_url' => 'https://randomuser.me/api/portraits/women/68.jpg',
                'expertise_areas' => ['SEL', 'Mental Health', 'Anxiety', 'Counseling'],
                'credentials' => [
                    ['title' => 'Ph.D. Clinical Psychology', 'issuer' => 'Stanford University', 'year' => '2005'],
                    ['title' => 'Licensed Clinical Psychologist', 'issuer' => 'State Board', 'year' => '2006'],
                ],
                'seller_type' => SellerProfile::TYPE_VERIFIED_EDUCATOR,
                'verification_badge' => SellerProfile::BADGE_EXPERT,
                'is_verified' => true,
                'ratings_average' => 4.9,
                'total_sales' => 1250,
            ],
            [
                'display_name' => 'TeachBetter Academy',
                'bio' => 'Professional development organization dedicated to improving teaching practices through research-based strategies and resources.',
                'avatar_url' => 'https://ui-avatars.com/api/?name=TB&background=f97316&color=fff&size=200',
                'expertise_areas' => ['Professional Development', 'Classroom Management', 'Instructional Design'],
                'credentials' => [
                    ['title' => 'ISTE Certified Organization', 'issuer' => 'ISTE', 'year' => '2022'],
                ],
                'seller_type' => SellerProfile::TYPE_ORGANIZATION,
                'verification_badge' => SellerProfile::BADGE_PARTNER,
                'is_verified' => true,
                'ratings_average' => 4.7,
                'total_sales' => 3500,
            ],
            [
                'display_name' => 'Marcus Johnson',
                'bio' => 'High organization math teacher sharing differentiated resources that make algebra accessible for all learners.',
                'avatar_url' => 'https://randomuser.me/api/portraits/men/75.jpg',
                'expertise_areas' => ['Mathematics', 'Algebra', 'Differentiation', 'Special Education'],
                'credentials' => [
                    ['title' => 'M.Ed. Mathematics Education', 'issuer' => 'UCLA', 'year' => '2018'],
                    ['title' => 'National Board Certified', 'issuer' => 'NBPTS', 'year' => '2021'],
                ],
                'seller_type' => SellerProfile::TYPE_VERIFIED_EDUCATOR,
                'verification_badge' => SellerProfile::BADGE_EDUCATOR,
                'is_verified' => true,
                'ratings_average' => 4.8,
                'total_sales' => 890,
            ],
            [
                'display_name' => 'Emma Wilson',
                'bio' => 'Elementary organization teacher passionate about making reading fun! Creator of engaging literacy resources for K-3.',
                'avatar_url' => 'https://randomuser.me/api/portraits/women/42.jpg',
                'expertise_areas' => ['Literacy', 'Reading', 'Phonics', 'Early Childhood'],
                'credentials' => [
                    ['title' => 'B.A. Early Childhood Education', 'issuer' => 'NYU', 'year' => '2015'],
                ],
                'seller_type' => SellerProfile::TYPE_INDIVIDUAL,
                'verification_badge' => null,
                'is_verified' => false,
                'ratings_average' => 4.6,
                'total_sales' => 340,
            ],
            [
                'display_name' => 'Mindful Organizations Initiative',
                'bio' => 'Nonprofit organization providing mindfulness-based SEL programs for organizations nationwide.',
                'avatar_url' => 'https://ui-avatars.com/api/?name=MS&background=10b981&color=fff&size=200',
                'expertise_areas' => ['Mindfulness', 'SEL', 'Wellness', 'Stress Management'],
                'credentials' => [
                    ['title' => 'CASEL SELect Program', 'issuer' => 'CASEL', 'year' => '2023'],
                ],
                'seller_type' => SellerProfile::TYPE_ORGANIZATION,
                'verification_badge' => SellerProfile::BADGE_PARTNER,
                'is_verified' => true,
                'ratings_average' => 4.9,
                'total_sales' => 2100,
            ],
        ];

        foreach ($externalSellers as $sellerData) {
            $sellers[] = SellerProfile::create([
                'user_id' => User::first()->id, // Placeholder user
                'org_id' => null, // External seller
                'display_name' => $sellerData['display_name'],
                'bio' => $sellerData['bio'],
                'avatar_url' => $sellerData['avatar_url'],
                'expertise_areas' => $sellerData['expertise_areas'],
                'credentials' => $sellerData['credentials'],
                'seller_type' => $sellerData['seller_type'],
                'is_verified' => $sellerData['is_verified'],
                'verified_at' => $sellerData['is_verified'] ? now()->subMonths(rand(1, 12)) : null,
                'verification_badge' => $sellerData['verification_badge'],
                'total_sales' => $sellerData['total_sales'],
                'total_items' => rand(5, 30),
                'lifetime_revenue' => $sellerData['total_sales'] * rand(5, 15),
                'ratings_average' => $sellerData['ratings_average'],
                'ratings_count' => rand(50, 300),
                'active' => true,
            ]);
        }

        return $sellers;
    }

    private function createMarketplaceItems(array $sellers, Organization $organization): array
    {
        $items = [];

        // Survey items
        $surveyItems = [
            [
                'title' => 'Weekly Learner Wellness Check-In',
                'short_description' => 'A quick 5-question wellness check for monitoring learner mental health.',
                'description' => "A research-validated weekly check-in survey designed to quickly assess learner wellness across five key dimensions: emotional state, stress level, sleep quality, social connections, and academic confidence.\n\n**What's Included:**\n- 5 carefully crafted questions with visual response scales\n- Automatic scoring and interpretation guide\n- Trend tracking recommendations\n- Spanish and Chinese translations\n\n**Best For:** K-12 homeroom teachers, counselors, and wellness coordinators",
                'tags' => ['wellness', 'mental health', 'check-in', 'SEL'],
                'target_grades' => ['3-5', '6-8', '9-12'],
                'pricing_type' => MarketplaceItem::PRICING_FREE,
                'is_featured' => true,
            ],
            [
                'title' => 'Comprehensive Anxiety Screening Tool',
                'short_description' => 'Evidence-based 15-question anxiety assessment for adolescents.',
                'description' => "A comprehensive anxiety screening instrument based on the GAD-7 and adapted for organization settings. Includes detailed interpretation guidelines and recommended interventions.\n\n**Clinical Features:**\n- Validated against clinical gold standards\n- Age-appropriate language for grades 6-12\n- Scoring rubric with cutoff thresholds\n- Parent notification letter templates\n- Counselor intervention flowchart\n\n**Best For:** Organization counselors, psychologists, and mental health teams",
                'tags' => ['anxiety', 'mental health', 'assessment', 'screening'],
                'target_grades' => ['6-8', '9-12'],
                'pricing_type' => MarketplaceItem::PRICING_ONE_TIME,
                'price' => 24.99,
            ],
            [
                'title' => 'Classroom Climate Survey Bundle',
                'short_description' => 'Complete set of surveys to assess classroom environment and learner belonging.',
                'description' => "A comprehensive bundle of three classroom climate surveys designed to measure learner sense of belonging, safety, and engagement.\n\n**Bundle Includes:**\n1. Learner Belonging Scale (10 questions)\n2. Classroom Safety Perception Survey (8 questions)\n3. Engagement & Motivation Assessment (12 questions)\n\n**Features:**\n- Pre/post comparison tools\n- Class-level aggregate reports\n- Action planning templates\n- Staff reflection guides",
                'tags' => ['classroom climate', 'belonging', 'engagement', 'organization culture'],
                'target_grades' => ['3-5', '6-8', '9-12'],
                'pricing_type' => MarketplaceItem::PRICING_ONE_TIME,
                'price' => 39.99,
            ],
            [
                'title' => 'MTSS Universal Screener Pack',
                'short_description' => 'Tier 1 academic and behavioral screening tools for MTSS implementation.',
                'description' => "Complete MTSS universal screening package with academic and behavioral assessments aligned to tiered intervention frameworks.\n\n**Includes:**\n- Reading fluency screener\n- Math computation screener\n- Behavioral observation checklist\n- Data analysis spreadsheet\n- Decision rules flowchart\n\n**Designed For:** MTSS coordinators and intervention teams",
                'tags' => ['MTSS', 'RTI', 'screening', 'intervention'],
                'target_grades' => ['K-2', '3-5', '6-8'],
                'pricing_type' => MarketplaceItem::PRICING_RECURRING,
                'recurring_price' => 19.99,
            ],
            [
                'title' => 'Social-Emotional Learning Pre/Post Assessment',
                'short_description' => 'Measure SEL growth with this CASEL-aligned assessment tool.',
                'description' => "Assess learner growth in all five CASEL competencies with this validated pre/post assessment tool.\n\n**Competencies Measured:**\n- Self-Awareness\n- Self-Management\n- Social Awareness\n- Relationship Skills\n- Responsible Decision-Making\n\n**Features:**\n- Learner self-report form\n- Teacher observation form\n- Growth comparison report template\n- Data visualization dashboard",
                'tags' => ['SEL', 'CASEL', 'assessment', 'growth'],
                'target_grades' => ['3-5', '6-8'],
                'pricing_type' => MarketplaceItem::PRICING_ONE_TIME,
                'price' => 29.99,
                'is_featured' => true,
            ],
        ];

        // Strategy/Course items
        $strategyItems = [
            [
                'title' => 'Calm Down Corner Complete Curriculum',
                'short_description' => 'Everything you need to create and manage a classroom calm down space.',
                'description' => "A comprehensive curriculum for implementing calm down corners in K-5 classrooms.\n\n**Includes:**\n- Setup guide with material list\n- 20 calm-down strategy cards\n- Learner self-regulation log\n- Teacher introduction script\n- Parent communication letter\n- Progress monitoring forms\n\n**Outcomes:** Learners learn to identify emotions, select coping strategies, and self-regulate independently.",
                'tags' => ['calm down', 'self-regulation', 'emotions', 'classroom management'],
                'target_grades' => ['K-2', '3-5'],
                'pricing_type' => MarketplaceItem::PRICING_ONE_TIME,
                'price' => 34.99,
                'is_featured' => true,
            ],
            [
                'title' => 'Growth Mindset 6-Week Unit',
                'short_description' => 'Complete lesson plans and activities to develop learner growth mindset.',
                'description' => "Transform learner attitudes about learning with this engaging 6-week growth mindset curriculum.\n\n**Weekly Themes:**\n1. Introduction to Growth Mindset\n2. The Power of Yet\n3. Embracing Challenges\n4. Learning from Mistakes\n5. Effort & Persistence\n6. Celebrating Growth\n\n**Each Week Includes:**\n- 45-minute lesson plan\n- Discussion questions\n- Interactive activity\n- Reflection journal page\n- Parent connection activity",
                'tags' => ['growth mindset', 'motivation', 'resilience', 'SEL'],
                'target_grades' => ['3-5', '6-8'],
                'pricing_type' => MarketplaceItem::PRICING_ONE_TIME,
                'price' => 49.99,
            ],
            [
                'title' => 'Test Anxiety Intervention Program',
                'short_description' => 'Evidence-based 4-session intervention for learners with test anxiety.',
                'description' => "A structured intervention program to help learners overcome test anxiety using cognitive-behavioral techniques.\n\n**4 Sessions:**\n1. Understanding Test Anxiety\n2. Relaxation & Breathing Techniques\n3. Cognitive Restructuring\n4. Test-Taking Strategies\n\n**Materials:**\n- Facilitator guide\n- Learner workbook\n- Parent handout\n- Pre/post assessment\n- Progress tracking form",
                'tags' => ['test anxiety', 'stress', 'intervention', 'CBT'],
                'target_grades' => ['6-8', '9-12'],
                'pricing_type' => MarketplaceItem::PRICING_ONE_TIME,
                'price' => 44.99,
            ],
            [
                'title' => 'Executive Function Skill Builders',
                'short_description' => 'Interactive activities to develop planning, organization, and self-monitoring skills.',
                'description' => "Help learners develop crucial executive function skills with this collection of engaging activities and tools.\n\n**Skills Addressed:**\n- Task initiation\n- Planning & prioritizing\n- Organization\n- Time management\n- Self-monitoring\n- Flexible thinking\n\n**Includes:**\n- 30 activity cards\n- Learner planning templates\n- Visual schedules\n- Self-monitoring checklists\n- Teacher implementation guide",
                'tags' => ['executive function', 'organization', 'ADHD', 'study skills'],
                'target_grades' => ['6-8', '9-12'],
                'pricing_type' => MarketplaceItem::PRICING_RECURRING,
                'recurring_price' => 14.99,
            ],
            [
                'title' => 'Mindfulness in the Classroom',
                'short_description' => 'Daily 5-minute mindfulness practices for busy classrooms.',
                'description' => "Bring the benefits of mindfulness to your classroom with these quick, practical activities.\n\n**Includes:**\n- 60 mindfulness scripts (2-5 minutes each)\n- Breathing exercise cards\n- Body scan audio guides\n- Movement breaks\n- Gratitude journaling prompts\n- Classroom poster set\n\n**Research Shows:** Regular mindfulness practice improves focus, reduces stress, and enhances emotional regulation.",
                'tags' => ['mindfulness', 'wellness', 'focus', 'stress reduction'],
                'target_grades' => ['K-2', '3-5', '6-8'],
                'pricing_type' => MarketplaceItem::PRICING_FREE,
            ],
        ];

        // Content/Resource items
        $contentItems = [
            [
                'title' => 'Emotion Identification Poster Set',
                'short_description' => 'Colorful posters featuring diverse children expressing 12 core emotions.',
                'description' => "Help learners build emotional vocabulary with this beautiful poster set featuring diverse children.\n\n**Includes:**\n- 12 emotion posters (11x17\")\n- Happy, Sad, Angry, Scared, Surprised, Disgusted\n- Anxious, Excited, Frustrated, Calm, Proud, Confused\n\n**Features:**\n- Diverse representation (skin tones, abilities, genders)\n- Child-friendly illustrations\n- Emotion words in English and Spanish\n- Printable PDF and high-res PNG files",
                'tags' => ['emotions', 'posters', 'classroom decor', 'SEL'],
                'target_grades' => ['K-2', '3-5'],
                'pricing_type' => MarketplaceItem::PRICING_ONE_TIME,
                'price' => 12.99,
            ],
            [
                'title' => 'Anxiety Coping Skills Workbook',
                'short_description' => 'Learner workbook with 30 anxiety management activities.',
                'description' => "A comprehensive learner workbook filled with engaging activities to help manage anxiety.\n\n**Sections:**\n1. Understanding Anxiety (psychoeducation)\n2. Body Awareness (recognizing physical symptoms)\n3. Breathing & Relaxation\n4. Thought Challenging\n5. Coping Strategies\n6. Building Confidence\n\n**Features:**\n- 30 printable activity pages\n- Journal prompts\n- Coping cards to cut out\n- Progress tracker\n- Parent guide",
                'tags' => ['anxiety', 'coping skills', 'workbook', 'CBT'],
                'target_grades' => ['6-8', '9-12'],
                'pricing_type' => MarketplaceItem::PRICING_ONE_TIME,
                'price' => 18.99,
                'is_featured' => true,
            ],
            [
                'title' => 'Conflict Resolution Role Play Scenarios',
                'short_description' => '25 realistic scenarios for practicing conflict resolution skills.',
                'description' => "Engage learners in practicing conflict resolution with these realistic, age-appropriate scenarios.\n\n**Includes:**\n- 25 scenario cards\n- Discussion questions for each\n- Role play guidelines\n- De-escalation strategies poster\n- Reflection worksheet\n\n**Scenario Topics:**\n- Peer disagreements\n- Group project conflicts\n- Social media issues\n- Rumors and gossip\n- Competition and jealousy",
                'tags' => ['conflict resolution', 'social skills', 'role play', 'SEL'],
                'target_grades' => ['6-8', '9-12'],
                'pricing_type' => MarketplaceItem::PRICING_ONE_TIME,
                'price' => 14.99,
            ],
            [
                'title' => 'Morning Meeting Activities Bundle',
                'short_description' => 'A full year of morning meeting activities, greetings, and sharing topics.',
                'description' => "Keep your morning meetings fresh and engaging all year long!\n\n**Includes:**\n- 180 greeting ideas\n- 180 sharing prompts\n- 180 activity suggestions\n- Monthly theme calendars\n- Community-building games\n- Seasonal variations\n\n**Organization:**\n- Organized by month\n- Difficulty levels indicated\n- Time estimates included\n- Adaptations for virtual learning",
                'tags' => ['morning meeting', 'community building', 'classroom routine', 'SEL'],
                'target_grades' => ['K-2', '3-5'],
                'pricing_type' => MarketplaceItem::PRICING_ONE_TIME,
                'price' => 24.99,
            ],
            [
                'title' => 'Self-Esteem Building Video Series',
                'short_description' => '10 animated videos teaching positive self-talk and self-worth.',
                'description' => "Help learners develop healthy self-esteem with this engaging animated video series.\n\n**Videos (5-7 minutes each):**\n1. What is Self-Esteem?\n2. Your Inner Voice\n3. Positive Self-Talk\n4. Celebrating Strengths\n5. Handling Mistakes\n6. Setting Boundaries\n7. Comparison Trap\n8. Building Confidence\n9. Asking for Help\n10. Loving Yourself\n\n**Includes:**\n- Streaming access\n- Discussion guides\n- Learner reflection sheets\n- Parent companion guide",
                'tags' => ['self-esteem', 'video', 'positive thinking', 'SEL'],
                'target_grades' => ['3-5', '6-8'],
                'pricing_type' => MarketplaceItem::PRICING_RECURRING,
                'recurring_price' => 9.99,
            ],
            [
                'title' => 'Trauma-Informed Classroom Strategies Guide',
                'short_description' => 'Practical strategies for creating trauma-sensitive learning environments.',
                'description' => "Essential guide for educators working with learners who have experienced trauma.\n\n**Covers:**\n- Understanding trauma and its effects\n- Creating safe classroom environments\n- Recognizing trauma responses\n- De-escalation techniques\n- Building trusting relationships\n- Self-care for educators\n\n**Formats:**\n- 45-page PDF guide\n- Quick reference cards\n- Poster of calming strategies\n- Staff training presentation",
                'tags' => ['trauma-informed', 'classroom management', 'safety', 'professional development'],
                'target_grades' => ['K-2', '3-5', '6-8', '9-12'],
                'pricing_type' => MarketplaceItem::PRICING_FREE,
                'is_featured' => true,
            ],
        ];

        // Provider service items
        $providerItems = [
            [
                'title' => 'Virtual Therapy Services for Organizations',
                'short_description' => 'Licensed therapists providing teletherapy for K-12 learners.',
                'description' => "Connect your learners with licensed mental health professionals through our virtual therapy platform.\n\n**Services:**\n- Individual therapy sessions\n- Group counseling\n- Crisis intervention\n- Parent consultations\n- Teacher consultations\n\n**Features:**\n- HIPAA-compliant platform\n- Flexible scheduling\n- Progress reports to organization\n- Bilingual therapists available\n- Insurance accepted\n\n**Pricing:** Per-learner monthly subscription or per-session options available.",
                'tags' => ['therapy', 'teletherapy', 'mental health', 'counseling'],
                'target_grades' => ['K-2', '3-5', '6-8', '9-12'],
                'pricing_type' => MarketplaceItem::PRICING_RECURRING,
                'recurring_price' => 149.99,
            ],
            [
                'title' => 'Executive Function Coaching Program',
                'short_description' => 'One-on-one coaching for learners with executive function challenges.',
                'description' => "Personalized coaching to help learners develop crucial executive function skills.\n\n**Program Includes:**\n- Initial assessment\n- Personalized coaching plan\n- Weekly 30-minute sessions\n- Parent/teacher check-ins\n- Progress monitoring\n- Strategy toolkit\n\n**Focus Areas:**\n- Organization\n- Time management\n- Task initiation\n- Planning & prioritizing\n- Emotional regulation",
                'tags' => ['executive function', 'coaching', 'ADHD', 'organization'],
                'target_grades' => ['6-8', '9-12'],
                'pricing_type' => MarketplaceItem::PRICING_RECURRING,
                'recurring_price' => 199.99,
            ],
            [
                'title' => 'SEL Curriculum Implementation Support',
                'short_description' => 'Expert consultants to help implement SEL programs district-wide.',
                'description' => "Partner with experienced SEL consultants to successfully implement social-emotional learning across your district.\n\n**Services Include:**\n- Needs assessment\n- Curriculum selection guidance\n- Staff training workshops\n- Implementation coaching\n- Fidelity monitoring\n- Data analysis and reporting\n\n**Our Team:**\n- Former organization counselors\n- SEL curriculum developers\n- Data analysts\n- Professional development specialists",
                'tags' => ['SEL', 'consulting', 'professional development', 'implementation'],
                'target_grades' => ['K-2', '3-5', '6-8', '9-12'],
                'pricing_type' => MarketplaceItem::PRICING_ONE_TIME,
                'price' => 2499.99,
            ],
            [
                'title' => 'Crisis Response Team Training',
                'short_description' => 'Comprehensive training for organization crisis response teams.',
                'description' => "Prepare your crisis response team with evidence-based training and protocols.\n\n**Training Covers:**\n- Crisis identification and assessment\n- Intervention protocols\n- Communication procedures\n- Post-crisis support\n- Documentation requirements\n- Self-care for responders\n\n**Delivery Options:**\n- On-site training (1-2 days)\n- Virtual training modules\n- Hybrid format\n\n**Includes:**\n- Training materials\n- Crisis response manual\n- Template documents\n- Ongoing consultation",
                'tags' => ['crisis', 'training', 'safety', 'professional development'],
                'target_grades' => ['K-2', '3-5', '6-8', '9-12'],
                'pricing_type' => MarketplaceItem::PRICING_ONE_TIME,
                'price' => 4999.99,
                'is_featured' => true,
            ],
        ];

        // Create all items
        $allItems = [
            MarketplaceItem::CATEGORY_SURVEY => $surveyItems,
            MarketplaceItem::CATEGORY_STRATEGY => $strategyItems,
            MarketplaceItem::CATEGORY_CONTENT => $contentItems,
            MarketplaceItem::CATEGORY_PROVIDER => $providerItems,
        ];

        $thumbnails = [
            MarketplaceItem::CATEGORY_SURVEY => [
                'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=400&h=300&fit=crop',
                'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=400&h=300&fit=crop',
                'https://images.unsplash.com/photo-1606326608606-aa0b62935f2b?w=400&h=300&fit=crop',
            ],
            MarketplaceItem::CATEGORY_STRATEGY => [
                'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=400&h=300&fit=crop',
                'https://images.unsplash.com/photo-1577896851231-70ef18881754?w=400&h=300&fit=crop',
                'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=400&h=300&fit=crop',
            ],
            MarketplaceItem::CATEGORY_CONTENT => [
                'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=400&h=300&fit=crop',
                'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&h=300&fit=crop',
                'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=400&h=300&fit=crop',
            ],
            MarketplaceItem::CATEGORY_PROVIDER => [
                'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400&h=300&fit=crop',
                'https://images.unsplash.com/photo-1551836022-d5d88e9218df?w=400&h=300&fit=crop',
                'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=400&h=300&fit=crop',
            ],
        ];

        foreach ($allItems as $category => $categoryItems) {
            foreach ($categoryItems as $index => $itemData) {
                $seller = $sellers[array_rand($sellers)];
                $thumbnailIndex = $index % count($thumbnails[$category]);

                $item = MarketplaceItem::create([
                    'seller_profile_id' => $seller->id,
                    'org_id' => $seller->org_id,
                    'title' => $itemData['title'],
                    'description' => $itemData['description'],
                    'short_description' => $itemData['short_description'],
                    'category' => $category,
                    'tags' => $itemData['tags'],
                    'thumbnail_url' => $thumbnails[$category][$thumbnailIndex],
                    'target_grades' => $itemData['target_grades'],
                    'pricing_type' => $itemData['pricing_type'],
                    'status' => MarketplaceItem::STATUS_APPROVED,
                    'is_featured' => $itemData['is_featured'] ?? false,
                    'is_verified' => $seller->is_verified,
                    'ratings_average' => rand(40, 50) / 10,
                    'ratings_count' => rand(5, 150),
                    'download_count' => rand(50, 1000),
                    'purchase_count' => rand(20, 500),
                    'view_count' => rand(100, 5000),
                    'published_at' => now()->subDays(rand(1, 180)),
                    'created_by' => $seller->user_id,
                ]);

                // Create pricing
                $pricingData = [
                    'marketplace_item_id' => $item->id,
                    'pricing_type' => $itemData['pricing_type'],
                    'license_type' => MarketplacePricing::LICENSE_SINGLE,
                    'is_active' => true,
                ];

                if ($itemData['pricing_type'] === MarketplaceItem::PRICING_ONE_TIME) {
                    $pricingData['price'] = $itemData['price'];
                    $pricingData['original_price'] = rand(0, 1) ? $itemData['price'] * 1.25 : null;
                } elseif ($itemData['pricing_type'] === MarketplaceItem::PRICING_RECURRING) {
                    $pricingData['recurring_price'] = $itemData['recurring_price'];
                    $pricingData['billing_interval'] = MarketplacePricing::INTERVAL_MONTH;
                }

                MarketplacePricing::create($pricingData);

                // Create team/site pricing for some items
                if (rand(0, 1) && $itemData['pricing_type'] !== MarketplaceItem::PRICING_FREE) {
                    $teamPrice = ($itemData['price'] ?? $itemData['recurring_price'] ?? 10) * 3;
                    MarketplacePricing::create([
                        'marketplace_item_id' => $item->id,
                        'pricing_type' => $itemData['pricing_type'],
                        'price' => $itemData['pricing_type'] === MarketplaceItem::PRICING_ONE_TIME ? $teamPrice : null,
                        'recurring_price' => $itemData['pricing_type'] === MarketplaceItem::PRICING_RECURRING ? $teamPrice : null,
                        'billing_interval' => $itemData['pricing_type'] === MarketplaceItem::PRICING_RECURRING ? MarketplacePricing::INTERVAL_MONTH : null,
                        'license_type' => MarketplacePricing::LICENSE_TEAM,
                        'seat_limit' => 10,
                        'is_active' => true,
                    ]);
                }

                $items[] = $item;
            }
        }

        return $items;
    }

    private function createReviews(array $items, Organization $organization): void
    {
        $reviewTexts = [
            5 => [
                'Exactly what I was looking for! My learners love it.',
                'Incredible resource. Well worth the price.',
                'This has transformed my classroom. Highly recommend!',
                'Comprehensive and easy to implement. Five stars!',
                'My learners showed immediate improvement after using this.',
                'Perfect for differentiation. Works great for all learners.',
                'The quality is outstanding. Very professional materials.',
            ],
            4 => [
                'Great resource overall. Minor improvements could be made.',
                'Very helpful for my learners. Solid purchase.',
                'Good value for the price. Would recommend.',
                'Works well, though I made some modifications for my class.',
                'Nice addition to my teaching toolkit.',
            ],
            3 => [
                'Decent resource but not as comprehensive as I hoped.',
                "It's okay. Some parts were more useful than others.",
                'Average quality. Does the job but nothing special.',
            ],
        ];

        $sellerResponses = [
            "Thank you so much for your feedback! We're thrilled it's working well for your learners.",
            'We appreciate your review! Let us know if you have any questions.',
            'Thanks for the kind words! We love hearing success stories from educators.',
            null,
            null,
        ];

        $reviewers = User::where('org_id', $organization->id)->limit(10)->get();

        foreach ($items as $item) {
            $numReviews = rand(0, 5);

            for ($i = 0; $i < $numReviews; $i++) {
                $rating = $this->weightedRating();
                $texts = $reviewTexts[$rating] ?? $reviewTexts[4];
                $reviewer = $reviewers->random();

                MarketplaceReview::create([
                    'marketplace_item_id' => $item->id,
                    'user_id' => $reviewer->id,
                    'org_id' => $organization->id,
                    'rating' => $rating,
                    'review_text' => $texts[array_rand($texts)],
                    'status' => MarketplaceReview::STATUS_PUBLISHED,
                    'is_verified_purchase' => rand(0, 1),
                    'helpful_count' => rand(0, 30),
                    'seller_response' => $sellerResponses[array_rand($sellerResponses)],
                    'seller_responded_at' => rand(0, 1) ? now()->subDays(rand(1, 30)) : null,
                    'created_at' => now()->subDays(rand(1, 90)),
                ]);
            }

            // Update item ratings aggregate
            $item->updateRatingsAggregate();
        }
    }

    private function weightedRating(): int
    {
        $rand = rand(1, 100);
        if ($rand <= 60) {
            return 5;
        }
        if ($rand <= 85) {
            return 4;
        }
        if ($rand <= 95) {
            return 3;
        }

        return rand(1, 2);
    }

    private function getRandomExpertise(): array
    {
        $options = [
            ['SEL', 'Social Skills', 'Emotional Regulation'],
            ['Mathematics', 'STEM', 'Problem Solving'],
            ['Literacy', 'Reading', 'Writing'],
            ['Special Education', 'IEP', 'Differentiation'],
            ['Classroom Management', 'Behavior', 'PBIS'],
            ['Mental Health', 'Counseling', 'Wellness'],
        ];

        return $options[array_rand($options)];
    }

    private function getRandomCredentials(): array
    {
        $credentials = [
            [['title' => 'M.Ed.', 'issuer' => 'State University', 'year' => '2018']],
            [['title' => 'National Board Certified', 'issuer' => 'NBPTS', 'year' => '2020']],
            [['title' => 'B.A. Education', 'issuer' => 'State College', 'year' => '2015']],
            [
                ['title' => 'M.A. Special Education', 'issuer' => 'University', 'year' => '2017'],
                ['title' => 'Certified Behavior Analyst', 'issuer' => 'BACB', 'year' => '2019'],
            ],
        ];

        return $credentials[array_rand($credentials)];
    }
}
