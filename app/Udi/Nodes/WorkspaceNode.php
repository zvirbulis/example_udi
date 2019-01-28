<?php


namespace App\Udi\Nodes;

use App\Udi\Nodes\Workspace\ActionFieldNode;
use App\Udi\Nodes\Workspace\FormFieldsNode;
use App\Udi\Nodes\Workspace\FormNode;
use App\Udi\Nodes\Workspace\ListFieldsNode;
use App\Udi\Nodes\Workspace\ListNode;

class WorkspaceNode extends BaseNode
{
    public function __construct($workspace)
    {
        parent::__construct($workspace);
    }

    /**
     * @return ListFieldsNode
     */
    public function getListFields()
    {
        return $this->getList()->getFieldsList();
    }

    /**
     * @return BaseNode|bool
     */
    public function getListItems()
    {
        return $this->getList()->getItemsList();
    }

    /**
     * @return ListNode
     */
    public function getList()
    {
        /**
         * @var ListNode $list
         */
        $list = $this->getNode('list');

        return $list;
    }

    public function getFormValues()
    {
        $result = [
            'parent' => [],
            'self' => $this->getForm()->getFieldsValues()
        ];
        $parent = $this->getParentForm();
        if ($parent) {
            $result['parent'] = $parent->getFieldsValues();
        }

        return $result;
    }

    /**
     * @return FormFieldsNode
     */
    public function getFormFields()
    {
        /**
         * @var FormFieldsNode $formFields;
         */
        $formFields = $this->getForm()->getFieldsList();

        return $formFields;
    }

    public function setFormLink($link)
    {
        $this->getNodeByPath('forms.self')->requireNode('_link')->setValue($link);

        return $this;
    }

    public function setLink($link)
    {
        $this->requireNode('_link')->setValue($link);

        return $this;
    }

    public function getLink()
    {
        return $this->getNode('_link')->getValue();
    }

    /**
     * @return FormNode
     */
    public function getForm()
    {
        /**
         * @var FormNode $form
         */
        $form = $this->getNodeByPath('forms.self.grid');

        return $form;
    }

    /**
     * @return FormNode
     */
    public function getParentForm()
    {
        /**
         * @var FormNode $form
         */
        $form = $this->getNodeByPath('forms.parent.grid');

        return $form;
    }

    /**
     * @param $action
     * @return ActionFieldNode[]
     */
    public function getAction($action)
    {
        $result = [];
        $listAction = $this->getList()->getAction($action);
        $formAction = $this->getForm()->getAction($action);
        if ($listAction) {
            $result[] = $listAction;
        }
        if ($formAction) {
            $result[] = $formAction;
        }

        return $result;
    }

    public function getExceptions()
    {
        return $this->getNode('exceptions');
    }
}
