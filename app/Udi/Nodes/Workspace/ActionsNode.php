<?php


namespace App\Udi\Nodes\Workspace;

use App\Udi\Nodes\BaseNode;

class ActionsNode extends BaseNode
{
    public function __construct($name = 'actions')
    {
        parent::__construct($name);
    }

    public function getAction($action)
    {
        /**
         * @var ActionFieldNode[] $actions
         */
        $actions = $this->getNodes();
        foreach ($actions as $actionNode) {
            if ($actionNode->getCodeValue() == $action) {
                return $actionNode;
            }
        }

        return false;
    }
}
