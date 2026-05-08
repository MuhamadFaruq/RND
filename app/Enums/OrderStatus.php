<?php

namespace App\Enums;

enum OrderStatus: string
{
    case KNITTING = 'knitting';
    case DYEING = 'dyeing';
    case RELAX_DRYER = 'relax-dryer';
    case COMPACTOR = 'compactor';
    case HEAT_SETTING = 'heat-setting';
    case STENTER = 'stenter';
    case TUMBLER = 'tumbler';
    case FLEECE = 'fleece';
    case PENGUJIAN = 'pengujian';
    case QE = 'qe';
    case FINISHED = 'finished';

    /**
     * Get a human-readable label for the status.
     */
    public function label(): string
    {
        return match($this) {
            self::KNITTING => 'Knitting',
            self::DYEING => 'Dyeing',
            self::RELAX_DRYER => 'Relax Dryer',
            self::COMPACTOR => 'Compactor',
            self::HEAT_SETTING => 'Heat Setting',
            self::STENTER => 'Stenter',
            self::TUMBLER => 'Tumbler Dry',
            self::FLEECE => 'Fleece/Raising',
            self::PENGUJIAN => 'Pengujian',
            self::QE => 'Quality Engineering',
            self::FINISHED => 'Finished (Selesai)',
        };
    }
}
