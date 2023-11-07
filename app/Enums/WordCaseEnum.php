<?php

namespace App\Enums;

enum WordCaseEnum: string
{
    case STUDLY = 'studly';
    case CAMEL = 'camel';
    case SNAKE = 'snake';
    case KEBAB = 'kebab';
}
