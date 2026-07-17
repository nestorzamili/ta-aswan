<?php

namespace Config;

use App\Libraries\BarangLookup;
use App\Libraries\NumberingService;
use App\Libraries\PdfService;
use App\Libraries\StockService;
use App\Libraries\TransactionLines;
use App\Libraries\TransactionService;
use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    public static function stock(bool $getShared = true): StockService
    {
        if ($getShared) {
            return static::getSharedInstance('stock');
        }

        return new StockService();
    }

    public static function numbering(bool $getShared = true): NumberingService
    {
        if ($getShared) {
            return static::getSharedInstance('numbering');
        }

        return new NumberingService();
    }

    public static function barangLookup(bool $getShared = true): BarangLookup
    {
        if ($getShared) {
            return static::getSharedInstance('barangLookup');
        }

        return new BarangLookup();
    }

    public static function transactionLines(bool $getShared = true): TransactionLines
    {
        if ($getShared) {
            return static::getSharedInstance('transactionLines');
        }

        return new TransactionLines(static::barangLookup(false));
    }

    public static function transactions(bool $getShared = true): TransactionService
    {
        if ($getShared) {
            return static::getSharedInstance('transactions');
        }

        return new TransactionService(static::stock(false), static::numbering(false));
    }

    public static function pdf(bool $getShared = true): PdfService
    {
        if ($getShared) {
            return static::getSharedInstance('pdf');
        }

        return new PdfService();
    }

    public static function dashboard(bool $getShared = true): \App\Libraries\DashboardService
    {
        if ($getShared) {
            return static::getSharedInstance('dashboard');
        }

        return new \App\Libraries\DashboardService();
    }
}
