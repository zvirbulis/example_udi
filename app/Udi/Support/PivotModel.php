<?php


namespace App\Udi\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Udi\Support\PivotModel
 *
 * @mixin \Eloquent
 */
class PivotModel extends Model
{
    public $timestamps = false;
    public $incrementing = false;

    protected $primaryKey = null;

    private static $_table = '';
    private static $_fillable = [];

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        $this->table = self::$_table;
        $this->fillable = self::$_fillable;
        $this->guarded = [];
    }

    public static function setUp($pivotData)
    {
        self::$_table = $pivotData['table'];
        self::$_fillable = $pivotData['fillable'] ?? [];
    }
};
