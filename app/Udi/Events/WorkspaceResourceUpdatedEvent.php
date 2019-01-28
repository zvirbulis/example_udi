<?php


namespace App\Udi\Events;

use App\Udi\Resources\WorkspaceResource;

class WorkspaceResourceUpdatedEvent
{
    private $workspace;
    private $resourceId;

    public function __construct(WorkspaceResource $workspace, $resourceId)
    {
        $this->workspace = $workspace;
        $this->resourceId = $resourceId;
    }

    public function getWorkspace(): WorkspaceResource
    {
        return $this->workspace;
    }

    public function getResourceId()
    {
        return $this->resourceId;
    }
}
