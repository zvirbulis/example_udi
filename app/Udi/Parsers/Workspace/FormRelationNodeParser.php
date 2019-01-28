<?php


namespace App\Udi\Parsers\Workspace;

use App\Udi\Nodes\BaseNode;
use App\Udi\Nodes\Workspace\FormRelationNode;
use App\Udi\Parsers\BaseNodeParser;

class FormRelationNodeParser extends BaseNodeParser
{
    public function __construct(BaseNode $node = null)
    {
        $node = $node ?? new FormRelationNode();
        parent::__construct($node);
    }
}
