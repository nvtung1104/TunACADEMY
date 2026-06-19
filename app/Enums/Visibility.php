<?php

namespace App\Enums;

enum Visibility: string
{
    case Public   = 'public';
    case Private  = 'private';
    case Class_   = 'class';
    case Selected = 'selected';
}
