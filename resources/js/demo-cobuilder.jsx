import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';

const QUESTIONS = [
    {
        key: 'aha_moment_feature',
        type: 'choice',
        prompt: 'What would make the biggest difference in your day if Pulse handled it automatically?',
        options: [
            'Dictation-based reporting from my phone',
            'Custom alerts to get students the help they need faster',
            'Integrated plans with suggested resources and next steps',
            'Automatic reporting for administration and compliance',
            'Other',
        ],
        allowOther: true,
    },
    {
        key: 'weekend_killer_task',
        type: 'long',
        prompt: 'Walk us through one specific task, situation, or moment that regularly drains your time or energy—and why it’s hard to solve with the tools you have now.',
    },
    {
        key: 'viral_referral_note',
        type: 'long',
        prompt: 'Who else in your school would immediately “get” the value of this—and what would they say if they saw it today?',
    },
    {
        key: 'district_buying_hurdle',
        type: 'choice',
        prompt: 'What is the single data point your administration would need to see to confidently say “yes” to Pulse?',
        options: [
            'Proof of compliance',
            'IEP or student growth metrics',
            'Hours reclaimed or workload reduction data',
            'Audit-ready reporting and documentation',
        ],
    },
    {
        key: 'beta_builder_intent',
        type: 'yesno',
        prompt: 'We’re hand-selecting 20 schools for direct beta access and early-adopter pricing. Do you want your school to be on that shortlist?',
    },
];

const defaultFormState = {
    aha_moment_feature: [],
    aha_moment_feature_other: '',
    weekend_killer_task: '',
    viral_referral_note: '',
    district_buying_hurdle: [],
    beta_builder_intent: '',
    email: '',
};

function calculatePulseScore(formState) {
    let score = 0;
    score += 15;
    if (formState.aha_moment_feature.some((item) => item !== 'Other')) {
        score += 20;
    }
    if (formState.district_buying_hurdle.length > 0) {
        score += 25;
    }
    if (formState.beta_builder_intent === 'Yes') {
        score += 40;
    }
    return Math.min(score, 100);
}

