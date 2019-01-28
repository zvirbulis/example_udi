<?php


namespace App\Udi\Interfaces;

interface HandbookFieldInterface
{
    public function setAvailableValues(array $values);
    public function getKeyField();
    public function getValueField();
    public function getAvailableValues();
    public function getSettings();
    public function getCodeValue();
}
