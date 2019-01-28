<?php


namespace App\Udi\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Udi\Http\Response;
use App\Udi\IoC;
use App\Udi\Nodes\Workspace\FormFileFieldNode;
use App\Udi\Resources\WorkspaceResource;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FileController extends ResourceController
{
    const FIELD_NAME = 'files';

    public function upload(Request $request, $workspace)
    {
        $fieldCode = $request->get('field');
        $repository = IoC::schemaRepository();
        try {
            $workspace = new WorkspaceResource($workspace, $repository);

            $this->access($workspace);

            $form = $workspace->getSkeleton()->getForm();
            /**
             * @var FormFileFieldNode $field
             */
            $field = $form->getField($fieldCode);
            $uploaderParams = $field->getUploader()->toArray(true);
            $files = isset($uploaderParams['validation'])
                ? $this->validateFiles($request, $uploaderParams['validation'])
                : $request->allFiles();

            $uploader = IoC::uploader($uploaderParams['code'], $uploaderParams);
            $data = $uploader->upload($files[self::FIELD_NAME]);
        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                return \Response::errorByException($e);
            }
            return Response::exception($e);
        }

        return Response::ok($data);
    }

    protected function validateFiles(Request $request, $validationParams)
    {
        $rules = isset($validationParams['rules']) ? $this->rules($validationParams['rules']) : '';
        $messages = isset($validationParams['messages']) ? $this->messages($validationParams['messages']) : [];

        return $this->validate($request, $rules, $messages);
    }

    private function rules($rules)
    {
        return [self::FIELD_NAME . '.*' => $rules];
    }

    private function messages($messages)
    {
        $result = [];
        foreach ($messages as $param => $message) {
            $result[self::FIELD_NAME . '.*' . $param] = $message;
        }

        return $result;
    }

    public function delete(Request $request, $workspace, $file)
    {
        $fieldCode = $request->get('field');
        $repository = IoC::schemaRepository();
        try {
            $workspace = new WorkspaceResource($workspace, $repository);

            $this->access($workspace);

            $form = $workspace->getSkeleton()->getForm();
            /**
             * @var FormFileFieldNode $field
             */
            $field = $form->getField($fieldCode);
            $uploaderParams = $field->getUploader()->toArray(true);

            $uploader = IoC::uploader($uploaderParams['code'], $uploaderParams);
            $uploader->deleteFile($file);
        } catch (\Exception $e) {
            return Response::exception($e);
        }

        return Response::ok([]);
    }
}
