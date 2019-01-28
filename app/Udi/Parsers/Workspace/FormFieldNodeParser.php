<?php


namespace App\Udi\Parsers\Workspace;

use App\Udi\Parsers\BaseNodeParser;
use App\Udi\Nodes\Workspace\FormFieldNode;

class FormFieldNodeParser extends BaseNodeParser
{
    public function __construct(FormFieldNode $node = null)
    {
        $node = $node ?? new FormFieldNode('field');

        parent::__construct($node);
    }
}
