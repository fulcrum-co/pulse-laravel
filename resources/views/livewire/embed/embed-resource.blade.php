<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 p-4">
    @if($notFound)
        <div class="flex items-center justify-center min-h-[400px]">
            <div class="text-center">
                <x-icon name="document" class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Resource Not Found</h2>
                <p class="text-gray-500">This resource is no longer available or has been made private.</p>
            </div>
        </div>
    @else
        <div class="max-w-2xl mx-auto">
            {{-- Resource Card --}}
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                {{-- Header --}}
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-6 text-white">
                    <div class="flex items-center gap-3 mb-3">
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
                        <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                            <x-icon name="{{ $icon }}" class="w-5 h-5" />
                        </div>
                        <div>
                            <span class="text-sm opacity-90">{{ ucfirst($resource->resource_type) }}</span>
                            @if($resource->estimated_duration_minutes)
                                <span class="text-sm opacity-75 ml-2">• {{ $resource->estimated_duration_minutes }} min</span>
                            @endif
                        </div>
                    </div>
                    <h1 class="text-2xl font-bold">{{ $resource->title }}</h1>
                </div>

                {{-- Content --}}
                <div class="p-6">
                    <p class="text-gray-700 leading-relaxed">{{ $resource->description }}</p>

                    @if($resource->category)
                        <div class="mt-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700">
                                {{ ucfirst($resource->category) }}
                            </span>
                        </div>
                    @endif

                    @if(!empty($resource->tags))
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach($resource->tags as $tag)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    @if($resource->url)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <a
                                href="{{ $resource->url }}"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-blue-500 text-white font-semibold rounded-xl hover:bg-blue-600 transition-colors"
                            >
                                <x-icon name="arrow-top-right-on-square" class="w-5 h-5" />
                                Open Resource
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">
                            Powered by Pulse
                        </span>
                        @if($resource->organization)
                            <span class="text-xs text-gray-500">
                                {{ $resource->organization->org_name }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
