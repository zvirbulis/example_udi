<?php


namespace App\Udi\Support;

use App\Udi\Nodes\Workspace\FieldCodeNode;
use Illuminate\Validation\Validator;

class Result
{
    private $status = false;
    private $data = [];
    private $errors = [];

    public static function validate(Validator $validator)
    {
        $result = new Result();
        if ($validator->fails()) {
            $result->setErrors($validator->errors()->toArray());
        } else {
            $result->setStatus(true);
        }

        return $result;
    }

    public function getData($key)
    {
        return $this->data[$key];
    }

    public function setData($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function setStatus($status)
    {
        $this->status = (bool)$status;

        return $this;
    }

    public function setErrors(array $messages)
    {
        foreach ($messages as $key => $value) {
            $key = FieldCodeNode::unformat($key);
            $this->errors[$key] = $value;
        }

        return $this;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function isFail()
    {
        return !$this->isSuccess();
    }

    public function isSuccess()
    {
        return $this->status;
    }
}
