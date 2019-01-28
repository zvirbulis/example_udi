<?php


namespace App\Udi\Parsers\Workspace;

use App\Udi\Nodes\BaseNode;
use App\Udi\Nodes\Workspace\FormRelationsNode;
use App\Udi\Parsers\BaseNodeParser;

class FormRelationsNodeParser extends BaseNodeParser
{
    protected $parsers = [
        '' => FormRelationNodeParser::class
    ];

    public function __construct(BaseNode $node = null)
    {
        $node = $node ?? new FormRelationsNode();
        parent::__construct($node);
    }
}
