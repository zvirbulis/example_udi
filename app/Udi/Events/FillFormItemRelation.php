<?php


namespace App\Udi\Events;

use App\Udi\Nodes\Workspace\FormRelationNode;

class FillFormItemRelation
{
    private $item;
    private $relation;

    public function __construct(FormRelationNode $relation, $item)
    {
        $this->relation = $relation;
        $this->item = $item;
    }

    public function getRelation()
    {
        return $this->relation;
    }

    public function getItem()
    {
        return $this->item;
    }
}
