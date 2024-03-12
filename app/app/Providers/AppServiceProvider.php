<?php

namespace App\Providers;

use App\Repositories\Eloquent\CategoryEloquentRepository;
use App\Repositories\Transactions\TransactionDb;
use Core\Domain\Repository\CategoryRepositoryInterface;
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
