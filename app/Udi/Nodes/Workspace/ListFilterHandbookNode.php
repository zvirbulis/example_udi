<?php


namespace App\Udi\Nodes\Workspace;

use App\Udi\Interfaces\HandbookFieldInterface;
use App\Udi\Nodes\Workspace\Traits\HandbookTrait;

class ListFilterHandbookNode extends ListFilterFieldNode implements HandbookFieldInterface
{
    use HandbookTrait;

    public function __construct($name = 'handbook')
    {
        parent::__construct($name);
    }
}
