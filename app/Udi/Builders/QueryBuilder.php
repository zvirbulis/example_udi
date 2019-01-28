<?php


namespace App\Udi\Builders;

use App\Udi\Exceptions\UdiException;
use App\Udi\Exceptions\UdiNotFoundException;
use App\Udi\IoC;
use App\Udi\Support\PivotModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class QueryBuilder
 * @package App\Udi\Builders
 * TODO разнести статические методы для выполнения операций с БД и собсвенно сам QueryBuilder
 */
class QueryBuilder
{
    const MULTIPLE_VALUES_SEPARATOR = "::";

    protected $model;
    protected $table;
    protected $select = [];
    protected $where = [];
    protected $order = [];
    protected $groupConcat = true;
    protected $offset;
    protected $limit;
    protected $values = [];
    protected $primaryKey = false;
    protected $primaryValue = false;

    private static $models = [];
    private static $fields = [];

    /**
     * QueryBuilder constructor.
     * @param null $model
     * @throws UdiException
     */
    public function __construct($model = null)
    {
        if (!is_null($model)) {
            $this->model($model);
        }
    }

    /**
     * @param $field
     * @return mixed
     * @throws UdiException
     */
    public static function modelInfoByField($field)
    {
        $fieldInfo = QueryBuilder::fieldInfo($field);

        return $fieldInfo['modelInfo'];
    }

    /**
     * @param $field
     * @return mixed
     * @throws UdiException
     */
    public static function fieldInfo($field)
    {
        if (!isset(static::$fields[$field])) {
            $relatives = [];
            $models = explode('.', $field);
            $attribute = array_pop($models);
            foreach (array_reverse($models) as $i => $model) {
                $model = static::parseModel($model);
                $modelInfo = static::modelInfo($model['code']);
                if (!$model['foreignKey']) {
                    $model['foreignKey'] = $modelInfo['foreignKey'];
                } else {
                    $modelInfo['foreignKey'] = $model['foreignKey'];
                }
                if (($i - 1) >= 0) {
                    $modelInfo['foreignKey'] = $model['foreignKey'];
                    $relatives[$model['code']] = $modelInfo;
                } else {
                    $fieldInfo = [
                        'model' => $model['code'],
                        'modelInfo' => $modelInfo,
                        'attribute' => $attribute,
                        'sql' => $attribute,
                        'sqlAlias' => $field
                    ];
                    $fieldInfo['sqlFull'] = $fieldInfo['modelInfo']['table'].'.'.$fieldInfo['sql'];
                    $fieldInfo['sqlFullAlias'] = $fieldInfo['sqlFull'].' AS '.$fieldInfo['sqlAlias'];
                }
            }
            $fieldInfo['relatives'] = $relatives;
            static::$fields[$field] = $fieldInfo;
        }

        return static::$fields[$field];
    }

    private static function parseModel($model)
    {
        $foreignKey = null;
        if (strpos($model, ':') !== false) {
            list($code, $foreignKey) = explode(':', $model);
        } else {
            $code = $model;
        }

        return compact('code', 'foreignKey');
    }

    /**
     * @param $modelCode
     * @return mixed
     * @throws UdiException
     */
    public static function modelInfo($modelCode)
    {
        if (!isset(static::$models[$modelCode])) {
            $model = IoC::model($modelCode);
            static::$models[$modelCode] = [
                'model' => get_class($model),
                'table' => $model->getTable(),
                'pivot' => $model instanceof PivotModel,
                'primaryKey' => $model->getKeyName(),
                'foreignKey' => $model->getForeignKey()
            ];
        }

        return static::$models[$modelCode];
    }

    public function getModel()
    {
        return $this->model;
    }

    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param $field
     * @param bool $isPrimary
     * @return QueryBuilder
     * @throws UdiException
     */
    public function initModel($field, $isPrimary = true)
    {
        $fieldInfo = static::fieldInfo($field);
        if ($isPrimary) {
            $this->primaryKey = $field;
        }

        return $this->model($fieldInfo['model']);
    }

    /**
     * @param $model
     * @return $this
     * @throws UdiException
     */
    public function model($model)
    {
        $this->model = $model;
        $this->table = static::modelInfo($model)['table'];

        return $this;
    }

    public function primaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    public function primaryValue($primaryValue)
    {
        $this->primaryValue = $primaryValue;

        return $this;
    }

    /**
     * @param $field
     * @param $primaryValue
     * @return QueryBuilder
     * @throws UdiException
     */
    public function attachByField($field, $primaryValue)
    {
        $fieldInfo = static::fieldInfo($field);

        return $this->attach($fieldInfo['model'], $primaryValue, $fieldInfo['modelInfo']['foreignKey']);
    }

