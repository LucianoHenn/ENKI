<?php

namespace App\Services\ARC\Sources\Providers\IAC\D2S;

use App\Services\ARC\Sources\Abstracts\BaseRequest;
use App\Services\ARC\Elements\Identifier;


/**
 * Class IACD2SRequest
 */
class IACD2SRequest extends BaseRequest
{
    public function getIdentifiers(): array
    {
        $identifiers[] = new Identifier('all', 'aj-cm9');
        return $identifiers;
    }
}
