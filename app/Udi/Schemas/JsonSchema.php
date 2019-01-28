<?php


namespace App\Udi\Schemas;

use App\Udi\Exceptions\UdiException;

class JsonSchema extends AbstractSchema
{
    /**
     * @param $data
     * @return array|mixed
     * @throws UdiException
     */
    protected function parse($data) : array
    {
        if (is_string($data)) {
            $result = json_decode($data, true);
            if (!$result) {
                throw new UdiException('$data невалідна json строка');
            }
        } elseif (is_array($data)) {
            $result = $data;
        } else {
            throw new UdiException('Невалідна $data для парсингу. Data має бути json строкою або масивом');
        }

        return $result;
    }
}
