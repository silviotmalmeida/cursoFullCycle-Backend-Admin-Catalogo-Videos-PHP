<?php

namespace App\Providers;

use App\Repositories\Eloquent\CastMemberEloquentRepository;
use App\Repositories\Eloquent\CategoryEloquentRepository;
use App\Repositories\Eloquent\GenreEloquentRepository;
use App\Repositories\Transactions\TransactionDb;
use Core\Domain\Repository\CastMemberRepositoryInterface;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\Domain\Repository\GenreRepositoryInterface;
use Core\UseCase\Interfaces\TransactionDbInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // devem ser registrados as interfaces a serem substituídas por classes concretas
        $this->app->singleton(CategoryRepositoryInterface::class, CategoryEloquentRepository::class);
        $this->app->singleton(GenreRepositoryInterface::class, GenreEloquentRepository::class);
        $this->app->singleton(CastMemberRepositoryInterface::class, CastMemberEloquentRepository::class);
        //// para transações no BD deve-se utilizar o método bind ao invés de singleton, pois devem ser criadas novas conexões a cada chamada
        $this->app->bind(TransactionDbInterface::class, TransactionDb::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
