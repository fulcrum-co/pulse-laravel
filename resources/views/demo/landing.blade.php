<x-layouts.app title="Pulse Demo Access">
    <script>
        if (new URLSearchParams(window.location.search).get('demo') === 'true') {
            window.location.href = '/demo/bypass';
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        :root {
            --pulse-accent: #f97316;
            --pulse-ink: #0f172a;
            --pulse-muted: #475569;
            --pulse-soft: #f8fafc;
        }
        .landing-body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        .pulse-outline {
            border: 1px solid #e2e8f0;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }
        .form-block label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
        }
        .form-input {
            width: 100%;
            margin-top: 6px;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #cbd5f5;
            background: #f8fafc;
            font-size: 14px;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--pulse-accent);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
            background: #fff;
        }
        .hero-pill {
            border: 1px solid #fdba74;
            background: #fff7ed;
            color: #9a3412;
        }
        .preview-rolling {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            box-shadow: inset 0 0 0 1px rgba(226, 232, 240, 0.6);
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            animation: previewCycle 16s infinite;
        }
        @keyframes previewCycle {
            0% { background-image: url('/demo-shots/1.png'); }
            11% { background-image: url('/demo-shots/2.png'); }
            22% { background-image: url('/demo-shots/3.png'); }
            33% { background-image: url('/demo-shots/4.png'); }
            44% { background-image: url('/demo-shots/5.png'); }
            55% { background-image: url('/demo-shots/6.png'); }
            66% { background-image: url('/demo-shots/7.png'); }
            77% { background-image: url('/demo-shots/8.png'); }
            88% { background-image: url('/demo-shots/9.png'); }
            100% { background-image: url('/demo-shots/1.png'); }
        }
    </style>

    <div
        class="landing-body min-h-screen bg-gradient-to-b from-white via-[#f7f8fb] to-white"
        x-data="{ openAccess: false }"
        x-init="if (window.location.hash === '#demo-access') { openAccess = true }"
        @open-demo-access.window="openAccess = true"
    >
        <section class="mx-auto max-w-6xl px-4 py-10" id="demo-access">
            <div class="inline-flex items-center gap-2 rounded-full hero-pill px-4 py-1 text-xs font-semibold">
                Pulse is currently in limited beta
                <span class="h-1 w-1 rounded-full bg-amber-400"></span>
                Accepting 20 participants
            </div>
            <div class="mt-5 grid grid-cols-1 lg:grid-cols-[0.75fr_1.25fr] gap-16 items-start">
                <div class="max-w-lg">
                    <h1 class="text-3xl md:text-4xl text-[var(--pulse-ink)] leading-tight font-semibold">
                        Less paperwork. More time with students.
                    </h1>
                    <p class="mt-4 text-sm text-[var(--pulse-muted)]">
                        We built Pulse to remove the administrative load from teachers and staff, helping them spend more time where they shine: <strong><em>with their students</em></strong>.
                    </p>
                    <div class="mt-6">
                        <h2 class="text-xl font-semibold text-[var(--pulse-ink)]">Help us shape it — take a 10‑minute test drive and tell us:</h2>
                        <ul class="mt-3 space-y-2 text-sm text-[var(--pulse-muted)] list-disc list-inside">
                            <li>What feels right?</li>
                            <li>What's missing?</li>
                            <li>What would actually help you on Monday morning?</li>
                        </ul>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <button @click="openAccess = true" class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-[var(--pulse-accent)] text-white font-semibold hover:opacity-90">Get Started</button>
                            <button id="open-feedback-survey" class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50">Give Us Feedback</button>
                        </div>
                        <p class="mt-3 text-xs text-gray-400">
                            No credit card. No sales pitch. Just click, explore, and share your thoughts.
                        </p>
                    </div>
                </div>

                <div id="get-access" class="sticky top-4 lg:ml-8">
                    <div class="pulse-outline rounded-3xl p-4">
                        <h2 class="text-2xl font-semibold text-[var(--pulse-ink)]">Prototype preview</h2>
                        <div class="mt-4 w-full preview-rolling" style="aspect-ratio: 2 / 1;"></div>
                    </div>
                </div>
            </div>
        </section>
        <div x-show="openAccess" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-black/40" @click="openAccess = false"></div>
            <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">Get Access</h2>
                    <button class="text-gray-400 hover:text-gray-600" @click="openAccess = false">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('demo.access') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs uppercase tracking-widest text-gray-500 mb-1">First name <span class="text-red-500">*</span></label>
                            <input name="first_name" required class="form-input" />
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-widest text-gray-500 mb-1">Last name <span class="text-red-500">*</span></label>
                            <input name="last_name" required class="form-input" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs uppercase tracking-widest text-gray-500 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" required class="form-input" />
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-widest text-gray-500 mb-1">Phone</label>
                            <input name="phone" class="form-input" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-widest text-gray-500 mb-1">Organization name</label>
                        <input name="org_name" class="form-input" />
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-widest text-gray-500 mb-1">Website</label>
                        <input name="org_url" class="form-input" />
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-widest text-gray-500 mb-1">Organization size</label>
                        <input name="org_size" class="form-input" />
                    </div>
                    <button
                        type="submit"
                        class="w-full rounded-xl bg-[var(--pulse-accent)] text-white py-3 font-semibold tracking-wide hover:opacity-90"
                    >
                        View Prototype
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div
        id="cobuilder-survey-root"
        class="landing-body"
    ></div>
</x-layouts.app>
