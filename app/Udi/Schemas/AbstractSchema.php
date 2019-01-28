<?php


namespace App\Udi\Schemas;

use App\Udi\Interfaces\SchemaInterface;
use Illuminate\Support\Arr;

abstract class AbstractSchema implements SchemaInterface
{
    protected $data;
    protected $name;

    public function __construct($name = null, $data = [])
    {
        $this->name = $name;
        $this->data = $this->parse($data);
    }

    abstract protected function parse($data) : array;

    public function getName()
    {
        return $this->name;
    }

    /**
     * @TODO избавиться от зависимости в виде Arr
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->data, $key, $default);
    }

    /**
     * @TODO избавиться от зависисмости в виде Arr
     * @param $key
     * @param $value
     * @return array
     */
    public function set($key, $value)
    {
        return Arr::set($this->data, $key, $value);
    }

    public function merge($data, $key = null)
    {
        if ($key) {
            $this->set($key, array_replace_recursive($this->get($key), $data));
        } else {
            $this->data = array_replace_recursive($this->data, $data);
        }

        return $this;
    }

    public function toArray()
    {
        return $this->data;
    }

    public function toJson()
    {
        return json_encode($this->data);
    }
}
