<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    public string $filesPath    = APPPATH . 'Database' . DIRECTORY_SEPARATOR;
    public string $defaultGroup = 'default';
    public array $default       = [
        'DSN'          => '',
        'hostname'     => 'localhost',
        'username'     => '',
        'password'     => '',
        'database'     => '',
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => (ENVIRONMENT !== 'production'),
        'charset'      => 'utf8mb4',
        'DBCollat'     => 'utf8mb4_general_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => true,
        'failover'     => [],
        'port'         => 3306,
        'numberNative' => false,
        'foundRows'    => false,
        'dateFormat'   => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];
    public array $tests = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1',
        'username'    => '',
        'password'    => '',
        'database'    => ':memory:',
        'DBDriver'    => 'SQLite3',
        'DBPrefix'    => 'db_',  // Needed to ensure we're working correctly with prefixes live. DO NOT REMOVE FOR CI DEVS
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8',
        'DBCollat'    => '',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => true,
        'failover'    => [],
        'port'        => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
        'synchronous' => null,
        'dateFormat'  => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';

            // Prefer MySQL (same as default .env) with table prefix so stock SQL / GET_LOCK work.
            // Falls back to the SQLite tests block above when default is not MySQLi.
            $driver = (string) ($this->default['DBDriver'] ?? '');
            $dbName = (string) ($this->default['database'] ?? '');
            if ($driver === 'MySQLi' && $dbName !== '' && $dbName !== ':memory:') {
                $this->tests['hostname'] = (string) ($this->default['hostname'] ?: '127.0.0.1');
                $this->tests['username'] = (string) ($this->default['username'] ?? '');
                $this->tests['password'] = (string) ($this->default['password'] ?? '');
                $this->tests['database'] = $dbName;
                $this->tests['DBDriver'] = 'MySQLi';
                $this->tests['DBPrefix'] = (string) env('database.tests.DBPrefix', 'tst_');
                $this->tests['port']     = (int) ($this->default['port'] ?? 3306);
                $this->tests['charset']  = 'utf8mb4';
                $this->tests['DBCollat'] = 'utf8mb4_general_ci';
                $this->tests['strictOn'] = true;
                unset($this->tests['foreignKeys'], $this->tests['busyTimeout'], $this->tests['synchronous']);
            }
        }
    }
}
