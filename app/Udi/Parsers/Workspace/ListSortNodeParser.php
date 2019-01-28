<?php


namespace App\Udi\Parsers\Workspace;

use App\Udi\Nodes\BaseNode;
use App\Udi\Nodes\Workspace\ListSortNode;
use App\Udi\Parsers\BaseNodeParser;

class ListSortNodeParser extends BaseNodeParser
{
    public function __construct()
    {
        $node = new ListSortNode();

        parent::__construct($node);
    }

    public function parse($data): BaseNode
    {
        /**
         * @var ListSortNode $sort
         */
        $sort = parent::parse($data);

        if (!$sort->getField()) {
            $sort->addNode(new BaseNode('field'));
        }
        if (!$sort->getOrder()) {
            $sort->addNode(new BaseNode('order'));
        }

        return $sort;
    }
}
