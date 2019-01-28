<?php


namespace App\Udi\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Udi\Http\Response;
use App\Udi\IoC;
use App\Udi\Resources\WorkspaceResource;
use Illuminate\Http\Request;

abstract class AbstractCustomWorkspaceController extends Controller
{
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var WorkspaceResource
     */
    protected $workspace;

    public function __construct(Request $request)
    {
        parent::__construct();

        $this->request = $request;
        $this->workspace = $this->workspace ?? str_replace('Controller', '', (new \ReflectionClass($this))->getShortName());
        $this->initWorkspace();
    }

    abstract public function load(Request $request);

    /**
     * @return array|bool
     */
    protected function getParentWorkspaceData()
    {
        return false;
    }

    private function initWorkspace()
    {
        $repository = IoC::schemaRepository();

        try{
            $this->workspace = new WorkspaceResource($this->workspace, $repository);

            if($workspacePair = $this->getParentWorkspaceData()){
                $this->workspace->withParentFormItem($workspacePair);
            }
        }
        catch (\Exception $e){
            return Response::exception($e);
        }
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function index()
    {
        try{
            $data = $this->workspace->build();

            return Response::ok($data);
        }
        catch( \Exception $e){
            return Response::exception($e);
        }
    }
}