<?php


namespace App\Udi\Parsers\Workspace;

use App\Udi\Nodes\BaseNode;
use App\Udi\Nodes\Workspace\ActionsNode;
use App\Udi\Parsers\BaseNodeParser;

class ActionsNodeParser extends BaseNodeParser
{
    protected $parsers = [
        '' => ActionFieldNodeParser::class
    ];

    public function __construct(BaseNode $node = null)
    {
        $node = $node ?? new ActionsNode();

        parent::__construct($node);
    }
}
