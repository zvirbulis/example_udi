<?php


namespace App\Udi\Nodes\Workspace;

use App\Udi\Nodes\BaseNode;

class ListPaginationNode extends BaseNode
{
    public function __construct($name = 'pagination')
    {
        parent::__construct($name);
    }

    public function setPage($page)
    {
        $this->getNode('page')->setValue($page);

        return $this;
    }

    public function getPage()
    {
        return $this->getNode('page')->getValue();
    }

    public function setPerPage($perPage)
    {
        $this->getNode('per_page')->setValue($perPage);

        return $this;
    }

    public function getPerPage()
    {
        return $this->getNode('per_page')->getValue();
    }

    public function setTotalPages($total)
    {
        $this->getNode('total_pages')->setValue($total);

        return $this;
    }

    public function getTotalPages()
    {
        return $this->getNode('total_pages')->getValue();
    }

    public function setTotalItems($total)
    {
        $this->getNode('total_items')->setValue($total);

        return $this;
    }

    public function getTotalItems()
    {
        return $this->getNode('total_items')->getValue();
    }
}
