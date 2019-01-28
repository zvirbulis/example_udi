<?php


namespace App\Udi\Nodes\Workspace;

use App\Udi\Nodes\BaseNode;

class ListCategoriesNode extends BaseNode
{
    public function __construct($name = 'categories')
    {
        parent::__construct($name);
    }

    public function getLevelsCount()
    {
        return count($this->getLevels());
    }

    public function getLevels()
    {
        $levels = $this->requireNode('levels');
        if (!$levels->getValue()) {
            $levels->setValue([]);
        }

        return $levels->toArray(true);
    }

    public function setItems(array $items)
    {
        $this->requireNode('items')->flush()->setValue($items);

        return $this;
    }
}
