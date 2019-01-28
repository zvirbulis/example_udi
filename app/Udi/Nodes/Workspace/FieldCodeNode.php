<?php


namespace App\Udi\Nodes\Workspace;

use App\Udi\Nodes\BaseNode;

class FieldCodeNode extends BaseNode
{
    public $models;
    public $attribute;
    public $origin;

    public static function format($code)
    {
        return str_replace('.', '__', $code);
    }

    public static function unformat($code)
    {
        return str_replace('__', '.', $code);
    }
}
