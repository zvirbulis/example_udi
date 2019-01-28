<?php


namespace App\Udi\Parsers\Workspace;

use App\Udi\Nodes\BaseNode;
use App\Udi\Nodes\Workspace\ListCategoriesNode;
use App\Udi\Parsers\BaseNodeParser;

class ListCategoriesNodeParser extends BaseNodeParser
{
    public function __construct(BaseNode $node = null)
    {
        $node = $node ?? new ListCategoriesNode();
        parent::__construct($node);
    }
}
