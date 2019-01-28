<?php


namespace App\Udi\Interfaces;


use App\Udi\Resources\AbstractResource;

interface UdiUserInterface
{
    public function isAdmin();
    public function getUserGroups();
    public function hasAccessToUdiResource(AbstractResource $resource);
}