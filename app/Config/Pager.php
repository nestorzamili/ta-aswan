<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Pager extends BaseConfig
{
    public array $templates = [
        'default_full'   => 'pager/bootstrap_full',
        'default_simple' => 'CodeIgniter\Pager\Views\default_simple',
        'default_head'   => 'CodeIgniter\Pager\Views\default_head',
        'bootstrap_full' => 'pager/bootstrap_full',
    ];
    public int $perPage = 10;
}
