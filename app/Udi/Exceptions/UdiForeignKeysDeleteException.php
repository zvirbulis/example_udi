<?php


namespace App\Udi\Exceptions;

use App\Udi\Nodes\BaseNode;

class UdiForeignKeysDeleteException extends UdiWorkspaceResourceException
{
    protected $message = "Неможливо видалити обрані записи оскільки вони пов'язані з іншими сутностями";

    protected function processData(BaseNode $node)
    {
        $value = $node->toArray(true);
        if(is_array($value)){
            $this->message = "Неможливо видалити обрані записи, оскільки вони пов'язані з іншими сутностями: ";
            $this->message .= implode("\n", $value);
        }
        else{
            $this->message = $value;
        }

        return parent::processData($node);
    }
}
