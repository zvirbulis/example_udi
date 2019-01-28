<?php


namespace App\Udi\Nodes\Workspace;

class ListFilterFieldNode extends FormFieldNode
{
    public function setValueValue($value)
    {
        if (is_array($value) && count($value) == 1) {
            $value = current($value);
        }
        $this->requireNode('value')->flush()->setValue($value);

        return $this;
    }

    public function getTypeFilter()
    {
        if ($filterType = $this->requireNode('type_filter')->getValue()) {
            return $filterType;
        }

        return $this->getTypeElement();
    }

    public function getTypeElement()
    {
        return $this->requireNode('type_element')->getValue();
    }
}
