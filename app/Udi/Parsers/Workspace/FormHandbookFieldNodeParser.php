<?php


namespace App\Udi\Parsers\Workspace;

use App\Udi\Nodes\BaseNode;
use App\Udi\Nodes\Workspace\FormHandbookFieldNode;

class FormHandbookFieldNodeParser extends FormFieldNodeParser
{
    public function __construct()
    {
        $node = new FormHandbookFieldNode();

        parent::__construct($node);
    }

    public function parse($data): BaseNode
    {
        $handbook = parent::parse($data);

        return $handbook;
    }
}
