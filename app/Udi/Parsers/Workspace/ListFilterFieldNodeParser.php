<?php


namespace App\Udi\Parsers\Workspace;

use App\Udi\Nodes\Workspace\ListFilterFieldNode;

class ListFilterFieldNodeParser extends FormFieldNodeParser
{
    public function __construct(ListFilterFieldNode $node = null)
    {
        $node = $node ?? new ListFilterFieldNode('field');

        parent::__construct($node);
    }
}
