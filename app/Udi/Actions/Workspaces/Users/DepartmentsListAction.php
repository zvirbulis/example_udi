<?php


namespace App\Udi\Actions\Workspaces\Users;

use App\Udi\Actions\BaseAction;

class DepartmentsListAction extends BaseAction
{
    public function run($id)
    {
        $this->redirect(route('teachers.departments'));
    }
}
