<?php


namespace App\Udi\Actions\Workspaces\Users;

use App\Udi\Actions\BaseAction;

class StuffListAction extends BaseAction
{
    public function run($id)
    {
        $this->redirect(route('teachers.list'));
    }
}
