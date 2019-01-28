<?php


namespace App\Udi\Parsers\Workspace;

use App\Udi\Parsers\BaseNodeParser;
use App\Udi\Parsers\Workspace\Traits\CanInitModels;
use App\Udi\Nodes\BaseNode;
use App\Udi\Nodes\Workspace\FieldCodeNode;
use App\Udi\Nodes\Workspace\ListFieldsNode;

class ListFieldsNodeParser extends BaseNodeParser
{
    use CanInitModels;

    protected $parsers = [
        '' => ListFieldNodeParser::class
    ];

    public function __construct()
    {
        $node = new ListFieldsNode();
        parent::__construct($node);
    }

    public function parse($data): BaseNode
    {
        /**
         * @var ListFieldsNode $fields
         */
        $fields = parent::parse($data);

        foreach ($fields->getFields() as $field) {
            /**
             * @var FieldCodeNode $code
             */
            $code = $field->getNode('code');
            $fields->matching = array_replace_recursive(
                $fields->matching,
                $this->initModels($fields->models, $code)
            );
        }

        return $fields;
    }
}
