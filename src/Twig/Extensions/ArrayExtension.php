<?php

namespace App\Twig\Extensions;

class ArrayExtension extends \Twig\Extension\AbstractExtension
{
    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('sumArray', [$this, 'sumArray']),
        ];
    }

    public function sumArray(array $array)
    {
        return array_sum($array);
    }


}