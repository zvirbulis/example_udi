<?php


namespace App\Udi\Nodes\Workspace;

use App\Udi\Interfaces\HandbookFieldInterface;
use App\Udi\Nodes\BaseNode;

class FormFieldNode extends BaseNode
{
    const VIS_NEW = 'new';
    const VIS_EDIT = 'edit';

    public function clearErrors()
    {
        return $this->setErrors(null);
    }

    public function setErrors($errors)
    {
        if ($this->hasNode('errors')) {
            $node = $this->getNode('errors');
        } else {
            $node = new BaseNode('errors');
            $this->addNode($node);
        }
        $node->flush();
        $node->setValue($errors);

        return $this;
    }

    public function getNameValue()
    {
        return $this->getNode('name')->getValue();
    }

    public function getCodeValue($format = false)
    {
        $code = $this->getNode('code')->getValue();
        if ($format) {
            $code = FieldCodeNode::format($code);
        }
        return $code;
    }

    public function validate()
    {
        if ($this->isReadonly()) {
            return true;
        }
        $rules = $this->getValidationRules();
        $code = $this->getCodeValue(true);
        $validator = \Validator::make(
            [$code => $this->getValue()],
            [$code => $rules],
            $this->getValidationMessages($code)
        );

        return $validator;
    }

    public function setValidationRules($rules)
    {
        $validation = $this->getValidation();
        if ($validation) {
            $validation->getNode('rules')->setValue($rules);
        }

        return $this;
    }

    public function getValidationRules()
    {
        $validation = $this->getValidation();
        if ($validation) {
            return $validation->getNode('rules')->getValue();
        }
        return '';
    }

    public function getValidationMessages($code)
    {
        $result = [];
        $validation = $this->getValidation();
        if ($validation) {
            $messages = $validation->getNode('messages')->toArray(true);
            $messages = $messages ?: [];
            foreach ($messages as $key => $message) {
                $result[$code.'.'.$key] = $message;
            }
        }
        return $result;
    }

    public function getValidation()
    {
        return $this->getNode('validation');
    }

    public function isPrimary()
    {
        return $this->hasNode('primary_key') && $this->getNode('primary_key')->getValue() === true;
    }

    public function isRequired()
    {
        return $this->hasNode('required') && $this->getNode('required')->getValue() === true;
    }

    public function isReadonly()
    {
        return $this->hasNode('readonly') && $this->getNode('readonly')->getValue() === true;
    }

    public function isHandbook()
    {
        return ($this instanceof HandbookFieldInterface);
    }

    public function isFile()
    {
        return ($this instanceof FormFileFieldNode);
    }

    public function isMultiple()
    {
        return $this->hasNode('multiple') && $this->getNode('multiple')->getValue() === true;
    }

    public function isPassword()
    {
        return $this->hasNode('type_data') && $this->getNode('type_data')->getValue() === 'password';
    }

    public function isDate()
    {
        return $this->hasNode('type_data') && $this->getNode('type_data')->getValue() === 'date';
    }

    public function isVirtual()
    {
        return $this->hasNode('virtual') && $this->getNode('virtual')->getValue() === true;
    }

    public function setValueValue($value)
    {
        if ($this->isMultiple() && !is_array($value)) {
            $value = [$value];
        }
        $this->requireNode('value')->flush()->setValue($value);

        return $this;
    }

    public function getValueValue()
    {
        return $this->requireNode('value')->toArray(true);
    }

    public function onlyNew()
    {
        return $this->getVisibility() === self::VIS_NEW;
    }

    public function onlyEdit()
    {
        return $this->getVisibility() === self::VIS_EDIT;
    }

    public function getVisibility()
    {
        return $this->hasNode('visibility') ? $this->getNode('visibility')->getValue() : null;
    }

    public function getFormat()
    {
        return $this->getNode('format');
    }
}
