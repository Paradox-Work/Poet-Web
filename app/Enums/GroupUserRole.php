<?php

namespace App\Enums;

enum GroupUserRole: string
{
    case ADMIN = 'admin';
    case MEMBER = 'member';
}