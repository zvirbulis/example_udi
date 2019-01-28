<?php


namespace App\Udi\Events;

use App\Udi\Resources\WorkspaceResource;

class WorkspaceResourceCreatingEvent
{
    private $workspace;

    public function __construct(WorkspaceResource $workspace)
    {
        $this->workspace = $workspace;
    }

    public function getWorkspace(): WorkspaceResource
    {
        return $this->workspace;
    }
}
