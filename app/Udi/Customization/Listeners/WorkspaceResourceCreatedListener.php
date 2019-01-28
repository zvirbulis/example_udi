<?php


namespace App\Udi\Customization\Listeners;

use App\Facades\Service;
use App\Models\Announcement;
use App\Models\Incident;
use App\Notifications\IncidentNotification;
use App\Notifications\NewAnnouncementNotification;
use App\Udi\Events\WorkspaceResourceCreatedEvent;
use App\Udi\Resources\WorkspaceResource;

class WorkspaceResourceCreatedListener
{
    public function handle(WorkspaceResourceCreatedEvent $event)
    {
        $workspace = $event->getWorkspace();
        if (in_array($workspace->getSkeleton()->getName(), ['Users', 'Parents'])) {
            $this->userCreated($workspace, $event->getResourceId());
        }

        if ($workspace->getSkeleton()->getName() == 'Announcements') {
            $this->announcementCreated($workspace, $event->getResourceId());
        }

        if ($workspace->getSkeleton()->getName() == 'Incidents') {
            $this->incidentCreated($workspace, $event->getResourceId());
        }
    }

    private function userCreated(WorkspaceResource $workspace, $userId)
    {
        $form = $workspace->getSkeleton()->getForm();
        $user = Service::userService()->getUserOrFail($userId);
        //если нужно отправить инвайт - отправляем
        if ($user->isActive() && ($invite = $form->getField('User.invite'))) {
            if ($invite->getValueValue()) {
                Service::userService()->sendInviteNotification($user);
            }
        }
    }

    private function announcementCreated($workspace, $announcementId)
    {
        /**
         * @TODO заменить сервисом нотификаций
         */
        $announcement = Announcement::find($announcementId);

        if ($announcement->active) {
            \Notification::send(
                Service::userService()->getBroadcastUser(),
                new NewAnnouncementNotification(Announcement::find($announcementId))
            );
        }
    }

    private function incidentCreated($workspace, $incidentId)
    {
        /**
         * @TODO заменить сервисом нотификаций
         */
        $incident = Incident::find($incidentId);

        \Notification::send(Service::studentService()->getStudentParents($incident->user), new IncidentNotification($incident));
    }
}
