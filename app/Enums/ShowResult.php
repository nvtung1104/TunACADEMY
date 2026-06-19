<?php

namespace App\Enums;

enum ShowResult: string
{
    case Immediate  = 'immediate';
    case AfterClose = 'after_close';
    case Manual     = 'manual';
}
