<?php


namespace App\Udi\Http\Controllers;

use App\Udi\Http\Response;
use App\Udi\Http\Traits\LoadHomeTrait;
use App\Udi\IoC;
use App\Udi\Nodes\WorkspaceNode;
use App\Udi\Parsers\WorkspaceNodeParser;
use App\Udi\Resources\WorkspaceResource;
use Illuminate\Http\Request;
use App\Facades\Service;
use Symfony\Component\HttpFoundation\ParameterBag;

class WorkspaceController extends ResourceController
{
    use LoadHomeTrait;

    private $debug = false;

    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {
            if ($request->ajax() || $this->debug) {
                return $next($request);
            }
            return $next($this->processRequest($request));
        });
    }

    public function index(Request $request, $workspace)
    {
        $repository = IoC::schemaRepository();
        try {
            $workspace = new WorkspaceResource($workspace, $repository);
            $this->access($workspace);

            $data = $workspace
                ->withListItems()
                ->withRequest($request)
                ->build();

            return Response::ok($data);
        } catch (\Exception $e) {
            return Response::exception($e);
        }
    }

    public function show(Request $request, $workspace, $id)
    {
        $repository = IoC::schemaRepository();
        try {
            $workspace = new WorkspaceResource($workspace, $repository);
            $this->access($workspace);

            $data = $workspace
                ->withListItems()
                ->withRequest($request)
                ->withFormItem($id)
                ->build();

            return Response::ok($data);
        } catch (\Exception $e) {
            return Response::exception($e);
        }
    }

    public function create(Request $request, $workspace)
    {
        $repository = IoC::schemaRepository();
        try {
            $workspace = new WorkspaceResource($workspace, $repository);
            $this->access($workspace);

            $dataSchema = $repository->newInstance('data', $request->getContent());
            $result = $workspace->create($dataSchema);
            if ($result->isFail()) {
                $data = $workspace
                    ->withListItems()
                    ->withFormErrors($result->getErrors())
                    ->build();

                return Response::badRequest($data);
            }
            $data = $workspace
                ->withListItems()
                ->withFormItem($result->getData('id'))
                ->build();

            return Response::ok($data);
        } catch (\Exception $e) {
            return Response::exception($e);
        }
    }

    public function update(Request $request, $workspace, $id)
    {
        $repository = IoC::schemaRepository();
        try {
            $workspace = new WorkspaceResource($workspace, $repository);
            $this->access($workspace);

            $dataSchema = $repository->newInstance('data', $request->getContent());
            $result = $workspace->update($id, $dataSchema);
            if ($result->isFail()) {
                $data = $workspace
                    ->withListItems()
                    ->withFormErrors($result->getErrors())
                    ->build();

                return Response::badRequest($data);
            }
            $data = $workspace
                ->withListItems()
                ->withFormItem($id)
                ->build();

            return Response::ok($data);
        } catch (\Exception $e) {
            return Response::exception($e);
        }
    }

    public function deleteMany(Request $request, $workspace)
    {
        $repository = IoC::schemaRepository();
        try {
            $workspace = new WorkspaceResource($workspace, $repository);
            $this->access($workspace);

            $ids = $request->get('item_ids');
            if (is_array($ids)) {
                $workspace->deleteMany($ids);
                $data = $workspace
                    ->withListItems()
                    ->withRequest($request)
                    ->build();

                return Response::ok($data);
            }
        } catch (\Exception $e) {
            return Response::exception($e);
        }
    }

    public function delete(Request $request, $workspace, $id)
    {
        $repository = IoC::schemaRepository();
        try {
            $workspace = new WorkspaceResource($workspace, $repository);
            $this->access($workspace);

            $workspace->delete($id);
            $data = $workspace
                ->withListItems()
                ->withRequest($request)
                ->build();

            return Response::ok($data);
        } catch (\Exception $e) {
            return Response::exception($e);
        }
    }
}
