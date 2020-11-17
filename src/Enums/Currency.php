<?php

namespace Zorb\IPay\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static GEL()
 * @method static static USD()
 * @method static static EUR()
 */
final class Currency extends Enum
{
    const GEL = 'GEL';
    const USD = 'USD';
    const EUR = 'EUR';
}
