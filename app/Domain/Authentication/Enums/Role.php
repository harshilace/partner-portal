<?php

namespace App\Domain\Authentication\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case MAIN_PARTNER = 'main_partner';
    case SUB_PARTNER = 'sub_partner';
    case PARTNER_USER = 'partner_user';
}
