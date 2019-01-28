<?php


namespace App\Udi\Actions\Workspaces\Users;


use App\Udi\Actions\BaseAction;

class ProfileAction extends BaseAction
{
    public function run($id)
    {
        $this->redirect(route('users.index', $id));
    }
}