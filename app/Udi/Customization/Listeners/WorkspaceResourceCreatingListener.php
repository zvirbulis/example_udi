<?php


namespace App\Udi\Customization\Listeners;

use App\Udi\Events\WorkspaceResourceCreatingEvent;
use App\Udi\Resources\WorkspaceResource;

class WorkspaceResourceCreatingListener
{
    public function handle(WorkspaceResourceCreatingEvent $event)
    {
        $workspace = $event->getWorkspace();
        if (in_array($workspace->getSkeleton()->getName(), ['Users', 'Parents'])) {
            $this->userCreating($workspace);
        }
    }

    private function userCreating(WorkspaceResource $workspace)
    {
        $form = $workspace->getSkeleton()->getForm();
        $active = $form->getField('User.active');
        $invite = $form->getField('User.invite');
        if (
            $active && $active->getValueValue() &&
            $invite && $invite->getValueValue()
        ) {
            $name = $form->getField('User.name');
            $pass = $form->getField('User.password');
            $pass_confirmation = $form->getField('User.password_confirmation');

            if (!$name->getValueValue()) {
                $name->setValueValue($form->getField('User.email')->getValueValue());
            }
            if (!$pass->getValueValue()) {
                $password = str_random();
                $pass->setValueValue($password);
                $pass_confirmation->setValueValue($password);
            }
        }
    }
}
