<?php


namespace App\Udi\Nodes\Workspace;

use App\Udi\Nodes\BaseNode;

class ListFieldNode extends BaseNode
{
    public function __construct($name = 'field')
    {
        parent::__construct($name);
    }

    public function getNameValue()
    {
        $name = $this->getNode('name');

        return $name ? $name->getValue() : null;
    }

    public function getCodeValue()
    {
        $code = $this->getCode();

        return $code ? $code->getValue() : null;
    }

    public function getCode()
    {
        return $this->getNode('code');
    }

    public function isPrimary()
    {
        return $this->hasNode('primary_key') && $this->getNode('primary_key')->getValue() === true;
    }

    public function isSortable()
    {
        return $this->hasNode('sortable') && $this->getNode('sortable')->getValue() === true;
    }

    public function getFormat()
    {
        return $this->hasNode('format') ? $this->getNode('format')->getValue() : false;
    }
}
