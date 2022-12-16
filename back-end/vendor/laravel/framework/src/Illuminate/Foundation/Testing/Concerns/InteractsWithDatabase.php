<?php
namespace Illuminate\Foundation\Testing\Concerns;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\Constraints\HasInDatabase;
use PHPUnit\Framework\Constraint\LogicalNot as ReverseConstraint;
use Illuminate\Foundation\Testing\Constraints\SoftDeletedInDatabase;
trait InteractsWithDatabase
{
    protected function assertDatabaseHas($table, array $data, $connection = null)
    {
        $this->assertThat(
            $table, new HasInDatabase($this->getConnection($connection), $data)
        );
        return $this;
    }
    protected function assertDatabaseMissing($table, array $data, $connection = null)
    {
        $constraint = new ReverseConstraint(
            new HasInDatabase($this->getConnection($connection), $data)
        );
        $this->assertThat($table, $constraint);
        return $this;
    }
    protected function assertSoftDeleted($table, array $data = [], $connection = null)
    {
        if ($table instanceof Model) {
            return $this->assertSoftDeleted($table->getTable(), [$table->getKeyName() => $table->getKey()], $table->getConnectionName());
        }
        $this->assertThat(
            $table, new SoftDeletedInDatabase($this->getConnection($connection), $data)
        );
        return $this;
    }
    protected function getConnection($connection = null)
    {
        $database = $this->app->make('db');
        $connection = $connection ?: $database->getDefaultConnection();
        return $database->connection($connection);
    }
    public function seed($class = 'DatabaseSeeder')
    {
        foreach (Arr::wrap($class) as $class) {
            $this->artisan('db:seed', ['--class' => $class, '--no-interaction' => true]);
        }
        return $this;
    }
}
