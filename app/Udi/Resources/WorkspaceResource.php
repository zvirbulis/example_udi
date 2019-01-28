<?php


namespace App\Udi\Resources;

use App\Udi\Builders\BaseDataBuilder;
use App\Udi\Builders\QueryBuilder;
use App\Udi\Events\WorkspaceResourceCreatedEvent;
use App\Udi\Events\WorkspaceResourceCreatingEvent;
use App\Udi\Events\WorkspaceResourceUpdatedEvent;
use App\Udi\Events\WorkspaceResourceUpdatingEvent;
use App\Udi\Exceptions\UdiDuplicateEntryException;
use App\Udi\Exceptions\UdiException;
use App\Udi\Exceptions\UdiForeignKeysDeleteException;
use App\Udi\Exceptions\UdiNotFoundException;
use App\Udi\Interfaces\UdiUserInterface;
use App\Udi\Interfaces\WorkspaceDataBuilderInterface;
use App\Udi\IoC;
use App\Udi\Parsers\WorkspaceNodeParser;
use App\Udi\SchemaRepositories\AbstractSchemaRepository;
use App\Udi\Schemas\AbstractSchema;
use App\Udi\Nodes\WorkspaceNode;
use App\Udi\Support\Result;
use Doctrine\DBAL\Driver\Mysqli\MysqliException;
use function GuzzleHttp\Psr7\str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Class WorkspaceResource
 * @package App\Udi\Resources
 * @mixin WorkspaceDataBuilderInterface
 */
class WorkspaceResource extends AbstractResource
{
    /**
     * @var WorkspaceNode
     */
    protected $skeleton;
    protected $builder;
    protected $parser;

    /**
     * WorkspaceResource constructor.
     * @param $workspace
     * @param AbstractSchemaRepository $schemaRepository
     * @throws UdiNotFoundException
     */
    public function __construct($workspace, AbstractSchemaRepository $schemaRepository)
    {
        parent::__construct('workspaces/'.$workspace, $schemaRepository);

        $dataSchema = $this->getDataSchema();
        if (!$dataSchema) {
            throw new UdiNotFoundException('Схема не знайдена для "'.$workspace.'"');
        }
        $this->parser = new WorkspaceNodeParser($workspace);
        $this->skeleton = $this->parser->parse($dataSchema->toArray());
        $this->skeleton->setLink($this->baseUrl);
        $this->setBuilder(IoC::workspaceBuilder($this->skeleton));
    }

    public function accessable(UdiUserInterface $user)
    {
        $access = parent::accessable($user);
        $this->skeleton = $this->parser->parse($this->getDataSchema()->toArray());
        $this->skeleton->setLink($this->baseUrl);

        return $access;
    }

    public function getSkeleton()
    {
        return $this->skeleton;
    }

    /**
     * @param AbstractSchema $dataSchema
     * @return Result
     * @throws UdiException
     */
    public function create(AbstractSchema $dataSchema)
    {
        $form = $this->mergeDataSchema($dataSchema)->skeleton->getForm();
        $primary = $form->getPrimaryField();
        event(new WorkspaceResourceCreatingEvent($this));
        $result = Result::validate($form->validate());
        if ($result->isSuccess()) {
            $values = $form->getFieldsValues();
            $query = new QueryBuilder();
            $query
                ->initModel($primary->getCodeValue())
                ->values($values);
            if ($parentForm = $this->skeleton->getParentForm()) {
                $parentPrimary = $parentForm->getPrimaryField();
                $query->attachByField($parentPrimary->getCodeValue(), $parentPrimary->getValueValue());
            }
            try {
                if ($id = $query->create()) {
                    event(new WorkspaceResourceCreatedEvent($this, $id));
                    $result->setData('id', $id);
                }
            } catch (QueryException $e) {
                if (strpos($e->getMessage(), 'Duplicate') !== false) {
                    throw IoC::exception('create-duplicate-entry', [$this]);
                }
                throw $e;
            }
        }

        return $result;
    }

    /**
     * @param $id
     * @param AbstractSchema $dataSchema
     * @return Result
     * @throws UdiException
     * @throws UdiNotFoundException
     */
    public function update($id, AbstractSchema $dataSchema)
    {
        $form = $this->mergeDataSchema($dataSchema)->skeleton->getForm();
        event(new WorkspaceResourceUpdatingEvent($this, $id));

        $result = Result::validate($form->validate());
        if ($result->isSuccess()) {
            $id = (new QueryBuilder())
                ->initModel($form->getPrimaryField()->getCodeValue())
                ->values($form->getFieldsValues())
                ->condition($parentForm = $this->skeleton->getParentForm(), function (QueryBuilder $query) use ($parentForm) {
                    $parentPrimary = $parentForm->getPrimaryField();
                    $query->attachByField($parentPrimary->getCodeValue(), $parentPrimary->getValueValue());
                })
                ->update($id);
            event(new WorkspaceResourceUpdatedEvent($this, $id));

            $result->setData('id', $id);
        }

        return $result;
    }

    public function mergeDataSchema(AbstractSchema $dataSchema)
    {
        $formFields = $dataSchema->get('forms.self.grid.fields');
        if ($formFields) {
            foreach ($formFields as $code => &$field) {
                if (isset($field['select_values'])) {
                    $field['select_values'] = [];
                }
            }
            unset($field);
            $dataSchema->set('forms.self.grid.fields', $formFields);
        }
        $dataSchema = parent::mergeDataSchema($dataSchema)->getDataSchema();
        $this->skeleton = $this->parser->parse($dataSchema->toArray());

        return $this;
    }

    /**
     * @param array $ids
     * @throws \Exception
     */
    public function deleteMany(array $ids)
    {
        DB::beginTransaction();
        foreach ($ids as $id) {
            try {
                $this->delete($id);
            } catch (UdiNotFoundException $e) {
                continue;
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }
        DB::commit();
    }

    /**
     * @param $id
     * @return $this
     * @throws UdiException
     * @throws UdiNotFoundException
     */
    public function delete($id)
    {
        $primary = $this->skeleton->getListFields()->getPrimary()->getCodeValue();
        $query = (new QueryBuilder())->initModel($primary);
        $query->condition($parentForm = $this->skeleton->getParentForm(), function (QueryBuilder $query) use ($parentForm) {
            $parentPrimary = $parentForm->getPrimaryField();
            $fieldInfo = $query::fieldInfo($parentPrimary->getCodeValue());
            $query->where($query->getModel().'.'.$fieldInfo['modelInfo']['foreignKey'], '=', $parentPrimary->getValueValue());
        });

        try {
            if (!$query->delete($id)) {
                throw new UdiException('Модель "'.$query->getModel().'" з id = "'.$id.'"  не може бути видаленою. Скасування операції');
            }
        } catch (QueryException $e) {
            if (strpos($e->getMessage(), 'foreign key')) {
                throw IoC::exception('delete-foreign-keys', [$this]);
            }
            throw $e;
        }

        return $this;
    }

    public function setBuilder(WorkspaceDataBuilderInterface $builder)
    {
        $this->builder = $builder;

        return $this;
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->builder, $name)) {
            return call_user_func_array([$this->builder, $name], $arguments);
        }
    }
}
