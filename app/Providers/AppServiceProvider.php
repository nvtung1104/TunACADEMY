<?php

namespace App\Providers;

use App\Models\Assignment;
use App\Models\Exam;
use App\Models\Lesson;
use App\Policies\AssignmentPolicy;
use App\Policies\ExamPolicy;
use App\Policies\LessonPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Lesson::class,    LessonPolicy::class);
        Gate::policy(Exam::class,      ExamPolicy::class);
        Gate::policy(Assignment::class, AssignmentPolicy::class);

        $this->warnMissingServiceKeys();
    }

    // Log a warning (not an exception) so missing keys are visible in logs
    // without crashing the app in local/test environments.
    private function warnMissingServiceKeys(): void
    {
        if (app()->isProduction()) {
            $required = ['GEMINI_API_KEY'];
            foreach ($required as $key) {
                if (empty(env($key))) {
                    Log::critical("Required service key [{$key}] is not set. AI features will be disabled.");
                }
            }
        }
    }
}
