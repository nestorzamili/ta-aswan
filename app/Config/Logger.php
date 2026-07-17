<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Log\Handlers\FileHandler;

class Logger extends BaseConfig
{
    public $threshold         = (ENVIRONMENT === 'production') ? 4 : 9;
    public string $dateFormat = 'Y-m-d H:i:s';
    public array $handlers    = [
        /*
     * --------------------------------------------------------------------
     * File Handler
     * --------------------------------------------------------------------
     */
        FileHandler::class => [
            'handles' => [
                'critical',
                'alert',
                'emergency',
                'debug',
                'error',
                'info',
                'notice',
                'warning',
            ],

            /*
         * The default filename extension for log files.
         * An extension of 'php' allows for protecting the log files via basic
         * scripting, when they are to be stored under a publicly accessible directory.
         *
         * NOTE: Leaving it blank will default to 'log'.
         */
            'fileExtension' => '',

            /*
         * The file system permissions to be applied on newly created log files.
         *
         * IMPORTANT: This MUST be an integer (no quotes) and you MUST use octal
         * integer notation (i.e. 0700, 0644, etc.)
         */
            'filePermissions' => 0644,

            /*
         * Logging Directory Path
         *
         * By default, logs are written to WRITEPATH . 'logs/'
         * Specify a different destination here, if desired.
         */
            'path' => '',
        ],

        /*
     * The ChromeLoggerHandler requires the use of the Chrome web browser
     * and the ChromeLogger extension. Uncomment this block to use it.
     */

        /*
     * The ErrorlogHandler writes the logs to PHP's native `error_log()` function.
     * Uncomment this block to use it.
     */
    ];
}
