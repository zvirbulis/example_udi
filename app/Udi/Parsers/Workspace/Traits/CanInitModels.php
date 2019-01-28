<?php


namespace App\Udi\Parsers\Workspace\Traits;

use App\Udi\Nodes\Workspace\FieldCodeNode;

trait CanInitModels
{
    protected function initModels(&$result, FieldCodeNode $code)
    {
        $models = $code->models;
        $matching = [];

        while (count($models) > 1) {
            $model = (object)array_shift($models);
            if (! isset($result['models'][$model->name]) || ! is_array($result['models'][$model->name])) {
                $result['models'][$model->name] = [];
            }

            $result['models'][$model->name]['foreignKey'] = $model->foreignKey;
            $result = &$result['models'][$model->name];
        }
        $model = array_shift($models);
        $result['models'][$model['name']]['foreignKey'] = $model['foreignKey'];
        $result['models'][$model['name']]['attributes'][] = $code->attribute;
        $matching[$model['name']][$code->attribute] = $code->origin;

        return $matching;
    }
}
