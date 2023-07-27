<?php

namespace App\Services\ARC\Sources\Abstracts;

use App\Services\ARC\Sources\Abstracts\BasePhase;

abstract class BaseRequest extends BasePhase
{
    /**
     * 
     * Must be redefined in single request to retrieve the identifiers for requests to make
     * @return array of App\Services\ARC\Elements\Identifier objects
     **/
    abstract public function getIdentifiers() : array;
}
