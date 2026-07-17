<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    public string $baseURL         = 'http://localhost:8080/';
    public array $allowedHostnames = [];
    public string $indexPage       = '';
    public string $uriProtocol     = 'REQUEST_URI';

    /*
    |--------------------------------------------------------------------------
    | Allowed URL Characters
    |--------------------------------------------------------------------------
    |
    | This lets you specify which characters are permitted within your URLs.
    | When someone tries to submit a URL with disallowed characters they will
    | get a warning message.
    |
    | As a security measure you are STRONGLY encouraged to restrict URLs to
    | as few characters as possible.
    |
    | By default, only these are allowed: `a-z 0-9~%.:_-`
    |
    | Set an empty string to allow all characters -- but only if you are insane.
    |
    | The configured value is actually a regular expression character group
    | and it will be used as: '/\A[<permittedURIChars>]+\z/iu'
    |
    | DO NOT CHANGE THIS UNLESS YOU FULLY UNDERSTAND THE REPERCUSSIONS!!
    |
    */
    public string $permittedURIChars       = 'a-z 0-9~%.:_\-';
    public string $defaultLocale           = 'en';
    public bool $negotiateLocale           = false;
    public array $supportedLocales         = ['en'];
    public string $appTimezone             = 'Asia/Jakarta';
    public string $charset                 = 'UTF-8';
    public bool $forceGlobalSecureRequests = false;

    /**
     * @var array<string, string>
     */
    public array $proxyIPs = [];

    public bool $CSPEnabled = false;
}
