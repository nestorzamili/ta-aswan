<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Domain/business-rule failure (stock, numbering, validation of lines, etc.).
 */
class BusinessException extends RuntimeException
{
}
