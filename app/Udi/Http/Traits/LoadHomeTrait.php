<?php


namespace App\Udi\Http\Traits;

use App\Facades\Service;
use Illuminate\Http\Request;

trait LoadHomeTrait
{
    protected $data = [];

    protected function processRequest(Request $request)
    {
        $action = __CLASS__.'@home';
        $route  = $request->route();
        $routeAction = array_merge($route->getAction(), [
            'uses' => $action,
            'controller' => $action
        ]);
        $route->setAction($routeAction);
        $route->controller = false;

        return $request;
    }

    public function home(Request $request)
    {
        $this->initData();

        $this->data['init_url'] = json_encode($request->fullUrl());

        return view('handbook.index', $this->data);
    }

    public function initData()
    {
        $user = \Auth::user();
        $this->data['header']['user'] = $user->toArray();
        $this->data['header']['user']['personal_photo'] = $user->personalPhoto ? $user->personalPhoto->src : '';
        $this->data['header']['pageTitle'] = 'Довідники';
        $this->data['header']['breadcrumbs'] = [[
            'name' => 'Довідники',
            'url' => route('udi.navbar.index')
        ]];
        $this->data['header']['profileUrl'] = $user ? Service::userService()->getUserProfileUrl($user) : '';
        //$this->data['header']['messages_count'] = $user ? Service::messagesService()->getUnreadMessageCount($user->id): 0;
        $this->data['header']['notify'] = [
            'type' => session('notify.type', 'success'),
            'text' => session('notify.text', '')
        ];
    }
}
