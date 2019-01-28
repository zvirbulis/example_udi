<?php


namespace App\Udi\Customization;

use App\Exceptions\Services\FileServiceException;
use App\Services\FileService;
use App\Udi\Exceptions\UdiException;
use App\Udi\Uploaders\BaseUploader;
use Illuminate\Http\UploadedFile;

class FileUploader extends BaseUploader
{
    private $fileService;

    public function __construct(string $disk = '', string $dir = '', bool $subdir = false)
    {
        parent::__construct($disk, $dir, $subdir);

        $this->fileService = new FileService([
            'upload_folder_path' => $dir,
            'storage_name' => $disk,
            'use_sub_folders' => true
        ]);
    }

    public function uploadFile(UploadedFile $file)
    {
        try {
            $file = $this->fileService->saveFile($file, true);
        } catch (FileServiceException $e) {
            throw new UdiException($e->getMessage());
        }

        return $file;
    }

    public function deleteFile($fileId)
    {
        try {
            $this->fileService->deleteFile($fileId);
        } catch (FileServiceException $e) {
            throw new UdiException($e->getMessage());
        }
    }
}
