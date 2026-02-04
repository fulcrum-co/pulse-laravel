<div>
    @if($show)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="share-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                {{-- Backdrop --}}
                <div wire:click="close" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                {{-- Modal Panel --}}
                <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full">
                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-purple-500 to-indigo-600 px-6 py-5 text-white">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                                <x-icon name="share" class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold" id="share-modal-title">Share {{ ucfirst($type) }}</h3>
                                <p class="text-sm opacity-90 truncate max-w-md">{{ $title }}</p>
                            </div>
                        </div>
                        <button wire:click="close" class="absolute top-4 right-4 text-white/80 hover:text-white">
                            <x-icon name="x-mark" class="w-6 h-6" />
                        </button>
                    </div>

                    <div class="px-6 py-6 space-y-6">
                        {{-- Public Status --}}
                        @if(!$isPublic)
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <x-icon name="exclamation-triangle" class="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5" />
                                    <div>
                                        <p class="text-sm font-medium text-yellow-800">This {{ $type }} is not public yet</p>
                                        <p class="text-sm text-yellow-700 mt-1">
                                            Make it public to allow others to access it via the share link or embed.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Direct Link --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <x-icon name="link" class="w-4 h-4 inline-block mr-1" />
                                Direct Link
                            </label>
                            <div class="flex gap-2">
                                <input
                                    type="text"
                                    value="{{ $publicUrl }}"
                                    readonly
                                    class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-700 font-mono"
                                    id="share-url-input"
                                >
                                <button
                                    onclick="navigator.clipboard.writeText('{{ $publicUrl }}').then(() => { this.innerHTML = '<span class=\'flex items-center gap-1\'><svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M5 13l4 4L19 7\'></path></svg> Copied!</span>'; setTimeout(() => { this.innerHTML = 'Copy'; }, 2000); })"
                                    class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors text-sm"
                                >
                                    Copy
                                </button>
                            </div>
                        </div>

                        {{-- Embed Code --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <x-icon name="code-bracket" class="w-4 h-4 inline-block mr-1" />
                                Embed Code
                            </label>
                            <p class="text-xs text-gray-500 mb-3">
                                Copy this code and paste it into your website's HTML to embed this {{ $type }}.
                            </p>

                            {{-- Size Controls --}}
                            <div class="flex gap-4 mb-3">
                                <div class="flex-1">
                                    <label class="block text-xs text-gray-500 mb-1">Width (px)</label>
                                    <input
                                        type="number"
                                        wire:model.live.debounce.300ms="embedWidth"
                                        min="300"
                                        max="1920"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                    >
                                </div>
                                <div class="flex-1">
                                    <label class="block text-xs text-gray-500 mb-1">Height (px)</label>
                                    <input
                                        type="number"
                                        wire:model.live.debounce.300ms="embedHeight"
                                        min="300"
                                        max="1200"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                    >
                                </div>
                                <div class="flex items-end">
                                    <button
                                        wire:click="$set('embedWidth', 800); $set('embedHeight', 600)"
                                        class="px-3 py-2 text-xs text-gray-600 hover:text-gray-900"
                                    >
                                        Reset
                                    </button>
                                </div>
                            </div>

                            <div class="relative">
                                <textarea
                                    readonly
                                    rows="4"
                                    class="w-full px-4 py-3 bg-gray-900 text-green-400 border border-gray-700 rounded-lg text-xs font-mono resize-none"
                                    id="embed-code-input"
                                >{{ $embedCode }}</textarea>
                                <button
                                    onclick="navigator.clipboard.writeText(document.getElementById('embed-code-input').value).then(() => { this.innerHTML = '<span class=\'flex items-center gap-1\'><svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M5 13l4 4L19 7\'></path></svg> Copied!</span>'; setTimeout(() => { this.innerHTML = 'Copy Code'; }, 2000); })"
                                    class="absolute top-2 right-2 px-3 py-1.5 bg-gray-700 hover:bg-gray-600 text-white text-xs font-medium rounded transition-colors"
                                >
                                    Copy Code
                                </button>
                            </div>
                        </div>

                        {{-- Preview --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <x-icon name="eye" class="w-4 h-4 inline-block mr-1" />
                                Preview
                            </label>
                            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 overflow-hidden">
                                <div class="aspect-video max-h-64 overflow-hidden rounded-lg shadow-sm">
                                    <iframe
                                        src="{{ $publicUrl }}"
                                        class="w-full h-full"
                                        frameborder="0"
                                    ></iframe>
                                </div>
                            </div>
                        </div>

                        {{-- Social Share --}}
                        <div class="pt-4 border-t border-gray-200">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Share on Social Media
                            </label>
                            <div class="flex gap-3">
                                <a
                                    href="https://twitter.com/intent/tweet?url={{ urlencode($publicUrl) }}&text={{ urlencode('Check out: ' . $title) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="flex items-center gap-2 px-4 py-2 bg-[#1DA1F2] text-white rounded-lg hover:bg-[#1a8cd8] transition-colors text-sm"
                                >
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                                    Twitter
                                </a>
                                <a
                                    href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($publicUrl) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="flex items-center gap-2 px-4 py-2 bg-[#0A66C2] text-white rounded-lg hover:bg-[#094d92] transition-colors text-sm"
                                >
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                    LinkedIn
                                </a>
                                <a
                                    href="mailto:?subject={{ urlencode($title) }}&body={{ urlencode('Check out this resource: ' . $publicUrl) }}"
                                    class="flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm"
                                >
                                    <x-icon name="envelope" class="w-4 h-4" />
                                    Email
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
