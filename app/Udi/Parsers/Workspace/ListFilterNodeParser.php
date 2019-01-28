<?php


namespace App\Udi\Parsers\Workspace;

use App\Udi\Nodes\BaseNode;
use App\Udi\Nodes\Workspace\ListFilterNode;
use App\Udi\Parsers\BaseNodeParser;

class ListFilterNodeParser extends BaseNodeParser
{
    public function __construct(BaseNode $node = null)
    {
        $node = $node ?? new ListFilterNode('filter');
        parent::__construct($node);
    }

    public function parse($data): BaseNode
    {
        $this->initFieldsParser($data);
        $filter = parent::parse($data);

        $order = $filter->requireNode('order');
        if (!$order->getValue()) {
            $order->setValue([]);
        }

        return $filter;
    }

    private function initFieldsParser($data)
    {
        if (!isset($data['fields'])) {
            $data['fields'] = [];
        }

        foreach ($data['fields'] as $key => $value) {
            if ($this->isHandbook($value)) {
                $this->parsers['fields.'.$key] = ListFilterHandbookNodeParser::class;
            } else {
                $this->parsers['fields.'.$key] = ListFilterFieldNodeParser::class;
            }
        }
    }

    private function isHandbook($data)
    {
        return (isset($data['type_element']) && $data['type_element'] == 'select');
    }
}
