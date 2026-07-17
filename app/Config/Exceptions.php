<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Debug\ExceptionHandler;
use CodeIgniter\Debug\ExceptionHandlerInterface;
use Psr\Log\LogLevel;
use Throwable;

class Exceptions extends BaseConfig
{
    public bool $log                   = true;
    public array $ignoreCodes          = [404];
    public string $errorViewPath       = APPPATH . 'Views/errors';
    public array $sensitiveDataInTrace = [];
    public bool $logDeprecations       = true;
    public string $deprecationLogLevel = LogLevel::WARNING;

    /*
     * DEFINE THE HANDLERS USED
     * --------------------------------------------------------------------------
     * Given the HTTP status code, returns exception handler that
     * should be used to deal with this error. By default, it will run CodeIgniter's
     * default handler and display the error information in the expected format
     * for CLI, HTTP, or AJAX requests, as determined by is_cli() and the expected
     * response format.
     *
     * Custom handlers can be returned if you want to handle one or more specific
     * error codes yourself like:
     *
     *      if (in_array($statusCode, [400, 404, 500])) {
     *          return new \App\Libraries\MyExceptionHandler();
     *      }
     *      if ($exception instanceOf PageNotFoundException) {
     *          return new \App\Libraries\MyExceptionHandler();
     *      }
     */
    public function handler(int $statusCode, Throwable $exception): ExceptionHandlerInterface
    {
        return new ExceptionHandler($this);
    }
}
