<?php
namespace Illuminate\Foundation\Testing;
use Illuminate\Contracts\Console\Kernel;
trait DatabaseMigrations
{
    public function runDatabaseMigrations()
    {
        $this->artisan('migrate:fresh');
        $this->app[Kernel::class]->setArtisan(null);
        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:rollback');
            RefreshDatabaseState::$migrated = false;
        });
    }
}
