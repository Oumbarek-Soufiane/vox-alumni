<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
   public function register(): void
    {
        // فرض التشفير (SSL) باش يقبلنا TiDB
        config(['database.connections.mysql.options' => [
            \PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt',
            \PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ]]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
