<?php


namespace App\Udi\Http\Controllers;

use App\Udi\Http\Response;
use App\Udi\IoC;
use App\Udi\Http\Traits\LoadHomeTrait;
use App\Udi\Resources\NavbarResource;
use Illuminate\Http\Request;

class NavbarController extends ResourceController
{
    use LoadHomeTrait;

    protected $debug = false;

    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {
            if ($request->ajax() || $this->debug) {
                return $next($request);
            }
            return $next($this->processRequest($request));
        });
    }

    public function home(Request $request)
    {
        $this->initData();

        try{
            $repository = IoC::schemaRepository();
            $navbar = new NavbarResource($repository);

            $this->access($navbar);

            $navbarData = $this->processData($navbar->getDataSchema()->toArray());
            $this->data['navbar_items'] = $navbarData['groups'];

            return view('handbook.navbar', $this->data);
        }
        catch(\Exception $e){
            return Response::exception($e);
        }
    }

    private function sort($a, $b)
    {
        return strip_tags($a['name']) <=> strip_tags($b['name']);
    }


    public function index(Request $request)
    {
        try{
            $repository = IoC::schemaRepository();
            $navbar = new NavbarResource($repository);

            $this->access($navbar);

            return $this->resource($navbar);
        }
        catch (\Exception $e){
            return Response::exception($e);
        }

    }
}
