<?php


namespace App\Udi\Parsers\Workspace;

use App\Udi\Nodes\Workspace\ListFieldNode;
use App\Udi\Parsers\BaseNodeParser;

class ListFieldNodeParser extends BaseNodeParser
{
    protected $parsers = [
        'code' => FieldCodeNodeParser::class
    ];

    public function __construct()
    {
        $node = new ListFieldNode();

        parent::__construct($node);
    }
}
