<?php


namespace App\Udi\Exceptions;


use App\Exceptions\KanSchoolException;

class UdiProgressActionException extends KanSchoolException
{
    private $responseData;

    public function setReponseData($responseData)
    {
        $this->responseData = $responseData;

        return $this;
    }

    public function getResponseData()
    {
        return $this->responseData;
    }
}