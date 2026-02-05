<x-layouts.app title="Pulse Demo Access">
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
        .form-block {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px;
            background: #fff;
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
    </style>

    <div class="landing-body min-h-screen bg-gradient-to-b from-white via-[#f7f8fb] to-white" x-data="{ open: false }" x-init="if (window.location.hash === '#demo-access') { open = true }" @open-demo-access.window="open = true">
        <section class="mx-auto max-w-6xl px-6 py-10" id="demo-access">
            <div class="inline-flex items-center gap-2 rounded-full hero-pill px-4 py-1 text-xs font-semibold">
                Pulse is currently in limited beta
                <span class="h-1 w-1 rounded-full bg-amber-400"></span>
                Accepting 20 participants
            </div>
            <div class="mt-5 grid grid-cols-1 lg:grid-cols-[1.05fr_0.95fr] gap-8 items-start">
                <div>
                    <h1 class="text-3xl md:text-4xl text-[var(--pulse-ink)] leading-tight font-semibold">
                        Less Paperwork, More Time with Students
                    </h1>
                    <p class="mt-4 text-base text-[var(--pulse-muted)]">
                        We built Pulse because we kept hearing the same thing from educators: “I became a teacher to teach — not to fill out endless reports.”
                    </p>
                    <div class="mt-5 space-y-4">
                        <div class="pulse-outline rounded-2xl p-4">
                            <h3 class="text-lg font-semibold text-[var(--pulse-ink)]">What if your admin tools actually gave you time back?</h3>
                            <p class="mt-2 text-sm text-[var(--pulse-muted)]">
                                We’re building something different — a system that handles the busywork so you can focus on the students and staff who need you most. But here’s the thing: we need your help to get it right.
                            </p>
                        </div>
                        <div class="pulse-outline rounded-2xl p-4">
                            <h3 class="text-lg font-semibold text-[var(--pulse-ink)]">Jump In and Tell Us What You Think</h3>
                            <p class="mt-2 text-sm text-[var(--pulse-muted)]">
                                We have a working prototype. It’s not perfect — it’s a starting point. And we want your voice shaping what comes next.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h2 class="text-xl font-semibold text-[var(--pulse-ink)]">Try it for 10 minutes. Tell us:</h2>
                        <ul class="mt-3 space-y-1 text-sm text-[var(--pulse-muted)]">
                            <li>• What feels right?</li>
                            <li>• What’s missing?</li>
                            <li>• What would actually help you on Monday morning?</li>
                        </ul>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <button @click="open = true" class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-[var(--pulse-accent)] text-white font-semibold hover:opacity-90">Get Started</button>
                            <button @click="open = true" class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50">Give Us Feedback</button>
                        </div>
                        <p class="mt-4 text-sm text-[var(--pulse-muted)]">
                            Based on your feedback, we’ll invite some schools to help us test new features as we build them — but for now, we just want to hear what you think.
                        </p>
                        <p class="mt-3 text-sm font-semibold text-[var(--pulse-ink)]">No credit card. No sales pitch. Just click, explore, and share your thoughts.</p>
                    </div>
                </div>

                <div id="get-access" class="sticky top-6">
                    <div class="pulse-outline rounded-3xl p-6">
                        <h2 class="text-2xl font-semibold text-[var(--pulse-ink)]">Prototype Preview</h2>
                        <p class="text-sm text-[var(--pulse-muted)] mt-2">
                            Drop a GIF or short looped video here. I can wire real screenshots once you share them.
                        </p>
                        <div class="mt-4 aspect-[9/16] w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 flex items-center justify-center text-sm text-slate-500">
                            Add GIF / screenshots here
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-black/40" @click="open = false"></div>
            <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">Get Access</h2>
                    <button class="text-gray-400 hover:text-gray-600" @click="open = false">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('demo.access') }}" class="space-y-4">
                    @csrf
                    <div class="form-block">
                        <label>First name</label>
                        <input name="first_name" required class="form-input" />
                    </div>
                    <div class="form-block">
                        <label>Last name</label>
                        <input name="last_name" required class="form-input" />
                    </div>
                    <div class="form-block">
                        <label>Email</label>
                        <input type="email" name="email" required class="form-input" />
                    </div>
                    <div class="form-block">
                        <label>Phone</label>
                        <input name="phone" class="form-input" />
                    </div>
                    <div class="form-block">
                        <label>Organization name</label>
                        <input name="org_name" class="form-input" />
                    </div>
                    <div class="form-block">
                        <label>Organization size</label>
                        <input name="org_size" class="form-input" />
                    </div>
                    <button
                        type="submit"
                        class="w-full rounded-xl bg-[var(--pulse-accent)] text-white py-3 font-semibold tracking-wide hover:opacity-90"
                    >
                        Get going
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
