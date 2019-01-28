<?php


namespace App\Udi\Nodes\Workspace;

use App\Udi\Nodes\BaseNode;

class ListSortNode extends BaseNode
{
    public function __construct($name = 'sort')
    {
        parent::__construct($name);
    }

    public function setField($fieldCode)
    {
        $field = $this->getNode('field');
        if ($field) {
            $field->setValue($fieldCode);
        }

        return $this;
    }

    public function getField()
    {
        $field = $this->getNode('field');

        return $field ? $field->getValue() : null;
    }

    public function setOrder($order)
    {
        $node = $this->getNode('order');
        if ($node) {
            $node->setValue($order);
        }

        return $this;
    }

    public function getOrder()
    {
        $order = $this->getNode('order');

        return $order ? $order->getValue() : null;
    }
}
