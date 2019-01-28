<?php


namespace App\Udi\Actions\Workspaces\Users;

use App\Udi\Actions\BaseAction;

class AuthorizeAction extends BaseAction
{
    public function run($id)
    {
        \Auth::loginUsingId($id);

        $this->redirect(route('home.index'));
    }
}
