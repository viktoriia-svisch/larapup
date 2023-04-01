<?php
namespace Illuminate\Validation\Rules;
use Illuminate\Database\Eloquent\Model;
class Unique
{
    use DatabaseRule;
    protected $ignore;
    protected $idColumn = 'id';
    public function ignore($id, $idColumn = null)
    {
        if ($id instanceof Model) {
            return $this->ignoreModel($id, $idColumn);
        }
        $this->ignore = $id;
        $this->idColumn = $idColumn ?? 'id';
        return $this;
    }
    public function ignoreModel($model, $idColumn = null)
    {
        $this->idColumn = $idColumn ?? $model->getKeyName();
        $this->ignore = $model->{$this->idColumn};
        return $this;
    }
    public function __toString()
    {
        return rtrim(sprintf('unique:%s,%s,%s,%s,%s',
            $this->table,
            $this->column,
            $this->ignore ? '"'.$this->ignore.'"' : 'NULL',
            $this->idColumn,
            $this->formatWheres()
        ), ',');
    }
}
