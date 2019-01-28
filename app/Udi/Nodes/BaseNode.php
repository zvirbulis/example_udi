<?php


namespace App\Udi\Nodes;

class BaseNode
{
    private $name;
    private $value;
    /**
     * @var BaseNode[]
     */
    private $nodes = [];
    /**
     * @var BaseNode
     */
    private $parent;

    public function __construct($name = null)
    {
        $this->setName($name);
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setParent(BaseNode $node)
    {
        $this->parent = $node;

        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function hasNodes()
    {
        return !empty($this->nodes);
    }

    public function getNodes()
    {
        return $this->nodes;
    }

    public function addNode(BaseNode $node)
    {
        $node->setParent($this);
        $this->nodes[$node->getName()] = $node;

        return $this;
    }

    public function removeNode($node)
    {
        if (!($node instanceof BaseNode)) {
            $node = $this->getNodeByPath($node);
        }
        if ($node) {
            foreach ($this->nodes as $i => $n) {
                if ($node === $n) {
                    unset($this->nodes[$i]);
                }
            }
        }

        return $this;
    }

    public function getNodeByPath($path)
    {
        $path = explode('.', $path);
        $node = $this;
        while ($nodeName = array_shift($path)) {
            $node = $node->getNode($nodeName);
            if(!$node){
                return false;
            }
        }

        return $node;
    }

    public function requireNode($name, $nodeClass = BaseNode::class)
    {
        $node = $this->getNode($name);
        if (!$node) {
            $node = new $nodeClass($name);
            $this->addNode($node);
        }

        return $node;
    }

    public function getNode($name)
    {
        return $this->hasNode($name)
            ? $this->nodes[$name]
            : false;
    }

    public function hasNode($name)
    {
        return isset($this->nodes[$name]);
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function flush()
    {
        $this->setValue(null);
        $this->nodes = [];

        return $this;
    }

    public function toArray($onlyValue = false)
    {
        if ($this->hasNodes()) {
            $arr = [];
            foreach ($this->getNodes() as $node) {
                $arr += $node->toArray();
            }
        } else {
            $arr = $this->getValue();
        }
        $result = $onlyValue ? $arr : [$this->getName() => $arr];

        return $result;
    }

    public function remove()
    {
        $parent = $this->parent;
        if (!$parent) {
            return;
        }

        return $parent->removeNode($this);
    }
}
