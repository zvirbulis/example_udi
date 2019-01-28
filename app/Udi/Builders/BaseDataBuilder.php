<?php


namespace App\Udi\Builders;

use App\Udi\Nodes\BaseNode;

class BaseDataBuilder
{
    protected $skeleton;

    public function __construct(BaseNode $skeleton)
    {
        $this->skeleton = $skeleton;
    }

    public function build()
    {
        return $this->skeleton->toArray();
    }
}
