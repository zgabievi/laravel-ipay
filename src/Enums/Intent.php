<?php

namespace Zorb\IPay\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Capture()
 * @method static static Authorize()
 * @method static static Loan()
 */
final class Intent extends Enum
{
    const Capture = 'CAPTURE';
    const Authorize = 'AUTHORIZE';
    const Loan = 'LOAN';
}
