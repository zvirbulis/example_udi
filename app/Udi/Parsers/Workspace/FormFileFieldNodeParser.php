<?php


namespace App\Udi\Parsers\Workspace;

use App\Udi\Nodes\Workspace\FormFileFieldNode;

class FormFileFieldNodeParser extends FormFieldNodeParser
{
    public function __construct(FormFileFieldNode $node = null)
    {
        $node = $node ?? new FormFileFieldNode();
        parent::__construct($node);
    }
}