    /**
     * @param $model
     * @param $primaryValue
     * @param null $foreignKey
     * @return $this
     * @throws UdiException
     */
    public function attach($model, $primaryValue, $foreignKey = null)
    {
        $modelInfo = static::modelInfo($model);
        $foreignKey = $foreignKey ?? $modelInfo['foreignKey'];
        $this->addValue($this->model.'.'.$foreignKey, $primaryValue);

        return $this;
    }

    public function values(array $values)
    {
        foreach ($values as $field => $value) {
            $this->addValue($field, $value);
        }

        return $this;
    }

    public function addValue($field, $value)
    {
        $this->values[$field] = $value;
        if ($this->primaryKey && $field === $this->primaryKey) {
            $this->primaryValue = $value;
        }

        return $this;
    }

    public function select(array $fields)
    {
        foreach ($fields as $field) {
            $this->addSelect($field);
        }

        return $this;
    }

    public function addSelect($field)
    {
        if ($field && !isset($this->select[$field])) {
            $this->select[$field] = $field;
        }

        return $this;
    }

    public function order(array $fields = [])
    {
        if(empty($order)){
            $this->order = [];
        }
        else{
            foreach ($fields as $field => $order) {
                $this->orderBy($field, $order);
            }
        }

        return $this;
    }

    public function orderBy($field, $order = 'asc')
    {
        $order = strtolower($order);
        $this->order[$field] = ($order == 'desc' ? 'desc' : 'asc');

        return $this;
    }

    public function where($where, $op = null, $value = null)
    {
        if (!is_array($where)) {
            $where = [[$where, $op, $value]];
        }
        foreach ($where as $arr) {
            list($field, $op, $value) = $arr;
            //$this->addSelect($field);
            $this->where[$field][$op] = $value;
        }

        return $this;
    }

    public function groupConcat($mode = true)
    {
        $this->groupConcat = $mode;

        return $this;
    }

    public function page($page, $perPage)
    {
        $page = (int)$page;
        $perPage = (int)$perPage;
        if ($page > 0 && $perPage > 0) {
            return $this->offset(($page - 1) * $perPage)->limit($perPage);
        }

        return $this;
    }

    public function offset($offset)
    {
        $this->offset = (int)$offset;

        return $this;
    }

