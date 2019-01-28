<?php


namespace App\Udi\Interfaces;

interface SchemaInterface
{
    public function getName();

    public function get($key, $default = null);
    public function set($key, $value);
    public function merge($data, $key = null);

    public function toArray();
    public function toJson();
}
