<?php

namespace Zorb\IPay\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static BAD_REQUEST()
 * @method static static UNAUTHORIZED()
 * @method static static FORBIDDEN()
 * @method static static METHOD_NOT_ALLOWED()
 * @method static static METHOD_NOT_ACCEPTABLE()
 * @method static static UNSUPPORTED_MEDIA_TYPE()
 */
final class ErrorCode extends Enum
{
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const METHOD_NOT_ALLOWED = 405;
    const METHOD_NOT_ACCEPTABLE = 406;
    const UNSUPPORTED_MEDIA_TYPE = 415;
}
