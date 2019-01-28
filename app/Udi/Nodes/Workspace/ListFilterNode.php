<?php


namespace App\Udi\Nodes\Workspace;

use App\Udi\Builders\Workspace\ListFilterBuilder;
use App\Udi\Nodes\BaseNode;

class ListFilterNode extends BaseNode
{
    public function __construct($name = 'filter')
    {
        parent::__construct($name);
    }

    public function acceptBuilder(ListFilterBuilder $filterBuilder)
    {
        //заполняем схему значениями из билдера (полученные из реквеста)
        foreach ($filterBuilder->getFilterData() as $filterData) {
            if (!($field = $this->getField($filterData['field']))) {
                continue;
            }
            $values = [];
            foreach ($filterData['values'] as $token => $value) {
                $values[] = $value;
            }
            $field->setValueValue($values);
        }
        //очищаем значения из реквеста
        $filterBuilder->flush();
        //заполняем билдер значениями из схемы (выставленные пользователем, либо по умолчанию)
        foreach ($this->getFields() as $field) {
            $this->setFilterValue($field, $filterBuilder);
        }

        return $this;
    }

    private function setFilterValue(ListFilterFieldNode $field, ListFilterBuilder $filterBuilder)
    {
        $value = $field->getValueValue();
        if (empty($value)) {
            return;
        }
        $fieldCode = $field->getCodeValue();
        //тут будет настраиваться оператор фильтра взависимости от типа поля
        switch ($field->getTypeFilter()) {
            case "substring":
                $filterBuilder->setValue($fieldCode, $value, $filterBuilder::OP_BEW);
                break;
            case "range":
                list($v1, $v2) = $this->normalizeRangeValue($value);
                if ($v1) {
                    $filterBuilder->setValue($fieldCode, $v1, $filterBuilder::OP_GTE);
                }
                if ($v2) {
                    $filterBuilder->setValue($fieldCode, $v2, $filterBuilder::OP_LTE);
                }
                break;
            default:
                $filterBuilder->setValue($field->getCodeValue(), $value);
                break;
        }
    }

    private function normalizeRangeValue($value)
    {
        if (!is_array($value)) {
            $value = [$value, 0];
        } elseif (empty($value)) {
            $value = [0, 0];
        } elseif (count($value) == 1) {
            $value = [current($value), 0];
        }

        return $value;
    }

    /**
     * @param $code
     * @return ListFilterFieldNode
     */
    public function getField($code)
    {
        /**
         * @var ListFilterFieldNode $field
         */
        $field = $this->getNode('fields')->getNode($code);

        return $field;
    }

    /**
     * @return ListFilterFieldNode[]
     */
    public function getFields()
    {
        /**
         * @var ListFilterFieldNode[] $fields
         */
        $fields = $this->requireNode('fields')->getNodes();

        return $fields;
    }

    public function getFilterableFields()
    {
        return $this->getFieldsOrder()->toArray(true);
    }

    public function getFieldsOrder()
    {
        return $this->getNode('order');
    }
}
