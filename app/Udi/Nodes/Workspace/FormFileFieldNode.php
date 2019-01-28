<?php


namespace App\Udi\Nodes\Workspace;

class FormFileFieldNode extends FormFieldNode
{
    public function __construct($name = 'file')
    {
        parent::__construct($name);
    }

    public function getKeyField()
    {
        return $this->getSettings()->requireNode('id')->getValue();
    }

    public function getNameField()
    {
        return $this->getSettings()->requireNode('label')->getValue();
    }

    public function getSrcField()
    {
        return $this->getSettings()->requireNode('src')->getValue();
    }

    public function getSizeField()
    {
        return $this->getSettings()->requireNode('size')->getValue();
    }

    public function initUploaderUrl($workspace)
    {
        if (!$this->getUploaderUrl()) {
            $this->setUploaderUrl('/udi/files/'.$workspace.'?field='.$this->getCodeValue());
        }

        return $this;
    }

    public function getUploaderUrl()
    {
        $uploader = $this->getUploader()->getNode('url');
        if ($uploader) {
            return $uploader->getValue();
        }

        return false;
    }

    public function setUploaderUrl($dir)
    {
        $uploader = $this->getUploader();
        $uploader->requireNode('url')->setValue($dir);

        return $this;
    }

    public function getDisk()
    {
        $disk = $this->getUploader()->getNode('disk');
        if ($disk) {
            return $disk->getValue();
        }

        return false;
    }

    public function getDir()
    {
        $dir = $this->getUploader()->getNode('dir');
        if ($dir) {
            return $dir->getValue();
        }

        return false;
    }

    public function isNeedSubdir()
    {
        $subdir = $this->getUploader()->getNode('subdir');
        if ($subdir) {
            return $subdir->getValue();
        }

        return false;
    }

    public function getUploader()
    {
        return $this->requireNode('uploader');
    }

    public function setFileValues(array $values)
    {
        $fileValues = $this->getFileValues();
        $fileValues->flush()->setValue($values);

        return $this;
    }

    public function getFileValues()
    {
        return $this->requireNode('file_values');
    }

    public function getSettings()
    {
        return $this->getNode('file_settings');
    }
}
