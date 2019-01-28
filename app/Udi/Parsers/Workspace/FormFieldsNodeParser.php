<?php


namespace App\Udi\Parsers\Workspace;

use App\Udi\Parsers\BaseNodeParser;
use App\Udi\Nodes\BaseNode;
use App\Udi\Nodes\Workspace\FormFieldsNode;

class FormFieldsNodeParser extends BaseNodeParser
{
    public function __construct()
    {
        $node = new FormFieldsNode('fields');

        parent::__construct($node);
    }

    public function parse($data): BaseNode
    {
        foreach ($data as $key => $value) {
            if ($this->isHandbook($value)) {
                $this->parsers[$key] = FormHandbookFieldNodeParser::class;
            } elseif ($this->isFile($value)) {
                $this->parsers[$key] = FormFileFieldNodeParser::class;
            } else {
                $this->parsers[$key] = FormFieldNodeParser::class;
            }
        }

        return parent::parse($data);
    }

    private function isHandbook($data)
    {
        return (isset($data['type_element']) && $data['type_element'] == 'select');
    }

    private function isFile($data)
    {
        return (isset($data['type_element']) && $data['type_element'] == 'file');
    }
}
