<?php

namespace Zorb\IPay\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Automatic()
 * @method static static Manual()
 */
final class CaptureMethod extends Enum
{
    const Automatic = 'AUTOMATIC';
    const Manual = 'MANUAL';
}
