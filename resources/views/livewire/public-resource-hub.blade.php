<div>
    {{-- Hero Section --}}
    <div class="text-center mb-10">
        <h1 class="text-3xl font-bold text-gray-900 mb-3">
            @if($orgName)
                {{ $orgName }} Resource Library
            @else
                Resource Library
            @endif
        </h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Explore our collection of resources and courses designed to support student success.
        </p>
    </div>

    {{-- Search Bar --}}
    <div class="max-w-2xl mx-auto mb-8">
        <div class="relative">
            <x-icon name="magnifying-glass" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search resources and courses..."
                class="w-full pl-12 pr-4 py-3 text-lg border border-gray-300 rounded-xl focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 shadow-sm"
            >
            @if($search)
                <button wire:click="$set('search', '')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <x-icon name="x-mark" class="w-5 h-5" />
                </button>
            @endif
        </div>
    </div>

    {{-- Category Tabs --}}
    <div class="flex items-center justify-center gap-2 mb-8">
        <button
            wire:click="$set('category', 'all')"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $category === 'all' ? 'bg-pulse-orange-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}"
        >
            All
            <span class="ml-1 text-xs opacity-75">({{ $counts['resources'] + $counts['courses'] }})</span>
        </button>
        <button
            wire:click="$set('category', 'resources')"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $category === 'resources' ? 'bg-pulse-orange-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}"
        >
            <x-icon name="document-text" class="w-4 h-4 inline-block mr-1" />
            Resources
            <span class="ml-1 text-xs opacity-75">({{ $counts['resources'] }})</span>
        </button>
        <button
            wire:click="$set('category', 'courses')"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $category === 'courses' ? 'bg-pulse-orange-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}"
        >
            <x-icon name="academic-cap" class="w-4 h-4 inline-block mr-1" />
            Courses
            <span class="ml-1 text-xs opacity-75">({{ $counts['courses'] }})</span>
        </button>
    </div>

    {{-- Access Status Banner --}}
    @if(!$isUnlocked && $freeViewsRemaining > 0)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8 text-center">
            <p class="text-sm text-blue-800">
                <x-icon name="eye" class="w-4 h-4 inline-block mr-1" />
                You have <strong>{{ $freeViewsRemaining }}</strong> free preview{{ $freeViewsRemaining !== 1 ? 's' : '' }} remaining.
                <button wire:click="$set('showLeadGate', true)" class="underline font-medium hover:text-blue-900">
                    Sign up for unlimited access
                </button>
            </p>
        </div>
    @elseif($isUnlocked)
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-8 text-center">
            <p class="text-sm text-green-800">
                <x-icon name="check-circle" class="w-4 h-4 inline-block mr-1" />
                Welcome back! You have full access to all resources.
            </p>
        </div>
    @endif

    {{-- Resources Section --}}
    @if($category === 'all' || $category === 'resources')
        @if($resources->count() > 0)
            <div class="mb-12">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <x-icon name="document-text" class="w-5 h-5 text-blue-500" />
                    Resources
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($resources as $resource)
                        <div
                            wire:click="viewResource({{ $resource->id }})"
                            class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg hover:border-pulse-orange-300 transition-all cursor-pointer"
                        >
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                    @php
                                        $icon = match($resource->resource_type) {
                                            'article' => 'document-text',
                                            'video' => 'play-circle',
                                            'worksheet' => 'clipboard-document-list',
                                            'activity' => 'puzzle-piece',
                                            'link' => 'link',
                                            default => 'document',
                                        };
                                    @endphp
                                    <x-icon name="{{ $icon }}" class="w-5 h-5 text-blue-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $resource->title }}</h3>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ ucfirst($resource->resource_type) }}</p>
                                    @if($resource->description)
                                        <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ $resource->description }}</p>
                                    @endif
                                </div>
                            </div>
                            @if($resource->estimated_duration_minutes)
                                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                                    <span class="text-xs text-gray-500">
                                        <x-icon name="clock" class="w-3 h-3 inline-block mr-1" />
                                        {{ $resource->estimated_duration_minutes }} min
                                    </span>
                                    @if(!empty($resource->target_grades))
                                        <span class="text-xs text-gray-500">
                                            Grades: {{ implode(', ', array_slice($resource->target_grades, 0, 3)) }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    {{-- Courses Section --}}
    @if($category === 'all' || $category === 'courses')
        @if($courses->count() > 0)
            <div class="mb-12">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <x-icon name="academic-cap" class="w-5 h-5 text-orange-500" />
                    Courses
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($courses as $course)
                        <div
                            wire:click="viewCourse({{ $course->id }})"
                            class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg hover:border-pulse-orange-300 transition-all cursor-pointer"
                        >
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0">
                                    <x-icon name="academic-cap" class="w-5 h-5 text-orange-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $course->title }}</h3>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ ucwords(str_replace('_', ' ', $course->course_type)) }}</p>
                                    @if($course->description)
                                        <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ $course->description }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                                <span class="text-xs text-gray-500">
                                    <x-icon name="list-bullet" class="w-3 h-3 inline-block mr-1" />
                                    {{ $course->steps_count }} steps
                                </span>
                                @if($course->estimated_duration_minutes)
                                    <span class="text-xs text-gray-500">
                                        <x-icon name="clock" class="w-3 h-3 inline-block mr-1" />
                                        {{ $course->estimated_duration_minutes }} min
                                    </span>
                                @endif
                            </div>
                            @if($course->visibility === 'gated')
                                <div class="mt-2">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-700">
                                        <x-icon name="lock-closed" class="w-3 h-3" />
                                        Email required
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    {{-- Empty State --}}
    @if($resources->isEmpty() && $courses->isEmpty())
        <div class="text-center py-16">
            <x-icon name="folder-open" class="w-16 h-16 text-gray-300 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">
                @if($isSearching)
                    No results found
                @else
                    No public resources available
                @endif
            </h3>
            <p class="text-gray-500">
                @if($isSearching)
                    Try adjusting your search terms.
                @else
                    Check back later for new content.
                @endif
            </p>
        </div>
    @endif

    {{-- Detail Modal --}}
    @if($selectedItem)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="detail-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                {{-- Backdrop --}}
                <div wire:click="closeDetail" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                {{-- Modal Panel --}}
                <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full max-h-[90vh] overflow-y-auto">
                    @if($selectedItem['type'] === 'resource')
                        {{-- Resource Detail --}}
                        <div class="bg-gradient-to-br from-blue-500 to-blue-600 px-6 py-8 text-white">
                            <div class="flex items-center gap-3 mb-4">
                                @php
                                    $icon = match($selectedItem['resource_type'] ?? 'document') {
                                        'article' => 'document-text',
                                        'video' => 'play-circle',
                                        'worksheet' => 'clipboard-document-list',
                                        'activity' => 'puzzle-piece',
                                        'link' => 'link',
                                        default => 'document',
                                    };
                                @endphp
                                <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                                    <x-icon name="{{ $icon }}" class="w-6 h-6" />
                                </div>
                                <div>
                                    <span class="text-sm opacity-75">{{ ucfirst($selectedItem['resource_type'] ?? 'Resource') }}</span>
                                    @if($selectedItem['duration'])
                                        <span class="text-sm opacity-75 ml-2">• {{ $selectedItem['duration'] }} min</span>
                                    @endif
                                </div>
                            </div>
                            <h3 class="text-2xl font-bold" id="detail-title">{{ $selectedItem['title'] }}</h3>
                        </div>
                        <div class="px-6 py-6">
                            <p class="text-gray-700 leading-relaxed">{{ $selectedItem['description'] }}</p>

                            @if($selectedItem['category'])
                                <div class="mt-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700">
                                        {{ ucfirst($selectedItem['category']) }}
                                    </span>
                                </div>
                            @endif

                            @if($selectedItem['url'])
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <a href="{{ $selectedItem['url'] }}" target="_blank" rel="noopener"
                                       class="inline-flex items-center gap-2 px-6 py-3 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 transition-colors">
                                        <x-icon name="arrow-top-right-on-square" class="w-5 h-5" />
                                        Open Resource
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- Course Detail --}}
                        <div class="bg-gradient-to-br from-orange-500 to-orange-600 px-6 py-8 text-white">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                                    <x-icon name="academic-cap" class="w-6 h-6" />
                                </div>
                                <div>
                                    <span class="text-sm opacity-75">{{ ucwords(str_replace('_', ' ', $selectedItem['course_type'] ?? 'Course')) }}</span>
                                    @if($selectedItem['duration'])
                                        <span class="text-sm opacity-75 ml-2">• {{ $selectedItem['duration'] }} min</span>
                                    @endif
                                </div>
                            </div>
                            <h3 class="text-2xl font-bold" id="detail-title">{{ $selectedItem['title'] }}</h3>
                        </div>
                        <div class="px-6 py-6">
                            <p class="text-gray-700 leading-relaxed">{{ $selectedItem['description'] }}</p>

                            @if(!empty($selectedItem['objectives']))
                                <div class="mt-6">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-3">What You'll Learn</h4>
                                    <ul class="space-y-2">
                                        @foreach($selectedItem['objectives'] as $objective)
                                            <li class="flex items-start gap-2 text-sm text-gray-600">
                                                <x-icon name="check-circle" class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
                                                {{ $objective }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(!empty($selectedItem['steps']))
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-3">Course Outline ({{ count($selectedItem['steps']) }} steps)</h4>
                                    <div class="space-y-2">
                                        @foreach($selectedItem['steps'] as $index => $step)
                                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                                <div class="w-8 h-8 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-sm font-semibold">
                                                    {{ $index + 1 }}
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium text-gray-900">{{ $step['title'] }}</p>
                                                    <p class="text-xs text-gray-500">{{ ucfirst($step['step_type']) }}{{ $step['duration'] ? ' • ' . $step['duration'] . ' min' : '' }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                                <p class="text-sm text-gray-500 mb-4">Contact us to get access to this course for your students.</p>
                                <button class="inline-flex items-center gap-2 px-6 py-3 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600 transition-colors">
                                    <x-icon name="envelope" class="w-5 h-5" />
                                    Request Access
                                </button>
                            </div>
                        </div>
                    @endif

                    <button
                        wire:click="closeDetail"
                        class="absolute top-4 right-4 text-white/80 hover:text-white"
                    >
                        <x-icon name="x-mark" class="w-6 h-6" />
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Lead Gate Modal --}}
    @if($showLeadGate)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                {{-- Backdrop --}}
                <div wire:click="closeLeadGate" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                {{-- Modal Panel --}}
                <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                    <div class="bg-gradient-to-br from-pulse-orange-500 to-pulse-orange-600 px-6 py-8 text-white text-center">
                        <x-icon name="sparkles" class="w-12 h-12 mx-auto mb-4 opacity-90" />
                        <h3 class="text-2xl font-bold mb-2">Unlock Full Access</h3>
                        <p class="opacity-90">Get unlimited access to our entire resource library</p>
                    </div>

                    <form wire:submit="submitLead" class="px-6 py-6 space-y-4">
                        <div>
                            <label for="lead-email" class="block text-sm font-medium text-gray-700 mb-1">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="email"
                                id="lead-email"
                                wire:model="leadEmail"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                placeholder="you@example.com"
                                required
                            >
                            @error('leadEmail')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="lead-name" class="block text-sm font-medium text-gray-700 mb-1">
                                Your Name
                            </label>
                            <input
                                type="text"
                                id="lead-name"
                                wire:model="leadName"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                placeholder="John Smith"
                            >
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="lead-org" class="block text-sm font-medium text-gray-700 mb-1">
                                    Organization
                                </label>
                                <input
                                    type="text"
                                    id="lead-org"
                                    wire:model="leadOrganization"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                    placeholder="School/District"
                                >
                            </div>
                            <div>
                                <label for="lead-role" class="block text-sm font-medium text-gray-700 mb-1">
                                    Role
                                </label>
                                <select
                                    id="lead-role"
                                    wire:model="leadRole"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                >
                                    <option value="">Select...</option>
                                    <option value="teacher">Teacher</option>
                                    <option value="counselor">Counselor</option>
                                    <option value="administrator">Administrator</option>
                                    <option value="parent">Parent/Guardian</option>
                                    <option value="student">Student</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="pt-4">
                            <button
                                type="submit"
                                class="w-full px-6 py-3 bg-pulse-orange-500 text-white font-semibold rounded-lg hover:bg-pulse-orange-600 focus:ring-2 focus:ring-offset-2 focus:ring-pulse-orange-500 transition-colors"
                            >
                                Get Free Access
                            </button>
                        </div>

                        <p class="text-xs text-gray-500 text-center">
                            By signing up, you agree to receive occasional updates about new resources.
                            You can unsubscribe at any time.
                        </p>

                        <div class="relative my-4">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-200"></div>
                            </div>
                            <div class="relative flex justify-center text-xs">
                                <span class="px-2 bg-white text-gray-500">or</span>
                            </div>
                        </div>

                        <a
                            href="{{ route('public.register.org', ['org' => $orgId]) }}"
                            class="block w-full text-center text-sm text-pulse-orange-600 hover:text-pulse-orange-700 font-medium"
                        >
                            Create a free account for full access
                        </a>
                    </form>

                    <button
                        wire:click="closeLeadGate"
                        class="absolute top-4 right-4 text-white/80 hover:text-white"
                    >
                        <x-icon name="x-mark" class="w-6 h-6" />
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
