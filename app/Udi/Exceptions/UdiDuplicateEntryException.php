<?php


namespace App\Udi\Exceptions;

class UdiDuplicateEntryException extends UdiWorkspaceResourceException
{
    protected $message = "Неможливо створити запис оскільки такий запис вже є у системі";
}
