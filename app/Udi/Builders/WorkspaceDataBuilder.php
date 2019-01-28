<?php


namespace App\Udi\Builders;

use App\Udi\Builders\Workspace\ListFilterBuilder;
use App\Udi\Events\FillFormItemRelation;
use App\Udi\Exceptions\UdiException;
use App\Udi\Exceptions\UdiNotFoundException;
use App\Udi\Interfaces\HandbookFieldInterface;
use App\Udi\Interfaces\WorkspaceDataBuilderInterface;
use App\Udi\IoC;
use App\Udi\Nodes\BaseNode;
use App\Udi\Nodes\Workspace\FormFileFieldNode;
use App\Udi\Nodes\Workspace\FormHandbookFieldNode;
use App\Udi\Nodes\Workspace\FormNode;
use App\Udi\Nodes\Workspace\ListFieldsNode;
use App\Udi\Nodes\WorkspaceNode;
use App\Udi\Resources\WorkspaceResource;
use App\Udi\Builders\HandbookBuilder;
use App\Udi\Support\Formatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class WorkspaceDataBuilder extends BaseDataBuilder implements WorkspaceDataBuilderInterface
{
    /**
     * @var WorkspaceNode
     */
    protected $skeleton;
    protected $baseUri;

    private $withListItems = false;
    private $onlyItems = false;
    private $withListCategories = false;
    private $withFormItem = false;
    private $withParentFormItem = false;
    private $withFormErrors = false;

    private $autoHandbooks = [];
    /**
     * @var ListFilterBuilder
     */
    private $filterBuilder;

    public function __construct(BaseNode $skeleton)
    {
        parent::__construct($skeleton);
    }

    public function withListItems($onlyItems = false): WorkspaceDataBuilderInterface
    {
        $this->withListItems = true;
        $this->onlyItems = $onlyItems;
        $this->withListCategories = true;

        return $this;
    }

    public function withListCategories()
    {
        $this->withListCategories = true;

        return $this;
    }

    public function withoutListCategories()
    {
        $this->withListCategories = false;

        return $this;
    }

    public function withRequest(Request $request, $realId = null): WorkspaceDataBuilderInterface
    {
        if ($action = $request->get('action')) {
            $actionItemId = $request->id ?? $request->get('item_ids');
            if($realId){
                $actionItemId = $realId;
            }
            $this->doAction($action, $actionItemId, $request->query());
        }
        if ($request->has('field')) {
            $this->withListSort($request->get('field'), $request->get('order'));
        }
        if ($request->has('page') || $request->has('per_page')) {
            $page = $request->get('page') ?? 1;
            $this->withListPagination($page, $request->get('per_page'));
        }
        if ($request->has('filter')) {
            $this->withListFilter($request->get('filter'));
        }

        return $this;
    }

    private function doAction($actionCode, $id, array $params = [])
    {
        if (empty($id)) {
            throw new UdiException('No ids for "'.$actionCode.'" action');
        }
        $action = is_array($id)
            ? $this->skeleton->getList()->getAction($actionCode)
            : $this->skeleton->getForm()->getAction($actionCode)
        ;
        if (!$action) {
            throw new UdiNotFoundException('Операція з кодом "'.$actionCode.'" не знайдена в схемі');
        }
        if (!is_array($id)) {
            $id = [$id];
        }
        $action->run($id, $params);
    }

    public function withListSort($field, $order = null): WorkspaceDataBuilderInterface
    {
        $list = $this->skeleton->getList();
        $list->getSort()->setField($field)->setOrder($order);
        $list->initSort();

        return $this;
    }

    public function withListPagination($page, $perPage = null): WorkspaceDataBuilderInterface
    {
        $list = $this->skeleton->getList();
        if ($pagination = $list->getPagination()) {
            $pagination->setPage($page);
            if ($perPage) {
                $pagination->setPerPage($perPage);
            }
            $list->initPagination();
        }

        return $this;
    }

    public function withListFilter($query): WorkspaceDataBuilderInterface
    {
        $list = $this->skeleton->getList();
        $this->filterBuilder = new ListFilterBuilder($query, $list->getFilterableFields());
        if ($filter = $list->getFilter()) {
            $filter->acceptBuilder($this->filterBuilder);
        }

        return $this;
    }

    public function withLink($link, $workspacePair = null): WorkspaceDataBuilderInterface
    {
        if ($workspacePair) {
            $link = rtrim($link, '/');
            $linkParams = explode('/', $link);
            if (isset($workspacePair['id'])) {
                array_pop($linkParams);
            }
            if (isset($workspacePair['workspace'])) {
                array_pop($linkParams);
            }
            $link = implode('/', $linkParams);
        }

        $this->skeleton->setLink($link);

        return $this;
    }

    public function withFormItem($id): WorkspaceDataBuilderInterface
    {
        $this->withFormItem = $id;

        return $this;
    }

    public function withFormErrors(array $errors): WorkspaceDataBuilderInterface
    {
        $this->withFormErrors = $errors;

        return $this;
    }

    public function withParentFormItem(array $workspacePair): WorkspaceDataBuilderInterface
    {
        $this->withParentFormItem = $workspacePair;

        return $this;
    }

    public function build()
    {
        if (!$this->onlyItems) {
            $this->fillFormHandbooks();
            $this->fillFormFilesUploaderUrl();
            $this->completeFormValidationRules();
            $this->acceptVisibility();
        }

        if ($errors = $this->withFormErrors) {
            $this->fillFormErrors($errors);
        }

        if ($workspacePair = $this->withParentFormItem) {
            $resource = new WorkspaceResource($workspacePair['workspace'], IoC::schemaRepository());
            $resource
                ->withLink($this->skeleton->getLink(), $workspacePair)
                ->withFormItem($workspacePair['id'])
                ->build();
            $this->skeleton->getNodeByPath('forms')->addNode(
                $resource->getSkeleton()->getNodeByPath('forms.self')->setName('parent')
            );
            if ($this->withListItems) {
                //устанавливаем ИД сущности родительской формы (если оно есть в форме)
                $this->fillFormForeignFieldValue($workspacePair['id']);
            }
        }

        if ($list = $this->skeleton->getList()) {
            if ($this->withListItems) {
                $this->fillListItems();
            }

            if ($this->withListCategories) {
                $this->fillListCategories();
            }
        }

        if ($this->filterBuilder) {
            $this->fillFilterHandbooks();
        }

        if ($itemId = $this->withFormItem) {
            $this->fillFormItem($itemId);
        }

        $data = parent::build()[$this->skeleton->getName()];
        return $data;
    }

    private function acceptVisibility()
    {
        $form = $this->skeleton->getForm();
        foreach ($form->getFields() as $field) {
            $removed = false;
            if ($this->withFormItem && $field->onlyNew()) {
                $removed = $field->remove();
            } elseif (!$this->withFormItem && $field->onlyEdit()) {
                $removed = $field->remove();
            }
            if ($removed && ($orderField = $form->getOrderField($field->getCodeValue()))) {
                $orderField->remove();
            }
        }
    }


    private function fillFormForeignFieldValue($id)
    {
        $parentInfo = QueryBuilder::modelInfoByField(
            $this->skeleton->getParentForm()->getPrimaryField()->getCodeValue()
        );
        $form = $this->skeleton->getForm();
        $selfInfo  = QueryBuilder::fieldInfo(
            $form->getPrimaryField()->getCodeValue()
        );
        $item = [$selfInfo['model'].'.'.$parentInfo['foreignKey'] => $id];
        $form->setFieldsValues($item);
        $this->fillFormAutoHandbooks($item);
    }

    /**
     * @return $this
     * @throws UdiException
     */
    protected function fillFormHandbooks()
    {
        $form = $this->skeleton->getForm();
        $handbooks = $form->getFieldsList()->getHandbooks();
        foreach ($handbooks as $handbookField) {
            $this->fillHandbook($handbookField);
        }

        return $this;
    }

    private function fillHandbook(HandbookFieldInterface $handbookField)
    {
        $settings = $handbookField->getSettings();
        if (!$settings) {
            return;
        }
        $filter = $settings->getNode('filter');
        if($filter){
            $filterQuery = $this->normalizeFilter($filter->getValue());
            if($this->withFormItem){
                $filterQuery = str_replace(['$id'], [$this->withFormItem], $filterQuery);
            }
        }
        //справочник с автозаполнением
        if ($autocomplete = $settings->getNode('autocomplete')) {
            $link = $autocomplete->requireNode('_link')->getValue();
            $limit = $autocomplete->requireNode('limit')->getValue();
            if (!$link) {
                $link = '/udi/handbooks/'.$this->skeleton->getName().'/?_field='.$handbookField->getCodeValue();
            }
            if ($limit) {
                $link.="&per_page=".$limit;
            }
            if ($filter) {
                $link.= '&filter='.$filterQuery;
            }
            $autocomplete->getNode('_link')->setValue($link);
            $this->autoHandbooks[] = $handbookField;
        }
        //обычный справочник
        else {
            $handbook = HandbookBuilder::createByWorkspaceNode($this->skeleton, $handbookField->getCodeValue());
            if ($filter) {
                $handbook->withListFilter($filterQuery);
            }
            $handbookField->setAvailableValues($handbook->get()->toArray());
        }
    }

    private function normalizeFilter($filterQuery)
    {
        $replace = [];
        if($this->withFormItem){
            $replace['$id'] = $this->withFormItem;
        }
        if(preg_match_all('/\$self\[(.*?)\]/', $filterQuery, $matches)){
            foreach($matches[1] as $fieldName){
                $value = null;
                if($this->withFormItem){
                    $field = $this->skeleton->getForm()->getField($fieldName);
                    if(!$field){
                        throw new UdiException('Filter field "'.$fieldName.'" for form not found');
                    }
                    $item = (new QueryBuilder())
                        ->initModel($fieldName)
                        ->select([$fieldName])
                        ->where($this->skeleton->getForm()->getPrimaryField()->getCodeValue(), '=', $this->withFormItem)
                        ->build()
                        ->first();
                    $value = $item[$fieldName];
                }
                elseif($this->filterBuilder){
                    $field = $this->skeleton->getList()->getFilter()->getField($fieldName);
                    if(!$field){
                        throw new UdiException('Filter field "'.$fieldName.'" for list filter not found');
                    }
                    $value = $field->getValueValue();
                }

                if(!is_null($value)){
                    $replace['$self['.$fieldName.']'] = $value;
                }
            }
        }

        if(!empty($replace)){
            $filterQuery = str_replace(array_keys($replace), array_values($replace), $filterQuery);
        }

        return $filterQuery;
    }

    protected function fillFormFilesUploaderUrl()
    {
        $form = $this->skeleton->getForm();
        $files = $form->getFieldsList()->getFiles();
        foreach ($files as $file) {
            $file->initUploaderUrl($this->skeleton->getName());
        }

        return $this;
    }


    protected function completeFormValidationRules()
    {
        $form = $this->skeleton->getForm();
        $id = $this->withFormItem;
        foreach ($form->getFields() as $field) {
            if (!($validation = $field->getValidation())) {
                continue;
            }
            $fieldInfo = QueryBuilder::fieldInfo($field->getCodeValue());
            $rules = explode('|', (string)$field->getValidationRules());
            foreach ($rules as &$rule) {
                if ($rule === 'unique') {
                    $rule.=':'.$fieldInfo['modelInfo']['table'].','.$fieldInfo['attribute'];
                    //для существующей сущности добавляем id этой сущности в исключение проверки уникальности
                    //чтобы её(сущность) можно было бы пересохранить
                    if ($id) {
                        $rule.= ','.$id;
                    }
                } elseif (mb_strpos($rule, 'strict_unique') !== false) {
                    if ($this->withParentFormItem) {
                        $rule = str_replace('$parent_id', $this->withParentFormItem['id'], $rule);
                    }
                    if ($id) {
                        $rule.=','.'except='.$id;
                    }
                }
            }
            unset($rule);
            $field->setValidationRules(implode('|', $rules));
        }

        return $this;
    }

    protected function fillFormErrors(array $errors)
    {
        $form = $this->skeleton->getForm();
        $form->setFieldsErrors($errors);

        return $this;
    }

    /**
     * @return $this
     * @throws UdiException
     */
    protected function fillListItems()
    {
        $list = $this->skeleton->getList();
        $fields = $list->getFieldsList();
        $primary = $fields->getPrimary();
        if (!$primary) {
            throw new UdiException('Первинне поле не знайдено для списку робочих областей');
        }
        $items = (new QueryBuilder())
            ->initModel($primary->getCodeValue())
            ->select($fields->getFieldCodes())
            ->condition($list->canSorted(), function (QueryBuilder $query) use ($list) {
                $query->orderBy($list->getSortField(), $list->getSortOrder());
            })
            ->condition($workspacePair = $this->withParentFormItem, function (QueryBuilder $query) use ($workspacePair, $primary) {
                $fieldCode = $this->skeleton->getParentForm()->getPrimaryField()->getCodeValue();
                $fieldInfo = QueryBuilder::fieldInfo($fieldCode);
                $modelInfo = $fieldInfo['modelInfo'];
                if ($modelInfo['pivot']) {
                    $modelInfo['foreignKey'] = $fieldInfo['attribute'];
                }
                $query->where($query->getModel().'.'.$modelInfo['foreignKey'], '=', $workspacePair['id']);
            })
            ->condition($pagination = $list->getPagination(), function (QueryBuilder $query) use ($pagination) {
                $query->page($pagination->getPage(), $pagination->getPerPage());
            })
            ->condition($filter = $list->getFilter(), function (QueryBuilder $query) use ($filter) {
                if (!$this->filterBuilder) {
                    $this->filterBuilder = new ListFilterBuilder('', $filter->getFilterableFields());
                    $filter->acceptBuilder($this->filterBuilder);
                }
                $this->filterBuilder->build($query);
            })
            ->get();

        if ($pagination && $items instanceof LengthAwarePaginator) {
            $pagination
                ->setTotalItems($items->total())
                ->setTotalPages($items->lastPage());
            $items = $items->getCollection();
        }
        $items = $items->toArray();

        foreach ($items as &$item) {
            $item = $this->normalizeItemValues($item);
            $item = $this->formatItemValues($item, $fields);
            $item['_link'] = $this->createLink($item[$primary->getCodeValue()]);
        }
        unset($item);

        $list->getItemsList()
            ->flush()
            ->setValue($items);

        return $this;
    }

    private function formatItemValues($item, ListFieldsNode $fields)
    {
        foreach($item as $key => $value){
            $field = $fields->getField($key);
            if($field && $format = $field->getFormat()){
                $item[$key] = Formatter::format($format, $value);
            }
        }

        return $item;
    }

    /**
     * @throws UdiException
     */
    protected function fillListCategories()
    {
        $list = $this->skeleton->getList();
        $categories = $list->getCategories();

        $fieldInfo = QueryBuilder::fieldInfo($list->getPrimary()->getCodeValue());
        $relationField = '';
        $relationIds = $levels = [];
        foreach ($categories->getLevels() as $i => $levelField) {
            $levels[$i] = [
                'field' => $fieldInfo['model'],
                'items' => []
            ];
            $query = (new QueryBuilder())->initModel($levelField);
            $fieldInfo = $query::fieldInfo($levelField);
            $primaryField = $fieldInfo['model'].'.'.$fieldInfo['modelInfo']['primaryKey'];
            if ($fieldInfo['relatives'] && current($fieldInfo['relatives'])['pivot']) {
                $relationField = key($fieldInfo['relatives']).'.'.$relationField;
            } else {
                $relationField = $fieldInfo['model'].'.'.$relationField;
            }
            $items = $query
                ->select([$levelField, $primaryField])
                ->condition(!empty($relationIds), function (QueryBuilder $query) use ($relationField, $relationIds, $i) {
                    $query->where($relationField, '=', $relationIds);
                })
                ->orderBy($levelField)
                ->groupConcat(false)
                ->get()
                ->toArray();

            $relationIds = [];
            foreach ($items as $item) {
                $item = (array)$item;
                $primaryValue = $item[$primaryField];
                $parentPrimaryValue = isset($item[$relationField]) ? $item[$relationField] : null;
                $levels[$i]['items'][(int)$parentPrimaryValue][] = [
                    'id' => $primaryValue,
                    'parent_id' => $parentPrimaryValue,
                    'label' => $item[$levelField],
                    'items' => []
                ];
                $relationIds[] = $primaryValue;
            }
            $relationField = $fieldInfo['modelInfo']['foreignKey'];
            $levels[$i]['field'] = $levels[$i]['field'].=".".$relationField;
        }

        $items = [];
        if (!empty($levels)) {
            $items = $this->getCategoryItems($levels[0], $levels);
        }

        $categories->setItems($items);

        return $this;
    }

    private function getCategoryItems($categoryItems, $levels, $levelNum = 1)
    {
        $result = [];
        $levelsCount = count($levels);
        $subCategoryItems = isset($levels[$levelNum]) ? $levels[$levelNum] : null;
        foreach ($categoryItems['items'] as $parentId => $items) {
            foreach ($items as $item) {
                if ($subCategoryItems && isset($subCategoryItems['items'][$item['id']])) {
                    $item['items'] = $this->getCategoryItems($subCategoryItems, $levels, $levelNum + 1);
                } elseif ($levelsCount == $levelNum) {
                    $item['_link'] = $this->filterLink([
                        $categoryItems['field'] => $item['id']
                    ]);
                }
                $result[] = $item;
            }
        }

        return $result;
    }

    protected function fillFormItem($itemId)
    {
        $itemId = (int)$itemId;
        $form = $this->skeleton->getForm();
        $primary = $form->getPrimaryField();
        if (!$primary) {
            throw new UdiException('Первинне поле не знайдено для форми робочих областей');
        }
        $fieldCode = $primary->getCodeValue();
        $model = $item = (new QueryBuilder())
            ->initModel($fieldCode)
            ->select($form->getFieldsList()->getFieldCodes())
            ->where($fieldCode, '=', $itemId)
            ->build()
            ->first();

        if (!$item) {
            throw new UdiNotFoundException(
                'Модель "'.QueryBuilder::modelInfoByField($fieldCode)['model'].'" з id = "'.$itemId.'" не знайдено!'
            );
        }
        if ($item instanceof Model) {
            $item = $item->toArray();
        }
        $item = $this->normalizeItemValues($item);
        $this->fillFormFileValues($form, $item);
        $this->fillFormAutoHandbooks($item);
        $form->setFieldsValues($item);
        $link = $this->createLink($itemId);
        foreach ($form->getRelations() as $relation) {
            event(new FillFormItemRelation($relation, $model));
            if ($relationCode = $relation->getCodeValue()) {
                $relation->setLink($link.'/'.$relationCode);
            }
        }
        $this->skeleton->setFormLink($link);

        return $this;
    }

    private function normalizeItemValues($values)
    {
        $values = (array)$values;
        foreach ($values as &$value) {
            $separator = QueryBuilder::MULTIPLE_VALUES_SEPARATOR;
            if (is_string($value) && strpos($value, $separator) !== false) {
                $value = explode($separator, $value);
            }
        }
        unset($value);

        return $values;
    }

    private function fillFormFileValues(FormNode $form, array &$fieldValues)
    {
        $files = $form->getFieldsList()->getFiles();
        foreach ($files as $file) {
            if (
                !$file->getSettings() ||
                !isset($fieldValues[$file->getCodeValue()])
            ) {
                continue;
            }

            $filesId = &$fieldValues[$file->getCodeValue()];

            $query = new QueryBuilder();
            $values = $query
                ->initModel($file->getKeyField())
                ->select([
                    $file->getKeyField(),
                    $file->getNameField(),
                    $file->getSrcField(),
                    $file->getSizeField()
                ])
                ->where($file->getKeyField(), '=', $filesId)
                ->order([$file->getNameField() => 'asc'])
                ->get();

            $file->setFileValues($values->toArray());
            $file->initUploaderUrl($this->skeleton->getName());
            if (!is_array($filesId)) {
                $filesId = [$filesId];
            }
        }

        return $this;
    }

    private function fillFormAutoHandbooks(array $fieldValues)
    {
        /**
         * @var FormHandbookFieldNode $handbookField
         */
        foreach ($this->autoHandbooks as $handbookField) {
            $code = $handbookField->getCodeValue();
            if (isset($fieldValues[$code])) {
                $handbook = HandbookBuilder::createByWorkspaceNode($this->skeleton, $code);
                $filter = $this->filterQuery([
                    $handbookField->getKeyField() => $fieldValues[$code]
                ]);
                $handbook->withListFilter($filter);
                $handbookField->setAvailableValues($handbook->get()->toArray());
            }
        }
    }

    private function fillFilterHandbooks()
    {
        $filter = $this->skeleton->getList()->getFilter();
        if (!$filter) {
            return false;
        }

        $form = $this->skeleton->getForm();
        foreach ($filter->getFields() as $filterField) {
            if (!$filterField->isHandbook()) {
                continue;
            }
            /**
             * @var HandbookFieldInterface $filterField
             * @var HandbookFieldInterface $formField
             */
            $field = $filterField->getCodeValue();
            $formField = $form->getField($field);
            if ($formField && $formField->isHandbook()) {
                if($filterField->getSettings() && $filterField->getSettings()->hasNode('autocomplete')){
                    $this->fillHandbook($filterField);
                    if($filterValue = $filterField->getValueValue()){
                        $this->fillFormAutoHandbooks([$filterField->getCodeValue() => $filterField->getValueValue()]);
                    }
                }
                $filterField->getAvailableValues()->setValue($formField->getAvailableValues()->getValue());
            } else {
                $this->fillHandbook($filterField);
            }
        }
    }

    private function filterLink(array $filters)
    {
        $link = $this->createLink('');
        if (empty($filters)) {
            return $link;
        }
        $link.="?filter=".$this->filterQuery($filters);

        return $link;
    }

    private function filterQuery(array $filters)
    {
        $query = '';
        foreach ($filters as $filterKey => $filterValues) {
            $query.=$filterKey.ListFilterBuilder::GLUE_FIELD_VALUES;
            if (!is_array($filterValues)) {
                $filterValues = [$filterValues];
            }
            $query.=implode(ListFilterBuilder::GLUE_VALUES, $filterValues);
            $query.=ListFilterBuilder::GLUE_FIELDS;
        }
        $query = rtrim($query, ListFilterBuilder::GLUE_FIELDS);

        return $query;
    }

    private function createLink($uri)
    {
        return rtrim($this->skeleton->getLink(), '/').'/'.ltrim($uri, '/');
    }
}