    public function limit($limit)
    {
        $limit = (int)$limit ? (int)$limit : 1;
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     * @throws UdiException
     */
    public function get()
    {
        $query = $this->build();
        if ($this->limit) {
            $offset = $this->offset ?? 0;
            $page = $offset / $this->limit + 1;
            return $query->paginate($this->limit, ['*'], 'page', $page);
        }

        return $query->get();
    }

    /**
     * @return Builder
     * @throws UdiException
     */
    public function build()
    {
        if (!$this->model) {
            throw new UdiException('Модель не визначена для QueryBuilder');
        }

        $modelInfo = self::modelInfo($this->model);
        $query = $modelInfo['model']::query();
        $this
            ->buildSelect($query)
            ->buildWhere($query)
            ->buildOrder($query)
            ->buildOffset($query)
            ->buildLimit($query)
        ;

        return $query;
    }

    /**
     * @param $condition
     * @param callable $action
     * @return $this
     */
    public function condition($condition, callable $action)
    {
        if ($condition) {
            call_user_func($action, $this);
        }

        return $this;
    }

    /**
     * @param Builder $query
     * @return $this
     * @throws UdiException
     */
    protected function buildSelect($query)
    {
        $joins = [];
        $select = [];
        $groups = [];
        foreach ($this->select as $field) {
            $fieldInfo = static::fieldInfo($field);
            $modelInfo = $fieldInfo['modelInfo'];
            $select[$fieldInfo['sqlFull']] = $fieldInfo['sqlFullAlias'];
            foreach ($fieldInfo['relatives'] as $relative) {
                //TODO тут какой-то overhead, посмотреть на досуге и отрефакторить
                if ($modelInfo['pivot']) {
                    $primaryTable = $relative['table'];
                    $foreignTable = $modelInfo['table'];
                    $primaryKey = $relative['primaryKey'];
                    $foreignKey = $relative['foreignKey'];
                    $joins[] = [$primaryTable, $primaryKey, $foreignTable, $foreignKey];
                    if ($primaryTable == $this->table) {
                        $joins[] = [$modelInfo['table'], $foreignKey, $relative['table'], $primaryKey];
                        if ($this->groupConcat && !isset($this->where[$field])) {
                            $groups[$fieldInfo['sqlFull']] = $primaryTable.'.'.$primaryKey;
                            $query->selectRaw('group_concat('.$fieldInfo['sqlFull'].' SEPARATOR "'.self::MULTIPLE_VALUES_SEPARATOR.'") AS `'.$fieldInfo['sqlAlias'].'`');
                        }
                    }
                    unset($select[$fieldInfo['sqlFullAlias']]);
                } elseif ($relative['pivot']) {
                    $primaryTable = $modelInfo['table'];
                    $foreignTable = $relative['table'];
                    $primaryKey = $modelInfo['primaryKey'];
                    $foreignKey = $modelInfo['foreignKey'];
                    $joins[] = [$primaryTable, $primaryKey, $foreignTable, $foreignKey];
                    if ($primaryTable == $this->table) {
                        $joins[] = [$relative['table'], $foreignKey, $primaryTable, $primaryKey];
                        if ($this->groupConcat && !isset($this->where[$field])) {
                            $groups[$fieldInfo['sqlFull']] = $primaryTable.'.'.$primaryKey;
                            $query->selectRaw('group_concat('.$fieldInfo['sqlFull'].' SEPARATOR "'.self::MULTIPLE_VALUES_SEPARATOR.'") AS `'.$fieldInfo['sqlAlias'].'`');
                        }
                    }
                } else {
                    $primaryTable = $modelInfo['table'];
                    $foreignTable = $relative['table'];
                    $primaryKey = $modelInfo['primaryKey'];
                    $foreignKey = $modelInfo['foreignKey'];
                    $joins[] = [$primaryTable, $primaryKey, $foreignTable, $foreignKey];
                }
                $modelInfo = $relative;
            }
        }
        foreach ($select as $key => $value) {
            if (isset($groups[$key])) {
                continue;
            }
            $query->addSelect($value);
        }
        if (!empty($groups)) {
            $query->groupBy($groups);
        }
        //Важен порядок джойнов поэтому их обрабатываем отдельно
        $joins = $this->normalizeJoins($joins);
        foreach ($joins as $joinParams) {
            list($primaryTable, $primaryKey, $foreignTable, $foreignKey) = $joinParams;
            $query->leftJoin($primaryTable, $primaryTable.'.'.$primaryKey, $foreignTable.'.'.$foreignKey);
        }


        return $this;
    }

    private function normalizeJoins(array $joins)
    {
        $result = [];
        $tables = [$this->table => 0];
        foreach ($joins as $join) {
            list($primaryTable, $primaryKey, $foreignTable, $foreignKey) = $join;
            if (isset($tables[$primaryTable])) {
                continue;
            }

            if (isset($tables[$foreignTable])) {
                $i = $tables[$foreignTable];
                array_splice($result, $i, 0, [$join]);
            } else {
                $result[] = $join;
            }
            $tables[$primaryTable] = count($tables);
        }

        return $result;
    }

    /**
     * @param Builder $query
     * @return $this
     * @throws UdiException
     */
    protected function buildWhere($query)
    {
        foreach ($this->where as $field => $arr) {
            $fieldInfo = static::fieldInfo($field);
            foreach ($arr as $op => $value) {
                if (is_array($value)) {
                    $not = $op === '!=';
                    $query->whereIn($fieldInfo['sqlFull'], $value, 'and', $not);
                } else {
                    $query->where($fieldInfo['sqlFull'], $op, $value);
                }
            }
        }

        return $this;
    }

    /**
     * @param Builder $query
     * @return $this
     * @throws UdiException
     */
    protected function buildOrder($query)
    {
        foreach ($this->order as $field => $order) {
            $fieldInfo = static::fieldInfo($field);
            $query->orderBy($fieldInfo['sqlFull'], $order);
        }

        return $this;
    }

    protected function buildOffset($query)
    {
        if ($this->offset !== null) {
            $query->offset($this->offset);
        }

        return $this;
    }

    protected function buildLimit($query)
    {
        if ($this->limit !== null) {
            $query->limit($this->limit);
        }

        return $this;
    }

    /**
     * @return mixed
     * @throws UdiException
     */
    public function create()
    {
        list($models, $relatives) = $this->parseValues();

        foreach ($relatives as $parent => $children) {
            $model = $models[$parent];
            /**
             * @var Model $instance
             */
            $instance = new $model['model'];
            $instance->fill($model['values'])->save();
            $id = $model['pivot'] ? $this->primaryValue : $instance->getKey();
            if (!empty($children)) {
                foreach ($children as $child => $attribute) {
                    $childModel = $models[$child];
                    if ($childModel['pivot']) {
                        $this->syncPivot($childModel, $attribute, $id);
                    } else {
                        $childModel['values'][$attribute] = $id;
                        $childModel['model']::create($childModel['values']);
                    }
                }
            }
        }

        return $id;
    }

    /**
     * @return array
     * @throws UdiException
     */
    protected function parseValues()
    {
        $models = $relatives = [];
        foreach ($this->values as $fieldCode => $fieldValue) {
            $fieldInfo = static::fieldInfo($fieldCode);
            $model = $fieldInfo['model'];

            $models[$model]['model'] = $fieldInfo['modelInfo']['model'];
            $models[$model]['table'] = $fieldInfo['modelInfo']['table'];
            $models[$model]['foreignKey'] = $fieldInfo['modelInfo']['foreignKey'];
            $models[$model]['pivot'] = $fieldInfo['modelInfo']['pivot'];
            if (is_array($fieldValue) && !$fieldInfo['modelInfo']['pivot']) {
                $fieldValue = implode(',', $fieldValue);
            }
            $models[$model]['values'][$fieldInfo['attribute']] = $fieldValue;

            //вложенность больше 1 - не рассматриваем
            if (count($fieldInfo['relatives']) == 1) {
                $relative = key($fieldInfo['relatives']);
                $relatives[$relative][$model] = $fieldInfo['relatives'][$relative]['foreignKey'];
            } elseif (!isset($relatives[$model])) {
                $relatives[$model] = [];
            }
        }

        return [$models, $relatives];
    }

    /**
     * @param null $primaryValue
     * @return bool
     * @throws UdiException
     * @throws UdiNotFoundException
     * @throws \Exception
     * TODO разделить на 2 метода update и updatePivot
     */
    public function update($primaryValue = null)
    {
        $primaryValue = $primaryValue ?? $this->primaryValue;
        if (!$primaryValue) {
            throw new UdiException('Первинне значення не визначено для "'.$this->model.'" оновлення операції');
        }
        list($models, $relatives) = $this->parseValues();

        DB::beginTransaction();
        foreach ($relatives as $parent => $children) {
            $model = $models[$parent];
            if ($model['pivot']) {
                if (!$this->primaryKey) {
                    throw new UdiException('Первинний ключ не знайдено для оновлення основи таблиці ("'.$this->table.'")');
                }
                $fieldInfo = self::fieldInfo($this->primaryKey);
                $where = array_merge($model['values'], [$fieldInfo['attribute'] => $primaryValue]);

                $instance = $model['model']::where($where)->first();
            } else {
                $instance = $model['model']::find($primaryValue);
                ;
            }

            if (!$instance) {
                DB::rollBack();
                throw new UdiNotFoundException('Модель "'.$this->model.'" з id = '.$primaryValue.' не знайдено. Відміна операції');
            }
            /**
             * @var Model $instance
             */
            if ($model['pivot']) {
                $instance->where($where)->update($model['values']);
            } else {
                $instance->fill($model['values'])->save();
            }
            $primaryValue = $instance->getKey();
            if (!empty($children)) {
                foreach ($children as $child => $attribute) {
                    $childModel = $models[$child];
                    if ($childModel['pivot']) {
                        $this->syncPivot($childModel, $attribute, $primaryValue);
                    } else {
                        $childModel['values'][$attribute] = $primaryValue;
                        $childModel['model']::updateOrCreate($childModel['values']);
                    }
                }
            }
        }
        DB::commit();

        return $primaryValue;
    }

    private function syncPivot($modelInfo, $primaryKey, $primaryValue)
    {
        $query = DB::table($modelInfo['table']);
        $query->where($primaryKey, $primaryValue)->delete();
        $insert = [];
        foreach ($modelInfo['values'] as $field => $values) {
            if (!is_array($values)) {
                $values = $values ? [$values]: [];
            }
            foreach ($values as $value) {
                if (!$value || intval($value) < 0) {
                    continue;
                }
                $insert[] = [$primaryKey => $primaryValue, $field => $value];
            }
        }
        if (!empty($insert)) {
            $query->insert($insert);
        }
    }

    /**
     * @param null $primaryValue
     * @return bool|null
     * @throws UdiException
     * @throws UdiNotFoundException
     * @throws \Exception
     */
    public function delete($primaryValue = null)
    {
        $primaryValue = $primaryValue ?? $this->primaryValue;
        if (!$primaryValue) {
            throw new UdiException('Первинне значення не визначено для "'.$this->model.'" видалення');
        }
        $modelInfo = static::modelInfo($this->model);
        /**
         * @var \Illuminate\Database\Eloquent\Builder $query
         */
        $query = $modelInfo['model']::query();
        if ($modelInfo['pivot']) {
            if (!$this->primaryKey) {
                throw new UdiException('Первинний ключ не знайдено для оновлення таблиці ("'.$this->table.'")');
            }
            $this->where($this->primaryKey, '=', $primaryValue);
        } else {
            $query->find($primaryValue);
        }
        $this->buildWhere($query->getQuery());

        /**
         * @var Model $model
         */
        $model = $query->get()->first();
        if (!$model) {
            throw new UdiNotFoundException('Модель "'.$this->model.'" з id = '.$primaryValue.' не знайдено. Відміна операції');
        } elseif ($modelInfo['pivot']) {
            return DB::table($modelInfo['table'])->where($model->getAttributes())->delete();
        } else {
            return $model->delete();
        }
    }
}
