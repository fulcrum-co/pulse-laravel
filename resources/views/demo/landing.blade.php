<x-layouts.app title="Pulse Demo Access">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&display=swap');
        :root {
            --pulse-accent: #f97316;
            --pulse-ink: #0f172a;
            --pulse-muted: #475569;
            --pulse-soft: #f8fafc;
        }
        .landing-body {
            font-family: 'Sora', system-ui, -apple-system, sans-serif;
        }
        .landing-serif {
            font-family: 'Source Serif 4', serif;
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
    </style>

    <div class="landing-body min-h-screen bg-gradient-to-b from-white via-[#f7f8fb] to-white">
        <header class="mx-auto max-w-6xl px-6 pt-8">
            <nav class="flex items-center justify-between">
                <div class="flex items-center gap-3 text-lg font-semibold text-[var(--pulse-ink)]">
                    <div class="h-10 w-10 rounded-full bg-[var(--pulse-accent)] text-white flex items-center justify-center">P</div>
                    Pulse Connect
                </div>
                <div class="flex items-center gap-3">
                    <a href="#get-access" class="text-sm font-medium text-[var(--pulse-muted)] hover:text-[var(--pulse-ink)]">Get Access</a>
                    <a href="/login" class="text-sm font-medium text-[var(--pulse-muted)] hover:text-[var(--pulse-ink)]">Admin Login</a>
                </div>
            </nav>
        </header>

        <section class="mx-auto max-w-6xl px-6 py-12">
            <div class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-4 py-1 text-xs font-semibold text-amber-800">
                Pulse is currently IN Limited beta
                <span class="h-1 w-1 rounded-full bg-amber-400"></span>
                Accepting 20 schools and consultants
            </div>
            <div class="mt-6 grid grid-cols-1 lg:grid-cols-[1.15fr_0.85fr] gap-10 items-start">
                <div>
                    <h1 class="landing-serif text-4xl md:text-5xl text-[var(--pulse-ink)] leading-tight">
                        Help Us Build the Future of Educator‑First Technology: The Pulse Prototype
                    </h1>
                    <p class="mt-5 text-lg text-[var(--pulse-muted)]">
                        We aren’t just building a tool; we’re building a community‑driven solution. Your voice is the missing piece.
                    </p>
                    <div class="mt-6 space-y-6">
                        <div class="pulse-outline rounded-2xl p-6">
                            <h3 class="text-xl font-semibold text-[var(--pulse-ink)]">Your Perspective Matters</h3>
                            <p class="mt-2 text-sm text-[var(--pulse-muted)]">
                                We know that the best technology isn't designed in a vacuum—it’s built in the hallways, classrooms, and administrative offices where the real work happens. We invite you to take a 3‑minute "test drive" of our current prototype and help us refine it into the platform your community truly deserves.
                            </p>
                        </div>
                        <div class="pulse-outline rounded-2xl p-6">
                            <h3 class="text-xl font-semibold text-[var(--pulse-ink)]">The Mission: Co‑Design for Impact</h3>
                            <p class="mt-2 text-sm text-[var(--pulse-muted)]">
                                Interactive experiences drive up to 60% deeper engagement than standard videos, but we want to go a step further. We want your initial impressions, your "wish lists," and your critical feedback to ensure Pulse Connect solves the high‑stakes structural crises you face every day.
                            </p>
                        </div>
                    </div>

                    <div class="mt-10">
                        <h2 class="text-2xl font-semibold text-[var(--pulse-ink)]">What to Explore in the Prototype</h2>
                        <div class="mt-4 space-y-4">
                            <div class="pulse-outline rounded-2xl p-5">
                                <h3 class="font-semibold text-[var(--pulse-ink)]">1. The "8‑Hour Promise": Is this enough?</h3>
                                <p class="text-sm text-[var(--pulse-muted)] mt-2">
                                    Test our automated digital roll‑calls and tardiness notifications. These tools are designed to reclaim an estimated 8+ staff hours per week. Tell us: Is this where you need time back most? What other repetitive tasks are stealing your focus?
                                </p>
                            </div>
                            <div class="pulse-outline rounded-2xl p-5">
                                <h3 class="font-semibold text-[var(--pulse-ink)]">2. The 30‑Second Audit: Does this create transparency?</h3>
                                <p class="text-sm text-[var(--pulse-muted)] mt-2">
                                    Try generating a comprehensive term report in under 30 seconds. We built this to solve the 30% time‑waste typically spent on manual administrative processes. Tell us: What metrics do your school boards and families actually care about? What is your current system missing?
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10">
                        <h2 class="text-2xl font-semibold text-[var(--pulse-ink)]">The "Pioneer" Feedback Framework</h2>
                        <p class="text-sm text-[var(--pulse-muted)] mt-2">
                            While you test the prototype, we invite you to use the "I Like, I Wish, What If" method to help us improve:
                        </p>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="pulse-outline rounded-2xl p-5">
                                <h3 class="font-semibold text-[var(--pulse-ink)]">I Like</h3>
                                <p class="text-sm text-[var(--pulse-muted)] mt-2">What resonated with you? What felt intuitive?</p>
                            </div>
                            <div class="pulse-outline rounded-2xl p-5">
                                <h3 class="font-semibold text-[var(--pulse-ink)]">I Wish</h3>
                                <p class="text-sm text-[var(--pulse-muted)] mt-2">What would you change to make this better for your specific workflow?</p>
                            </div>
                            <div class="pulse-outline rounded-2xl p-5">
                                <h3 class="font-semibold text-[var(--pulse-ink)]">What If</h3>
                                <p class="text-sm text-[var(--pulse-muted)] mt-2">What features would make your life significantly better if we added them tomorrow?</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 pulse-outline rounded-2xl p-6">
                        <h2 class="text-2xl font-semibold text-[var(--pulse-ink)]">Join the Beta Builders</h2>
                        <p class="text-sm text-[var(--pulse-muted)] mt-2">
                            Checking out the prototype is the first step toward joining our Limited Beta Cohort of 20 forward‑thinking schools. Participants in this group receive a significantly reduced early‑access rate and have a direct line to our development team to ensure the final product meets their exact needs.
                            <strong class="block mt-2 text-[var(--pulse-ink)]">No credit card. No sales pitch. Just your expertise helping us build something better.</strong>
                        </p>
                    </div>
                </div>

                <div id="get-access" class="sticky top-6">
                    <div class="pulse-outline rounded-3xl p-6">
                        <h2 class="text-2xl font-semibold text-[var(--pulse-ink)]">Get Access</h2>
                        <p class="text-sm text-[var(--pulse-muted)] mt-2">
                            Fill this out for immediate access to the view‑only prototype.
                        </p>
                        <form method="POST" action="{{ route('demo.access') }}" class="mt-6 space-y-4">
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
                                <label>Email address</label>
                                <input type="email" name="email" required class="form-input" />
                            </div>
                            <div class="form-block">
                                <label>Phone number</label>
                                <input name="phone" class="form-input" />
                            </div>
                            <div class="form-block">
                                <label>Organization name</label>
                                <input name="org_name" class="form-input" />
                            </div>
                            <div class="form-block">
                                <label>Organization size (all people served)</label>
                                <input name="org_size" class="form-input" />
                                <p class="mt-2 text-xs text-[var(--pulse-muted)]">
                                    Includes everyone in the organization’s scope—administrators, staff, participants, and families/guardians. If applicable, include after‑school volunteers/support.
                                </p>
                            </div>
                            <button
                                type="submit"
                                class="w-full rounded-xl bg-[var(--pulse-accent)] text-white py-3 font-semibold tracking-wide hover:opacity-90"
                            >
                                Get Access
                            </button>
                        </form>
                        <p class="mt-4 text-xs text-[var(--pulse-muted)]">
                            You’ll enter a view‑only prototype. No data changes are saved.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
