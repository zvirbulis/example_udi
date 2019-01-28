<?php


namespace App\Udi\Nodes\Workspace;

use App\Udi\Nodes\BaseNode;

class FormNode extends BaseNode
{
    public $models = [];
    public $matching = [];

    public function getFieldsValues()
    {
        return $this->getFieldsList()->getValues();
    }

    public function getPrimaryField()
    {
        $primary = $this->getFieldsList()->getPrimary();
        $orders = $this->getFieldsOrder();
        foreach ($orders as $field) {
            if ($field->origin === $primary->getName()) {
                $primary->fieldCode = $field;
                break;
            }
        }

        return $primary;
    }

    public function validate()
    {
        return $this->getFieldsList()->validate();
    }

    public function setFieldsValues(array $values)
    {
        $this->getFieldsList()->setValues($values);

        return $this;
    }

    public function setFieldsErrors(array $errors)
    {
        $this->getFieldsList()->setErrors($errors);

        return $this;
    }

    /**
     * @param $code
     * @return FormFieldNode
     */
    public function getField($code)
    {
        return $this->getFieldsList()->getField($code);
    }

    /**
     * @return FormFieldNode[]
     */
    public function getFields()
    {
        return $this->getFieldsList()->getFields();
    }

    /**
     * @return FormFieldsNode
     */
    public function getFieldsList()
    {
        /**
         * @var FormFieldsNode $fields
         */
        $fields = $this->getNode('fields');

        return $fields;
    }

    /**
     * @return FieldCodeNode[]
     */
    public function getFieldsOrder()
    {
        /**
         * @var FieldCodeNode[] $nodes
         */
        $nodes = $this->getNodeByPath('order')->getNodes();

        return $nodes;
    }

    /**
     * @return FormRelationNode[]
     */
    public function getRelations()
    {
        /**
         * @var FormRelationNode[] $relations;
         */
        $relations = $this->getRelationsList()->getRelations();

        return $relations;
    }

    /**
     * @return FormRelationsNode
     */
    public function getRelationsList()
    {
        /**
         * @var FormRelationsNode $relations
         */
        $relations = $this->getNode('relations');

        return $relations;
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

    public function getOrderField($fieldName)
    {
        foreach ($this->getFieldsOrder() as $orderField) {
            if ($orderField->getValue() === $fieldName) {
                return $orderField;
            }
        }

        return false;
    }
}