function CoBuilderSurvey() {
    const [open, setOpen] = useState(false);
    const [step, setStep] = useState(0);
    const [formState, setFormState] = useState(defaultFormState);
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState('');
    const totalSteps = QUESTIONS.length;
    const isWelcome = step === 0;
    const isThankYou = step === totalSteps + 1;

    useEffect(() => {
        const trigger = document.getElementById('open-feedback-survey');
        if (!trigger) {
            return undefined;
        }
        const handler = () => {
            setOpen(true);
            setStep(0);
            setFormState(defaultFormState);
            setError('');
        };
        trigger.addEventListener('click', handler);
        return () => trigger.removeEventListener('click', handler);
    }, []);

    // Auto-open feedback modal if URL has #feedback hash
    useEffect(() => {
        if (window.location.hash === '#feedback') {
            setOpen(true);
            setStep(0);
            setFormState(defaultFormState);
            setError('');
        }
    }, []);

    const progress = useMemo(() => {
        if (step === 0) return 0;
        if (step > totalSteps) return 100;
        return Math.round((step / totalSteps) * 100);
    }, [step, totalSteps]);

    const currentQuestion = QUESTIONS[step - 1] || null;

    const updateForm = (key, value) => {
        setFormState((prev) => ({ ...prev, [key]: value }));
    };

    const toggleMultiSelect = (key, option) => {
        setFormState((prev) => {
            const current = Array.isArray(prev[key]) ? prev[key] : [];
            if (current.includes(option)) {
                return { ...prev, [key]: current.filter((item) => item !== option) };
            }
            return { ...prev, [key]: [...current, option] };
        });
    };

    const goNext = () => {
        if (step < totalSteps) {
            setStep(step + 1);
            return;
        }
        if (step === totalSteps) {
            handleSubmit();
        }
    };

    const goBack = () => {
        if (step > 0) {
            setStep(step - 1);
        }
    };

    const handleSubmit = async () => {
        setError('');
        if (formState.beta_builder_intent === 'Yes' && !formState.email) {
            setError('Email is required to join the Beta Builders shortlist.');
            return;
        }
        if (!window?.location) {
            setError('Survey endpoint is not available.');
            return;
        }

        setSubmitting(true);
        const payload = {
            'Probability Score': calculatePulseScore(formState),
            Timestamp: new Date().toISOString(),
            Email: formState.email || '',
            'First Name': '',
            'Last Name': '',
            aha_moment_feature: formState.aha_moment_feature.join(', '),
            weekend_killer_task: formState.weekend_killer_task,
            viral_referral_note: formState.viral_referral_note,
            district_buying_hurdle: formState.district_buying_hurdle.join(', '),
            beta_builder_intent: formState.beta_builder_intent,
        };

        if (formState.aha_moment_feature.includes('Other') && formState.aha_moment_feature_other) {
            payload.aha_moment_feature = `${payload.aha_moment_feature}${payload.aha_moment_feature ? ', ' : ''}${formState.aha_moment_feature_other}`;
        }

        try {
            await fetch('/demo/feedback', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(payload),
            });
            setStep(totalSteps + 1);
        } catch (err) {
            setError(err.message || 'Unable to submit feedback.');
        } finally {
            setSubmitting(false);
        }
    };

    if (!open) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div className="absolute inset-0 bg-black/40" onClick={() => setOpen(false)}></div>
            <div className="relative bg-white w-full max-w-2xl rounded-2xl shadow-xl border border-gray-200 p-8">
                <div className="flex items-center justify-between mb-6">
                    <h2 className="text-xl font-semibold text-gray-900">Co-Builder Survey</h2>
                    <button className="text-gray-400 hover:text-gray-600" onClick={() => setOpen(false)}>
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div className="min-h-[300px]">
                    {isWelcome && (
                        <div className="space-y-4">
                            <h3 className="text-2xl font-semibold text-slate-900">
                                You just took the first step toward reclaiming your school day.
                            </h3>
                            <p className="text-base text-slate-600">
                                Your perspective is the blueprint we use to build.
                            </p>
                            <button
                                className="mt-6 inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-[var(--pulse-accent)] text-white font-semibold hover:opacity-90"
                                onClick={() => setStep(1)}
                            >
                                Let&apos;s build this together →
                            </button>
                        </div>
                    )}

                    {!isWelcome && !isThankYou && currentQuestion && (
                        <div className="space-y-4">
                            <h3 className="text-xl font-semibold text-slate-900">{currentQuestion.prompt}</h3>

                            {currentQuestion.type === 'choice' && (
                                <div className="grid gap-3">
                                    {currentQuestion.options.map((option) => (
                                        <button
                                            key={option}
                                            type="button"
                                            onClick={() => toggleMultiSelect(currentQuestion.key, option)}
                                            className={`w-full text-left border rounded-xl px-4 py-3 text-slate-700 font-medium hover:border-pulse-orange-300 ${
                                                formState[currentQuestion.key].includes(option)
                                                    ? 'border-pulse-orange-500 bg-orange-50'
                                                    : 'border-slate-200 bg-white'
                                            }`}
                                        >
                                            <span className="inline-flex items-center gap-2">
                                                <span className={`h-4 w-4 rounded border ${formState[currentQuestion.key].includes(option) ? 'bg-[var(--pulse-accent)] border-[var(--pulse-accent)]' : 'border-slate-300'}`}></span>
                                                {option}
                                            </span>
                                        </button>
                                    ))}
                                    {currentQuestion.allowOther && formState[currentQuestion.key].includes('Other') && (
                                        <input
                                            type="text"
                                            className="form-input"
                                            placeholder="Tell us what you had in mind"
                                            value={formState.aha_moment_feature_other}
                                            onChange={(event) => updateForm('aha_moment_feature_other', event.target.value)}
                                        />
                                    )}
                                </div>
                            )}

                            {currentQuestion.type === 'short' && (
                                <input
                                    type="text"
                                    className="form-input"
                                    placeholder="Type your answer"
                                    value={formState[currentQuestion.key] || ''}
                                    onChange={(event) => updateForm(currentQuestion.key, event.target.value)}
                                />
                            )}

                            {currentQuestion.type === 'long' && (
                                <textarea
                                    rows="5"
                                    className="form-input"
                                    placeholder="Share your thoughts"
                                    value={formState[currentQuestion.key] || ''}
                                    onChange={(event) => updateForm(currentQuestion.key, event.target.value)}
                                />
                            )}

                            {currentQuestion.type === 'yesno' && (
                                <div className="space-y-4">
                                    <div className="flex gap-3">
                                        {['Yes', 'No'].map((value) => (
                                            <button
                                                key={value}
                                                type="button"
                                                onClick={() => updateForm('beta_builder_intent', value)}
                                                className={`flex-1 border rounded-xl px-4 py-3 text-slate-700 font-medium hover:border-pulse-orange-300 ${
                                                    formState.beta_builder_intent === value
                                                        ? 'border-pulse-orange-500 bg-orange-50'
                                                        : 'border-slate-200 bg-white'
                                                }`}
                                            >
                                                {value}
                                            </button>
                                        ))}
                                    </div>
                                    {formState.beta_builder_intent === 'Yes' && (
                                        <div>
                                            <label className="block text-xs uppercase tracking-widest text-gray-500 mb-1">Email</label>
                                            <input
                                                type="email"
                                                className="form-input"
                                                placeholder="you@school.org"
                                                value={formState.email}
                                                onChange={(event) => updateForm('email', event.target.value)}
                                            />
                                        </div>
                                    )}
                                </div>
                            )}

                            {error && <p className="text-sm text-red-500">{error}</p>}

                            <div className="flex items-center justify-between pt-4">
                                <button className="text-slate-500 hover:text-slate-700" onClick={goBack}>
                                    Back
                                </button>
                                <button
                                    className="inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-[var(--pulse-accent)] text-white font-semibold hover:opacity-90"
                                    onClick={goNext}
                                    disabled={submitting}
                                >
                                    {step === totalSteps ? (submitting ? 'Submitting…' : 'Submit') : 'Next'}
                                </button>
                            </div>
                        </div>
                    )}

                    {isThankYou && (
                        <div className="space-y-4 text-center">
                            <h3 className="text-2xl font-semibold text-slate-900">We hear you.</h3>
                            <p className="text-base text-slate-600">
                                Your feedback is being reviewed by the team now. We’re working to build a system that advocates for you just as hard as you advocate for your students.
                            </p>
                            <button
                                className="mt-4 inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-[var(--pulse-accent)] text-white font-semibold hover:opacity-90"
                                onClick={() => setOpen(false)}
                            >
                                Close
                            </button>
                        </div>
                    )}
                </div>

                <div className="mt-6">
                    <div className="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                        <div className="h-full bg-[var(--pulse-accent)] transition-all" style={{ width: `${progress}%` }}></div>
                    </div>
                </div>
            </div>
        </div>
    );
}

const rootEl = document.getElementById('cobuilder-survey-root');
if (rootEl) {
    const root = createRoot(rootEl);
    root.render(<CoBuilderSurvey />);
}
