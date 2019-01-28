<?php
namespace App\Udi\Customization\Rules;

use App\Udi\Builders\QueryBuilder;
use App\Udi\IoC;
use App\Udi\Nodes\Workspace\FieldCodeNode;
use Illuminate\Validation\Validator;

class StrictUnique implements \Illuminate\Contracts\Validation\Rule
{
    public static function validate($attribute, $value, $parameters, Validator $validator)
    {
        $rule = new static();
        $customAttributes = ($validator instanceof Validator) ? $validator->customAttributes : [];

        return $rule->passes($attribute, $value, $parameters, $customAttributes);
    }

    public function passes($attribute, $value, $parameters = null, array $fieldValues = [])
    {
        $field = FieldCodeNode::unformat($attribute);
        $query = (new QueryBuilder())
            ->initModel($field)
            ->where($field, '=', $value);
        $parameters = $this->normalizeParams($parameters);
        foreach ($parameters as $k => $v) {
            if ($k === 'except') {
                $model = IoC::model($query->getModel());
                $query->where($query->getModel().'.'.$model->getKeyName(), '!=', $v);
            } else {
                if (is_null($v) && isset($fieldValues[$k])) {
                    $v = $fieldValues[$k];
                }
                $query->where($k, '=', $v);
            }
        }

        return $query->build()->count() === 0;
    }

    private function normalizeParams($params)
    {
        $result = [];
        if (!is_array($params)) {
            return $result;
        }
        foreach ($params as $param) {
            $paramParts = explode('=', $param);
            $field = $paramParts[0];
            $value = isset($paramParts[1]) ? $paramParts[1] : null;
            $result[$field] = $value;
        }

        return $result;
    }

    public function message()
    {
        return 'Запис з такими параметрами вже є у системі';
    }
}
