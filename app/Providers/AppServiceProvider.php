<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\ScheduleRepositoryInterface;
use App\Repositories\ScheduleRepository;
use App\Services\ScheduleConflictService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register repositories
        $this->app->bind(
            \App\Repositories\Contracts\CourseRepositoryInterface::class,
            \App\Repositories\CourseRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\TimetableRepositoryInterface::class,
            \App\Repositories\TimetableRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\ProfessorRepositoryInterface::class,
            \App\Repositories\ProfessorRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\ImportantDatesRepositoryInterface::class,
            \App\Repositories\ImportantDatesRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\ScheduleRepositoryInterface::class,
            \App\Repositories\ScheduleRepository::class
        );

        // Register scrapers as singletons since they maintain state
        $this->app->singleton(
            \App\Services\Scraper\CourseCatalogScraper::class
        );

        $this->app->singleton(
            \App\Services\Scraper\TimetableScraper::class
        );

        $this->app->singleton(
            \App\Services\Scraper\RateMyProfessorScraper::class
        );

        $this->app->singleton(
            \App\Services\Scraper\ImportantDatesScraper::class
        );
        $this->app->singleton(ScheduleRepositoryInterface::class, ScheduleRepository::class);
        $this->app->singleton(ScheduleConflictService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
