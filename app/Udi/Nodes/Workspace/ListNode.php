<?php


namespace App\Udi\Nodes\Workspace;

use App\Udi\Nodes\BaseNode;

class ListNode extends BaseNode
{
    public function __construct($name = 'list')
    {
        parent::__construct($name);
    }

    public function initSort()
    {
        $sort = $this->getSort();
        $fieldCode = $sort->getField();
        if (!$fieldCode) {
            $primary = $this->getFieldsList()->getPrimary();
            $fieldCode = $primary ? $primary->getCodeValue() : null;
        }
        if ($fieldCode && $this->getFieldsList()->isSortable($fieldCode)) {
            //если primary не указан или по нему нельзя сортировать - очищаем ноду $sort
            $sort->setField($fieldCode);
        } else {
            $sort->flush();
        }

        $fieldOrder = $sort->getOrder() ?: 'desc';
        $fieldOrder = (strtolower($fieldOrder) == 'asc') ? 'asc' : 'desc';
        $sort->setOrder($fieldOrder);
    }

    public function initPagination()
    {
        $pagination = $this->getPagination();
        if ($pagination) {
            $pagination->setPage($pagination->getPage() ?? 1);
            $pagination->setPerPage($pagination->getPerPage() ?? 20);
        }
    }

    public function canSorted()
    {
        return !!$this->getSortField();
    }

    public function canPaginate()
    {
        return !!$this->getPagination();
    }

    public function canFilter()
    {
        return !!$this->getFilter();
    }

    public function getSortField()
    {
        return $this->getSort()->getField();
    }

    public function getSortOrder()
    {
        return $this->getSort()->getOrder();
    }

    /**
     * @return ListFilterNode
     */
    public function getFilter()
    {
        /**
         * @var ListFilterNode $filter
         */
        $filter = $this->getNode('filter');

        return $filter;
    }

    /**
     * @return ListSortNode
     */
    public function getSort()
    {
        /**
         * @var ListSortNode $sort
         */
        $sort = $this->getNode('sort');

        return $sort;
    }

    public function getFilterableFields()
    {
        if ($filter = $this->getFilter()) {
            return $filter->getFilterableFields();
        }

        return [];
    }

    public function getPrimary()
    {
        return $this->getFieldsList()->getPrimary();
    }

    /**
     * @return ListFieldsNode
     */
    public function getFieldsList()
    {
        /**
         * @var ListFieldsNode $fields
         */
        $fields = $this->getNode('fields');

        return $fields;
    }

    public function getItemsList()
    {
        return $this->getNode('items');
    }

    /**
     * @return ListPaginationNode
     */
    public function getPagination()
    {
        /**
         * @var ListPaginationNode $pagination
         */
        $pagination = $this->getNode('pagination');

        return $pagination;
    }

    /**
     * @return ListCategoriesNode
     */
    public function getCategories()
    {
        /**
         * @var ListCategoriesNode $categories
         */
        $categories = $this->getNode('categories');

        return $categories;
    }

    public function getAction($action)
    {
        return $this->getActions()->getAction($action);
    }


    /**
     * @return ActionsNode
     */
    public function getActions()
    {
        /**
         * @var ActionsNode $actions
         */
        $actions = $this->requireNode('actions', ActionsNode::class);

        return $actions;
    }
}
