<?php


namespace App\Udi\Customization\Listeners;

use App\Facades\Service;
use App\Models\Announcement;
use App\Notifications\NewAnnouncementNotification;
use App\Udi\Events\WorkspaceResourceUpdatedEvent;

class WorkspaceResourceUpdatedListener
{
    public function handle(WorkspaceResourceUpdatedEvent $event)
    {
        $workspace = $event->getWorkspace();

        if ($workspace->getSkeleton()->getName() == 'Announcements') {
            $this->announcementUpdated($workspace, $event->getResourceId());
        }
    }

    private function announcementUpdated($workspace, $announcementId)
    {
        $announcement = Announcement::find($announcementId);

        $announcementNotExistInFeed = Service::feedService()->getFeedItemByResource($announcement)->count();

        if ($announcement->active && !$announcementNotExistInFeed) {
            \Notification::send(
                Service::userService()->getBroadcastUser(),
                new NewAnnouncementNotification($announcement)
            );
        }
    }
}
