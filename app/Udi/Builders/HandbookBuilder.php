<?php


namespace App\Udi\Builders;

use App\Udi\Builders\Workspace\ListFilterBuilder;
use App\Udi\Exceptions\UdiException;
use App\Udi\Nodes\Workspace\FormHandbookFieldNode;
use App\Udi\Nodes\WorkspaceNode;
use Illuminate\Http\Request;

class HandbookBuilder
{
    protected $keyField;
    protected $valueField;
    protected $query;

    /**
     * HandbookBuilder constructor.
     * @param $keyField
     * @param $valueField
     * @throws UdiException
     */
    public function __construct($keyField, $valueField)
    {
        $this->keyField = $keyField;
        $this->valueField = $valueField;
        $this->query = (new QueryBuilder())
            ->initModel($this->keyField)
            ->select([$this->keyField, $this->valueField])
            ->orderBy($this->valueField, 'asc');
    }

    /**
     * @param WorkspaceNode $workspace
     * @param $field
     * @return HandbookBuilder
     * @throws UdiException
     */
    public static function createByWorkspaceNode(WorkspaceNode $workspace, $field)
    {
        $form = $workspace->getForm();
        $handbookField = $form->getField($field);
        if (!($handbookField && $handbookField->isHandbook())) {
            throw new UdiException('Простір "'.$workspace->getName().'" не має поля "'.$field.'"');
        }
        /**
         * @var FormHandbookFieldNode $handbookField
         */

        $handbook = new self($handbookField->getKeyField(), $handbookField->getValueField());
        if($handbookField->hasSort()){
            list($sortField, $sortOrder) = explode(':', $handbookField->getSort());
            $handbook->resetSort()->withListSort($sortField, $sortOrder);
        }

        return $handbook;
    }

    public function withRequest(Request $request)
    {
        if ($request->has('page') || $request->has('per_page')) {
            $page = $request->get('page') ?? 1;
            $this->withListPagination($page, $request->get('per_page'));
        }
        if ($request->has('filter')) {
            $this->withListFilter($request->get('filter'));
        }

        return $this;
    }

    public function resetSort()
    {
        $this->query->order([]);

        return $this;
    }

    public function withListSort($field, $order = null)
    {
        $this->query->orderBy($field, $order);

        return $this;
    }

    public function withListPagination($page, $perPage = null)
    {
        $this->query->page($page, $perPage);

        return $this;
    }

    public function withListFilter($query)
    {
        $filterBuilder = new ListFilterBuilder($query, null);
        $filterBuilder->build($this->query);

        return $this;
    }

    /**
     * @return \Illuminate\Support\Collection
     * @throws UdiException
     */
    public function get()
    {
        return $this->query->build()->get();
    }
}
