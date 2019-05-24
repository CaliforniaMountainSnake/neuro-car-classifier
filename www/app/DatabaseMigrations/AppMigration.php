<?php

namespace App\DatabaseMigrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Schema;

class AppMigration extends Migration
{
    public function getDatabaseConnection(): Builder
    {
        return Schema::connection('mysql_app');
    }

    public function getPdo(): \PDO
    {
        return $this->getDatabaseConnection()->getConnection()->getPdo();
    }
}
