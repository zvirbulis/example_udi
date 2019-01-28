<?php


namespace App\Udi\Traits;

use App\Udi\Resources\AbstractResource;
use Illuminate\Auth\Authenticatable;

/**
 * Trait UdiUserTrait
 * @package App\Udi\Traits
 * @mixin Authenticatable
 */
trait UdiUserTrait
{
    protected $groupAttr = 'group_code';

    public function hasAccessToUdiResource(AbstractResource $resource)
    {
        return $resource->accessable($this);
    }

    public function getUserGroups()
    {
        $group = $this->{$this->groupAttr};
        if(!is_array($group)){
            $group = [$group];
        }
        if($this->isAdmin()){
            $group[] = 'admin';
        }

        return array_unique($group);
    }
}