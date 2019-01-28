<?php


namespace App\Udi\Nodes\Workspace;

use App\Udi\IoC;
use App\Udi\Nodes\BaseNode;

class ActionFieldNode extends BaseNode
{
    public function run(array $id, array $params = [])
    {
        $type = $this->getType();
        if ($type === 'custom') {
            $type = $this->getCodeValue();
        }
        $action = IoC::action($type, $params, $this);

        foreach ($id as $i) {
            $action->run($i);
            if (!$this->isMultiple()) {
                break;
            }
        }
    }

    public function getNameValue()
    {
        return $this->requireNode('name')->getValue();
    }

    public function getCodeValue()
    {
        return $this->requireNode('code')->getValue();
    }

    public function getType()
    {
        return $this->requireNode('type')->getValue();
    }

    public function isMultiple()
    {
        return $this->hasNode('multiple') && $this->getNode('multiple')->getValue() === true;
    }

    public function getSetting($key)
    {
        $settings = $this->getSettings();

        return isset($settings[$key]) ? $settings[$key] : null;
    }

    public function getSettings()
    {
        $settings = $this->getNode('settings');

        return ($settings ? $settings->toArray(true) : []);
    }

    public function getRedirectLink()
    {
        $link = $this->requireNode('_link')->getValue();

        return $link ? $link : '';
    }

    public function setRedirectLink($link)
    {
        $this->requireNode('_link')->setValue($link);
        $this->requireNode('redirect')->setValue(true);

        return $this;
    }

    public function setSuccessMessage($message)
    {
        $this->requireNode('success')->setValue($message);

        return $this;
    }

    public function getSuccessMessage()
    {
        $this->requireNode('success')->getValue();
    }

    public function setErrorMessage($message)
    {
        $this->requireNode('error')->setValue($message);

        return $this;
    }

    public function getErrorMessage()
    {
        return $this->requireNode('error')->getValue();
    }
}
