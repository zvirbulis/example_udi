<?php


namespace App\Udi\Exceptions;


use App\Udi\IoC;
use App\Udi\Nodes\BaseNode;
use App\Udi\Resources\WorkspaceResource;
use Throwable;

class UdiWorkspaceResourceException extends UdiException
{
    private $workspace;
    protected $message;

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        if(empty($message)){
            $message = $this->message;
        }
        parent::__construct($message, $code, $previous);
    }

    public function setWorkspace(WorkspaceResource $workspace)
    {
        $this->workspace = $workspace;
        if($exceptions = $workspace->getSkeleton()->getExceptions()){
            foreach(IoC::exceptions() as $code => $class){
                if($class === get_class($this) && $e = $exceptions->getNode($code)){
                    $this->processData($e);
                    break;
                }
            }
        }

        return $this;
    }

    protected function processData(BaseNode $node)
    {
        $value = $node->toArray(true);
        if(is_string($value)){
            $this->message = $value;
        }

        return $this;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }
}