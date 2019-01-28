<?php


namespace App\Udi\Uploaders;

use App\Udi\Exceptions\UdiException;
use Illuminate\Http\UploadedFile;

class BaseUploader
{
    protected $disk;
    protected $dir;
    protected $subdir;
    /**
     * FileUploader constructor
     *
     * @param $disk
     * @param $dir
     */
    public function __construct($disk = '', $dir = '', $subdir = false)
    {
        $this->disk = \Storage::disk($disk);
        $this->dir = $dir;
        $this->subdir = $subdir;
    }

    public function upload(array $files)
    {
        $result = [];
        foreach ($files as $file) {
            $result[] = $this->uploadFile($file);
        }

        return $result;
    }

    public function uploadFile(UploadedFile $file)
    {
        $dir = $this->dir;
        if ($this->subdir) {
            $dir.= mb_substr(0, 3, $file->getFilename());
        }

        $path = $this->disk->putFile($dir, $file);
        if (!$path) {
            throw new UdiException("Файл {$file->getFilename()} не збережено");
        }

        return $path;
    }

    public function deleteFile($pathInDisk)
    {
        if (!$this->disk->delete($pathInDisk)) {
            throw new UdiException('Файл зі шляхом "'.$pathInDisk.'" не видалено');
        }
    }
}
