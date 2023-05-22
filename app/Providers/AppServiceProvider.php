<?php

namespace App\Providers;

use App\Support\ChangesDetector;
use App\Mappers\ArticleEntityMapper;
use Illuminate\Support\ServiceProvider;
use App\Repositories\ArticlesRepository;
use Illuminate\Contracts\Foundation\Application;
use App\Contracts\ChangesDetector as ChangesDetectorContract;
use App\Contracts\ArticlesRepository as ArticlesRepositoryContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ChangesDetectorContract::class, ChangesDetector::class);

        $this->app->bind(ArticlesRepositoryContract::class, function (Application $application) {
            return new ArticlesRepository(
                connection: $application->make('db.connection'),
                mapper: $application->make(ArticleEntityMapper::class),
                table: 'articles',
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
