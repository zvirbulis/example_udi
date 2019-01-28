<?php


namespace App\Udi\Parsers;

use App\Udi\Interfaces\ParserInterface;
use App\Udi\Nodes\BaseNode;

abstract class BaseNodeParser implements ParserInterface
{
    /**
     * @var BaseNode
     */
    protected $node;
    /**
     * @var ParserInterface
     */
    protected $parsers = [];

    public function __construct(BaseNode $node)
    {
        $this->node = $node;
    }

    public function getNode()
    {
        return $this->node;
    }

    public function parse($data) : BaseNode
    {
        $this->parseNodes($data, $this->node);
        return $this->node;
    }

    protected function parseNodes(array $data, BaseNode $node, $prefix = null)
    {
        $prefix = $prefix ?? $node->getName();
        foreach ($data as $key => $values) {
            $parserCode = $this->createParserCode($prefix, $key, $values);
            $parser = $this->getParser($parserCode, $key);
            $parsedValue = $parser
                ? $parser->parse($values)
                : $values;
            if (!$parsedValue instanceof BaseNode) {
                $values = $parsedValue;
                $parsedValue = new BaseNode($key);
            }
            $node->addNode($parsedValue);
            if (!$parser && is_array($values)) {
                $this->parseNodes($values, $parsedValue, $parserCode);
            } else {
                $parsedValue->setValue($values);
            }
        }
    }

    /**
     * @param $parserCode
     * @param $key
     * @return ParserInterface | false
     */
    private function getParser($parserCode, $key)
    {
        if (empty($this->parsers)) {
            return false;
        }

        $parts = explode('.', $parserCode);
        unset($parts[0]);
        $parserCode = implode('.', $parts);
        if (
            isset($this->parsers[$parserCode]) &&
            class_exists($this->parsers[$parserCode])
        ) {
            $parser = new $this->parsers[$parserCode];
            if ($parser instanceof BaseNodeParser) {
                $parser->getNode()->setName($key);
            }

            return $parser;
        }

        return false;
    }

    private function createParserCode($prefix, $key, $values)
    {
        $key = is_int($key) ? '[]' : '.'.$key;

        return $prefix.$key;
    }
}
