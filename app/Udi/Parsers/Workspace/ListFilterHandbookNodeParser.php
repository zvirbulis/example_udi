<?php


namespace App\Udi\Parsers\Workspace;

use App\Udi\Nodes\Workspace\ListFilterHandbookNode;

class ListFilterHandbookNodeParser extends ListFilterFieldNodeParser
{
    public function __construct($node = null)
    {
        $node = $node ?? new ListFilterHandbookNode();

        parent::__construct($node);
    }
}
