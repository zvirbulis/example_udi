<?php


namespace App\Udi\Nodes\Workspace;

use App\Udi\Nodes\BaseNode;

class ListFieldsNode extends BaseNode
{
    public $models = [];
    public $matching = [];

    public function isSortable($fieldCode)
    {
        if ($field = $this->getField($fieldCode)) {
            return $field->isSortable();
        }

        return false;
    }

    public function getPrimary()
    {
        foreach ($this->getFields() as $field) {
            if ($field->isPrimary()) {
                return $field;
            }
        }

        return false;
    }

    public function getField($fieldCode)
    {
        foreach ($this->getFields() as $field) {
            if ($field->getCodeValue() == $fieldCode) {
                return $field;
            }
        }

        return false;
    }

    public function getFieldCodes()
    {
        $codes = array_map(function (ListFieldNode $field) {
            return $field->getCodeValue();
        }, $this->getFields());

        return $codes;
    }

    /**
     * @return ListFieldNode[]
     */
    public function getFields()
    {
        /**
         * @var ListFieldNode[] $fields
         */
        $fields = $this->getNodes();

        return $fields;
    }
}
