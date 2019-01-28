<?php


namespace App\Udi\Actions\Workspaces\Users;

use App\Facades\Service;
use App\Udi\Actions\BaseAction;

class InviteAction extends BaseAction
{
    public function run($id)
    {
        Service::userService()->sendCredentialsNotification($id);
        $this->success('Інвайт успішно відправлено');
    }
}
