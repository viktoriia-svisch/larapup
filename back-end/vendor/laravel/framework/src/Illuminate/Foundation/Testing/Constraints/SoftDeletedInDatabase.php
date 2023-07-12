<?php
namespace Illuminate\Foundation\Testing\Constraints;
use Illuminate\Database\Connection;
use PHPUnit\Framework\Constraint\Constraint;
class SoftDeletedInDatabase extends Constraint
{
    protected $show = 3;
    protected $database;
    protected $data;
    public function __construct(Connection $database, array $data)
    {
        $this->data = $data;
        $this->database = $database;
    }
    public function matches($table): bool
    {
        return $this->database->table($table)
                ->where($this->data)->whereNotNull('deleted_at')->count() > 0;
    }
    public function failureDescription($table): string
    {
        return sprintf(
            "any soft deleted row in the table [%s] matches the attributes %s.\n\n%s",
            $table, $this->toString(), $this->getAdditionalInfo($table)
        );
    }
    protected function getAdditionalInfo($table)
    {
        $results = $this->database->table($table)->get();
        if ($results->isEmpty()) {
            return 'The table is empty';
        }
        $description = 'Found: '.json_encode($results->take($this->show), JSON_PRETTY_PRINT);
        if ($results->count() > $this->show) {
            $description .= sprintf(' and %s others', $results->count() - $this->show);
        }
        return $description;
    }
    public function toString(): string
    {
        return json_encode($this->data);
    }
}
