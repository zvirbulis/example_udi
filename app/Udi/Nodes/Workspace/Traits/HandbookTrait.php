<?php


namespace App\Udi\Nodes\Workspace\Traits;

use App\Udi\Nodes\BaseNode;

trait HandbookTrait
{
    public function setAvailableValues(array $values)
    {
        $keyField = $this->getKeyField();
        $valueField = $this->getValueField();
        $availableValues = $this->getAvailableValues()->toArray(true);
        foreach ($values as $value) {
            $value = (array)$value;
            $availableValue = [
                'id' => $value[$keyField],
                'label' => $value[$valueField]
            ];
            if($this->hasUrl()){
                $availableValue['url'] = str_replace(
                    ['$id', '$label'],
                    [$availableValue['id'], $availableValue['label']],
                    $this->getUrl()
                );
            }
            $availableValues[] = $availableValue;
        }

        $this->getAvailableValues()->flush()->setValue($availableValues);

        return $this;
    }

    public function getKeyField()
    {
        return $this->getSettings()->getNode('id')->getValue();
    }

    public function getValueField()
    {
        return $this->getSettings()->getNode('label')->getValue();
    }

    public function getAvailableValues()
    {
        return $this->requireNode('select_values');
    }

    public function getUrl()
    {
        return $this->hasUrl() ? $this->getSettings()->getNode('url')->getValue() : false;
    }

    public function hasUrl()
    {
        return $this->getSettings()->getNode('url') !== false;
    }

    public function getSort()
    {
        return $this->hasSort() ?  $this->getSettings()->getNode('sort')->getValue() : false;
    }

    public function hasSort()
    {
        return $this->getSettings()->getNode('sort') !== false;
    }

    /**
     * @return BaseNode | false
     */
    public function getSettings()
    {
        return $this->getNode('select_settings');
    }
}
