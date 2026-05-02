<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super-admin';
    case ADMIN       = 'admin';
    case MARKETING   = 'marketing';
    case OPERATOR    = 'operator';
    case KNITTING    = 'knitting';
    case DYEING      = 'dyeing';
    case FINISHING   = 'finishing';
    case STENTER     = 'stenter';
    case RELAX_DRYER = 'relax-dryer';
    case TUMBLER     = 'tumbler';
    case FLEECE      = 'fleece';
    case PENGUJIAN   = 'pengujian';
    case QE          = 'qe';

    public function label(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Super Administrator',
            self::ADMIN       => 'Administrator',
            self::MARKETING   => 'Marketing Staff',
            self::OPERATOR    => 'General Operator',
            default           => strtoupper($this->value),
        };
    }
}
