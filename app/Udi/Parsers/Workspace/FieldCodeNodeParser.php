<?php


namespace App\Udi\Parsers\Workspace;

use App\Udi\Parsers\BaseNodeParser;
use App\Udi\Nodes\BaseNode;
use App\Udi\Nodes\Workspace\FieldCodeNode;

class FieldCodeNodeParser extends BaseNodeParser
{
    /**
     * @var FieldCodeNode
     */
    protected $node;

    public function __construct()
    {
        $node = new FieldCodeNode();
        parent::__construct($node);
    }

    public function parse($data) : BaseNode
    {
        $models = explode('.', $data);
        $attribute = array_pop($models);

        foreach ($models as $model) {
            $model = $this->parseModel($model);
            $this->node->models[$model['name']] = $model;
        }

        $this->node->origin = $data;
        $this->node->attribute = $attribute;

        return $this->node;
    }

    protected function parseModel($model)
    {
        $foreignKey = null;
        if (strpos($model, ':') !== false) {
            list($name, $foreignKey) = explode(':', $model);
        } else {
            $name = $model;
        }

        return compact('name', 'foreignKey');
    }
}
