<?php

namespace App\Services\Utils;

use Mustache_Engine;

class Mustache extends Mustache_Engine
{
    public static function new(...$args)
    {
        return new self(...$args);
    }

    public function renderRecursive($arrayTemplate, $placeholders)
    {
        $newArrayTemplate = $arrayTemplate;
        array_walk_recursive($newArrayTemplate, fn(&$element) => ($element = !is_string($element) ? $element : $this->render($element, $placeholders)) );
        return $newArrayTemplate;
    }
}
