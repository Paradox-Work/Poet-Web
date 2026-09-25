<?php

namespace App\Enums;

enum GroupUserStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
}