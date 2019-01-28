<?php


namespace App\Udi\Nodes\Workspace;

use App\Udi\Nodes\BaseNode;

class FormFieldsNode extends BaseNode
{
    public function getPrimary()
    {
        foreach ($this->getFields() as $field) {
            if ($field->isPrimary()) {
                return $field;
            }
        }

        return false;
    }

    public function getValues()
    {
        $values = [];
        foreach ($this->getFields() as $field) {
            $value = $field->getValueValue();
            if (
                $field->isVirtual() ||
                ($field->isPassword() && !$value)
            ) {
                continue;
            }
            if (
                !$value &&
                $value !== 0 &&
                (
                    $field->isHandbook() ||
                    $field->isDate() ||
                    $field->isFile()
                )
            ) {
                $value = null;
            }
            $values[$field->getCodeValue()] = $value;
        }

        return $values;
    }

    public function validate()
    {
        $values = $originValues = $rules = $messages = [];
        foreach ($this->getFields() as $field) {
            $value = $field->getValueValue();
            if ($field->isPassword()) {
                $primary = $this->getPrimary();
                if (!$value && $primary->getValueValue()) {
                    continue;
                }
            }
            $originValues[$field->getCodeValue()] = $value;
            $code = $field->getCodeValue(true);
            $values[$code] = $value;
            $rules[$code] = $field->getValidationRules();
            $messages = array_merge($messages, $field->getValidationMessages($code));
        }
        $validator = \Validator::make($values, $rules, $messages, $originValues);

        return $validator;
    }

    public function setValues($values)
    {
        foreach ($this->getFields() as $field) {
            if ($field->isVirtual() || $field->isPassword()) {
                continue;
            }
            $code = $field->getCodeValue();
            if (isset($values[$code])) {
                $field->setValueValue($values[$code]);
            }
        }

        return $this;
    }

    public function setErrors(array $errors)
    {
        foreach ($this->getFields() as $field) {
            $code = $field->getCodeValue();
            if (isset($errors[$code])) {
                $field->setErrors($errors[$code]);
            } else {
                $field->clearErrors();
            }
        }

        return $this;
    }

    /**
     * @return FormHandbookFieldNode[]
     */
    public function getHandbooks()
    {
        $handbooks = [];
        foreach ($this->getFields() as $field) {
            if ($field->isHandbook()) {
                $handbooks[] = $field;
            }
        }

        return $handbooks;
    }

    /**
     * @return FormFileFieldNode[]
     */
    public function getFiles()
    {
        $files = [];
        foreach ($this->getFields() as $field) {
            if ($field->isFile()) {
                $files[] = $field;
            }
        }

        return $files;
    }

    public function getFieldCodes()
    {
        $codes = [];
        foreach ($this->getFields() as $field) {
            if ($field->isVirtual()) {
                continue;
            }
            $codes[] = $field->getCodeValue();
        }

        return $codes;
    }

    public function getField($code)
    {
        return $this->getNode($code);
    }

    /**
     * @return FormFieldNode[]
     */
    public function getFields()
    {
        /**
         * @var FormFieldNode[] $fields
         */
        $fields = $this->getNodes();

        return $fields;
    }
}
