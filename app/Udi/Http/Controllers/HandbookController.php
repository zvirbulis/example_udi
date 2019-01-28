<?php


namespace App\Udi\Http\Controllers;

use App\Udi\Http\Response;
use App\Udi\IoC;
use App\Udi\Resources\WorkspaceResource;
use App\Udi\Builders\HandbookBuilder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class HandbookController extends ResourceController
{
    public function index(Request $request, $workspace)
    {
        $repository = IoC::schemaRepository();
        try {
            $workspace = new WorkspaceResource($workspace, $repository);

            $this->access($workspace);

            $handbook = HandbookBuilder::createByWorkspaceNode($workspace->getSkeleton(), $request->get('_field'));
            $data = $handbook
                ->withRequest($request)
                ->get()
                ->toArray();

            return Response::ok($data);
        } catch (\Exception $e) {
            return Response::exception($e);
        }
    }
}
