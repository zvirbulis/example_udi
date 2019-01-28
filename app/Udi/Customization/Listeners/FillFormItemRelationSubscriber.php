<?php


namespace App\Udi\Customization\Listeners;

use App\Factories\UsersFactory;
use App\Udi\Customization\Models\User;
use App\Udi\Events\FillFormItemRelation;
use App\Udi\Nodes\Workspace\FormRelationNode;

class FillFormItemRelationSubscriber
{
    public function handle(FillFormItemRelation $event)
    {
        $relation = $event->getRelation();
        if ($relation->getCodeValue() == '#USER_PROFILES#') {
            $this->setCorrectUserProfile($relation, $event->getItem());
        }
    }

    private function setCorrectUserProfile(FormRelationNode $relation, User $user)
    {
        $relation->setCodeValue('');

        $user->setAttribute('id', $user->getAttribute('User.id'));
        $user->setAttribute('group_id', $user->getAttribute('User.group_id'));
        try {
            $user = app(UsersFactory::class)->extendUser($user);
            if (method_exists($user, 'profile')) {
                if ($profile = $user->profile()) {
                    $profile = (new \ReflectionClass($profile->getRelated()))->getShortName();
                    $relation->setCodeValue($profile.'s');
                }
            }
        } catch (\Exception $e) {
        }
    }
}
