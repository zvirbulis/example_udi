<?php


namespace App\Udi\Actions\Workspaces\Users;

use App\Udi\Actions\BaseAction;

class ChattingAction extends BaseAction
{
    public function run($id)
    {
        if (config('messenger.enable')) {
            $this->redirect(route('messages.chat.user.create', ['user' => $id]));
        } else {
            return $this->error('Сервіс чат-повідомлень відключений');
        }
    }
}
