<?php


namespace App\Udi\Actions\Workspaces\Users;

use App\Facades\Service;
use App\Traits\AuthenticatesUsers;
use App\Udi\Actions\BaseAction;

class RestoreLoginAction extends BaseAction
{
    use AuthenticatesUsers;

    public function run($id)
    {
        $user = Service::userService()->getUserOrFail($id);
        request()->offsetSet($this->username(), $user->getAttribute($this->username()));
        $key = $this->throttleKey(request());

        $this->limiter()->clear($key);
        $user->update(['active' => 'Y']);

        $this->success('Доступ до сайту успішно відновлено');
    }
}
