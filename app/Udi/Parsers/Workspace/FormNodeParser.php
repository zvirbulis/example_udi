<?php


namespace App\Udi\Parsers\Workspace;

use App\Udi\Parsers\BaseNodeParser;
use App\Udi\Parsers\Workspace\Traits\CanInitModels;
use App\Udi\Nodes\BaseNode;
use App\Udi\Nodes\Workspace\FormNode;

class FormNodeParser extends BaseNodeParser
{
    use CanInitModels;

    /**
     * @var FormNode
     */
    protected $node;
    protected $parsers = [
        'order[]' => FieldCodeNodeParser::class,
        'fields' => FormFieldsNodeParser::class,
        'relations' => FormRelationsNodeParser::class,
        'actions' => ActionsNodeParser::class
    ];

    public function __construct()
    {
        $node = new FormNode();
        parent::__construct($node);
    }

    public function parse($data): BaseNode
    {
        $data['relations'] = $data['relations'] ?? [];

        parent::parse($data);

        foreach ($this->node->getFieldsOrder() as $fieldCode) {
            $this->node->matching = array_replace_recursive(
                $this->node->matching,
                $this->initModels($this->node->models, $fieldCode)
            );
        }

        return $this->node;
    }
}
