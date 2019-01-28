<?php


namespace App\Udi\Builders\Workspace;

use App\Udi\Builders\QueryBuilder;

class ListFilterBuilder
{
    const GLUE_FIELDS = ";";
    const GLUE_FIELD_VALUES = "::";
    const GLUE_VALUES = "|";

    const OP_GTE = 'GTE';
    const OP_LTE = 'LTE';
    const OP_BEW = 'BEW';
    const OP_EQUALS = 'EQUALS';
    const OP_NOT = 'NOT';
    const OP_GT = 'GT';
    const OP_LT = 'LT';
    const OP_BW = 'BW';
    const OP_EW = 'EW';

    private static $opTokens = [
        '>=' => self::OP_GTE,
        '<=' => self::OP_LTE,
        '^$' => self::OP_BEW,
        '=' => self::OP_EQUALS,
        '!' => self::OP_NOT,
        '>' => self::OP_GT,
        '<' => self::OP_LT,
        '^' => self::OP_BW,
        '$' => self::OP_EW
    ];

    private $query;
    private $filter = [];
    private $availableFields;

    public function __construct($strQuery, $availableFields = null)
    {
        $this->query = $strQuery;
        $this->availableFields = $availableFields;
        $this->parse();
    }

    protected function parse()
    {
        $fields = explode(self::GLUE_FIELDS, $this->query);
        foreach ($fields as $fieldValues) {
            if (!$fieldValues) {
                continue;
            }
            list($field, $values) = explode(self::GLUE_FIELD_VALUES, $fieldValues);
            if (!$this->isAvailable($field)) {
                continue;
            }
            $values = explode(self::GLUE_VALUES, $values);
            foreach ($values as $value) {
                $operator = self::$opTokens["="];
                foreach (static::$opTokens as $token => $op) {
                    if (mb_strpos($value, $token) === 0) {
                        $value = mb_substr($value, mb_strlen($token));
                        $operator = $op;
                        break;
                    }
                }

                $this->setValue($field, $value, $operator);
            }
        }
    }

    public function setValue($field, $value, $op = self::OP_EQUALS)
    {
        $exists = false;
        foreach ($this->filter as &$fieldValues) {
            if ($fieldValues['field'] == $field) {
                $this->addValue($field, $value, $op);
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $this->filter[] = [
                'field' => $field,
                'values' => [$op => $value]
            ];
        }
        unset($fieldValues);

        return $this;
    }

    public function addValue($field, $value, $op = self::OP_EQUALS)
    {
        if (!$this->isAvailable($field)) {
            return $this;
        }

        foreach ($this->filter as &$fieldValues) {
            if ($fieldValues['field'] == $field) {
                $this->_addValue($fieldValues['values'], $op, $value);
                break;
            }
        }
        unset($fieldValues);

        return $this;
    }

    private function _addValue(&$values, $op, $value)
    {
        if (isset($values[$op])) {
            if (!is_array($values[$op])) {
                $values[$op] = [$values[$op]];
            }
            $values[$op][] = $value;
        } else {
            $values[$op] = $value;
        }
    }

    public function isAvailable($field)
    {
        return (!is_array($this->availableFields) || in_array($field, $this->availableFields));
    }

    public function build(QueryBuilder $builder)
    {
        foreach ($this->filter as $fields) {
            $field = $fields['field'];
            foreach ($fields['values'] as $op => $value) {
                switch ($op) {
                    case self::OP_GTE: $op = '>='; break;
                    case self::OP_LTE: $op = '<='; break;
                    case self::OP_BEW:
                        $op  = 'LIKE';
                        $value = '%'.$value.'%';
                        break;
                    case self::OP_NOT: $op = '!='; break;
                    case self::OP_GT: $op = '>'; break;
                    case self::OP_LT: $op = '<'; break;
                    case self::OP_BW:
                        $op  = 'LIKE';
                        $value = $value.'%';
                        break;
                    case self::OP_EW:
                        $op = 'LIKE';
                        $value = '%'.$value;
                        break;
                    default: $op = '=';
                }
                $builder->where($field, $op, $value);
            }
        }

        return $builder;
    }

    public function getFilterData()
    {
        return $this->filter;
    }

    public function flush($field = null)
    {
        if (is_null($field)) {
            $this->filter = [];
        } else {
            foreach ($this->filter as $i => $filterData) {
                if ($filterData['field'] == $field) {
                    unset($this->filter[$i]);
                }
            }
        }

        return $this;
    }
}
