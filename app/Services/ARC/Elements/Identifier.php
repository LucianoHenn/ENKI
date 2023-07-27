<?php


namespace App\Services\ARC\Elements;
 

class Identifier
{
    public $market = 'all';
    public $identifier = '';

    public function __construct($market = 'all', $identifier = '')
    {
        $this->market = $market;
        $this->identifier = $identifier;
    }
}