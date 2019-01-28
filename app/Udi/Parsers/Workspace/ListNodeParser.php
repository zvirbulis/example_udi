<?php


namespace App\Udi\Parsers\Workspace;

use App\Udi\Nodes\BaseNode;
use App\Udi\Nodes\Workspace\ListNode;
use App\Udi\Parsers\BaseNodeParser;

class ListNodeParser extends BaseNodeParser
{
    protected $parsers = [
        'categories' => ListCategoriesNodeParser::class,
        'sort' => ListSortNodeParser::class,
        'pagination' => ListPaginationNodeParser::class,
        'filter' => ListFilterNodeParser::class,
        'fields' => ListFieldsNodeParser::class,
        'actions' => ActionsNodeParser::class
    ];

    public function __construct()
    {
        $node = new ListNode();

        parent::__construct($node);
    }

    public function parse($data): BaseNode
    {
        /**
         * @var ListNode $list
         */
        $list = parent::parse($data);
        if (!$list->getCategories()) {
            $categories = (new $this->parsers['categories'])->parse([]);
            $list->addNode($categories);
        }
        //Если сортировка не указана в схеме - создаем ее вручную, чтобы выставить сортировку по-умолчанию
        if (!$list->getSort()) {
            $sort = (new $this->parsers['sort'])->parse([]);
            $list->addNode($sort);
        }
        $list->initSort();
        $list->initPagination();

        return $list;
    }
}
