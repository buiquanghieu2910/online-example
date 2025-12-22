<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repository Interfaces
use App\Repositories\IExamRepository;
use App\Repositories\IQuestionRepository;
use App\Repositories\IUserRepository;
use App\Repositories\IUserExamRepository;

// Repository Implementations
use App\Repositories\Impl\ExamRepositoryImpl;
use App\Repositories\Impl\QuestionRepositoryImpl;
use App\Repositories\Impl\UserRepositoryImpl;
use App\Repositories\Impl\UserExamRepositoryImpl;

// Service Interfaces
use App\Services\IExamService;
use App\Services\IQuestionService;
use App\Services\IUserService;
use App\Services\IExamTakingService;
use App\Services\IDashboardService;

// Service Implementations
use App\Services\Impl\ExamServiceImpl;
use App\Services\Impl\QuestionServiceImpl;
use App\Services\Impl\UserServiceImpl;
use App\Services\Impl\ExamTakingServiceImpl;
use App\Services\Impl\DashboardServiceImpl;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Repositories
        $this->app->bind(IExamRepository::class, ExamRepositoryImpl::class);
        $this->app->bind(IQuestionRepository::class, QuestionRepositoryImpl::class);
        $this->app->bind(IUserRepository::class, UserRepositoryImpl::class);
        $this->app->bind(IUserExamRepository::class, UserExamRepositoryImpl::class);

        // Register Services
        $this->app->bind(IExamService::class, ExamServiceImpl::class);
        $this->app->bind(IQuestionService::class, QuestionServiceImpl::class);
        $this->app->bind(IUserService::class, UserServiceImpl::class);
        $this->app->bind(IExamTakingService::class, ExamTakingServiceImpl::class);
        $this->app->bind(IDashboardService::class, DashboardServiceImpl::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
