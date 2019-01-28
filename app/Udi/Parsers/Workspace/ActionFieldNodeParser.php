<?php


namespace App\Udi\Parsers\Workspace;

use App\Udi\Nodes\BaseNode;
use App\Udi\Nodes\Workspace\ActionFieldNode;
use App\Udi\Parsers\BaseNodeParser;

class ActionFieldNodeParser extends BaseNodeParser
{
    public function __construct(BaseNode $node = null)
    {
        $node = new ActionFieldNode();

        parent::__construct($node);
    }
}
