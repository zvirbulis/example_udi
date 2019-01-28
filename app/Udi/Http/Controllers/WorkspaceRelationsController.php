<?php


namespace App\Udi\Http\Controllers;

use App\Udi\Http\Traits\LoadHomeTrait;
use App\Udi\IoC;
use App\Udi\Resources\WorkspaceResource;
use Illuminate\Http\Request;
use App\Udi\Http\Response;

class WorkspaceRelationsController extends ResourceController
{
    use LoadHomeTrait;

    protected $workspaces = [];
    protected $ids = [];

    private $debug = false;

    public function __construct(Request $request)
    {
        $this->middleware(function (Request $request, $next) {
            if ($request->ajax() || $this->debug) {
                return $next($request);
            }
            return $next($this->processRequest($request));
        });

        $this->initRelations($request);
    }

    protected function initRelations(Request $request)
    {
        $workspaces = [$request->workspace];
        $ids = [$request->id];
        $relations = explode('/', $request->relations);
        foreach ($relations as $i => $value) {
            if (($i + 1) % 2 === 0) {
                $ids[] = $value;
            } else {
                $workspaces[] = $value;
            }
        }
        $this->workspaces = $workspaces;
        $this->ids = $ids;
    }

    public function index(Request $request)
    {
        list($workspace, $id) = array_values($this->getSelfWorkspacePair());
        if ($id) {
            return $this->show($request, $workspace, $id);
        }

        $repository = IoC::schemaRepository();
        try {
            $workspace = new WorkspaceResource($workspace, $repository);
            $this->access($workspace);

            $data = $workspace
                ->withListItems()
                ->withRequest($request, $id)
                ->withParentFormItem($this->getParentWorkspacePair())
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
                ->withRequest($request, $id)
                ->withParentFormItem($this->getParentWorkspacePair())
                ->withFormItem($id)
                ->build();

            return Response::ok($data);
        } catch (\Exception $e) {
            return Response::exception($e);
        }
    }

    public function create(Request $request)
    {
        list($workspace, $id) = array_values($this->getSelfWorkspacePair());

        $repository = IoC::schemaRepository();
        try {
            $workspace = new WorkspaceResource($workspace, $repository);
            $this->access($workspace);

            $workspace->withParentFormItem($this->getParentWorkspacePair())->build();
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

    public function update(Request $request)
    {
        list($workspace, $id) = array_values($this->getSelfWorkspacePair());

        $repository = IoC::schemaRepository();
        try {
            $workspace = new WorkspaceResource($workspace, $repository);
            $this->access($workspace);

            $workspace->withParentFormItem($this->getParentWorkspacePair())->build();
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
                ->withFormItem($result->getData('id'))
                ->build();

            return Response::ok($data);
        } catch (\Exception $e) {
            return Response::exception($e);
        }
    }

    public function delete(Request $request)
    {
        if ($request->has('item_ids')) {
            return $this->deleteMany($request);
        }

        list($workspace, $id) = array_values($this->getSelfWorkspacePair());

        $repository = IoC::schemaRepository();
        try {
            $workspace = new WorkspaceResource($workspace, $repository);
            $this->access($workspace);

            //для удаления pivot-сущностей
            $workspace->withParentFormItem($this->getParentWorkspacePair())->build();
            $workspace->delete($id);
            $data = $workspace
                ->withRequest($request)
                ->withListItems()
                ->build();

            return Response::ok($data);
        } catch (\Exception $e) {
            return Response::exception($e);
        }
    }

    public function deleteMany(Request $request)
    {
        list($workspace, $id) = array_values($this->getSelfWorkspacePair());

        $repository = IoC::schemaRepository();
        try {
            $workspace = new WorkspaceResource($workspace, $repository);
            $this->access($workspace);

            $ids = $request->get('item_ids');
            if (is_array($ids)) {
                //для удаления pivot-сущностей
                $workspace->withParentFormItem($this->getParentWorkspacePair())->build();
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

    public function getParentWorkspacePair()
    {
        return $this->getWorkspacePair(-2);
    }

    public function getSelfWorkspacePair()
    {
        return $this->getWorkspacePair(-1);
    }

    protected function getWorkspacePair($index)
    {
        if ($index < 0) {
            $index += count($this->workspaces);
        }
        $workspace = isset($this->workspaces[$index]) ? $this->workspaces[$index] : null;
        $id = isset($this->ids[$index]) ? $this->ids[$index] : null;

        return [
            'workspace' => $workspace,
            'id' => $id
        ];
    }
}
