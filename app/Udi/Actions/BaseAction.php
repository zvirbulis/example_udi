<?php


namespace App\Udi\Actions;

use App\Udi\Exceptions\UdiProgressActionException;
use App\Udi\Nodes\Workspace\ActionFieldNode;

abstract class BaseAction
{
    protected $params = [];
    protected $node;

    public function __construct(array $params, ActionFieldNode $node)
    {
        $this->params = $params;
        $this->node = $node;
    }

    abstract public function run($id);

    public function redirect($link)
    {
        $link = $this->node->getRedirectLink() ?: $link;
        $this->node->setRedirectLink($link);

        return $this;
    }

    public function success($message)
    {
        $message = $this->node->getSuccessMessage() ?: $message;
        $this->node->setSuccessMessage($message);
        $this->node->setErrorMessage('');
        return $this;
    }

    public function progress($step, $total, array $params = [])
    {
        $data = [
            'in_progress' => true,
            'progress' => [
                'step' => $step,
                'total' => $total
            ],
            'params' => $params
        ];
        $e = (new UdiProgressActionException())->setReponseData($data);

        throw $e;
    }

    public function error($message)
    {
        $message = $this->node->getErrorMessage() ?: $message;
        $this->node->setErrorMessage($message);
        $this->node->setSuccessMessage('');

        return $this;
    }
}
