<?php


namespace App\Udi\Nodes\Workspace;

use App\Udi\Nodes\BaseNode;

class FormRelationsNode extends BaseNode
{
    public function __construct($name = 'relations')
    {
        parent::__construct($name);
    }

    public function getRelations()
    {
        return $this->getNodes();
    }
}
