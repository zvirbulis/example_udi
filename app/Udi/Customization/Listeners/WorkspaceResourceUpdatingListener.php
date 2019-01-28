<?php


namespace App\Udi\Customization\Listeners;

use App\Udi\Events\WorkspaceResourceUpdatingEvent;

class WorkspaceResourceUpdatingListener
{
    public function handle(WorkspaceResourceUpdatingEvent $event)
    {
        $workspace = $event->getWorkspace();
    }
}
