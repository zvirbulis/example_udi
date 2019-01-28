<?php


namespace App\Udi\Support;


class Formatter
{
    public static function format($method, $value)
    {
        $result = $value;
        $method = self::parseMethod($method);

        if(method_exists(__CLASS__, $method['name'])){
            if(is_array($value)){
                $result = [];
                foreach($value as $k => $v){
                    $result[$k] = self::call($method['name'], $method['args'], $value);
                }
            }
            else{
                $result = self::call($method['name'], $method['args'], $value);
            }
        }

        return $result;
    }

    private static function parseMethod($method)
    {
        $result = [
            'name' => '',
            'args' => []
        ];

        $args = explode(',', $method);
        $result['name'] = array_shift($args);
        if($args){
            $result['args'] = array_map('trim', $args);
        }

        return $result;
    }

    private static function call($name, array $args, $value)
    {
        array_unshift($args, $value);

        return call_user_func_array([__CLASS__, $name], $args);
    }


    protected static function date($value, $format)
    {
        return date($format, strtotime($value));
    }

    protected static function year_interval($value)
    {
        $value = $value ?: 'Весь рік';

        return $value;
    }

    protected static function odd_even($value)
    {
        $value = (($value === null)
            ? "завжди"
            : ($value === 0 ? "непарний" : "парний")
        );

        return $value;
    }

    protected static function boolean($value)
    {
        return $value ? 'Так' : 'Ні';
    }

    protected static function year_status($value)
    {
        $statuses = [
            'active' => 'Поточний',
            'develop' => 'В розробці',
            'archive' => 'Архівний'
        ];

        return $statuses[$value] ?? 'Статус не визначено!';
    }
}