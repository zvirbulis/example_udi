<?php


namespace App\Udi\Parsers\Workspace;

use App\Udi\Nodes\BaseNode;
use App\Udi\Nodes\Workspace\ListPaginationNode;
use App\Udi\Parsers\BaseNodeParser;

class ListPaginationNodeParser extends BaseNodeParser
{
    public function __construct($node = null)
    {
        $node = $node ?? new ListPaginationNode();

        parent::__construct($node);
    }

    public function parse($data): BaseNode
    {
        $pagination = parent::parse($data);

        $pagination->requireNode('page');
        $pagination->requireNode('per_page');
        $pagination->requireNode('total_pages');
        $pagination->requireNode('total_items');

        return $pagination;
    }
}
