<?php


namespace App\Udi\Nodes\Workspace;

use App\Udi\Nodes\BaseNode;

class FormRelationNode extends BaseNode
{
    public function __construct($name = 'relation')
    {
        parent::__construct($name);
    }

    public function setCodeValue($value)
    {
        $this->getNode('code')->setValue($value);

        return $this;
    }

    public function getCodeValue()
    {
        return $this->getNode('code')->getValue();
    }

    public function getNameValue()
    {
        return $this->getNode('name')->getValue();
    }

    public function getLink()
    {
        return $this->getNode('_link')->getValue();
    }

    public function setLink($link)
    {
        $this->getNode('_link')->setValue($link);

        return $this;
    }
}
