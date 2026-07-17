<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected const MSG_NOT_FOUND = 'Data tidak ditemukan.';

    /**
     * Allowed page sizes for list pagination (?per_page=).
     */
    protected const PER_PAGE_OPTIONS = [10, 20, 50];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        $this->helpers = ['form', 'url', 'form_ui'];
        parent::initController($request, $response, $logger);
    }

    /**
     * Resolve list page size from ?per_page= (10|20|50).
     */
    protected function perPage(int $default = 10): int
    {
        $n = (int) $this->request->getGet('per_page');

        return in_array($n, self::PER_PAGE_OPTIONS, true) ? $n : $default;
    }

    protected function redirectWithFieldErrors(string $to, array $errors, ?string $summary = null)
    {
        $summary ??= $errors === [] ? null : 'Periksa kembali isian yang ditandai.';

        $response = redirect()->to($to)->withInput()->with('errors', $errors);
        if ($summary !== null && $summary !== '') {
            $response = $response->with('error', $summary);
        }

        return $response;
    }

    protected function redirectBackWithFieldErrors(array $errors, ?string $summary = null)
    {
        $summary ??= $errors === [] ? null : 'Periksa kembali isian yang ditandai.';

        $response = redirect()->back()->withInput()->with('errors', $errors);
        if ($summary !== null && $summary !== '') {
            $response = $response->with('error', $summary);
        }

        return $response;
    }

    protected function parseDateFilter(?string $value): string
    {
        $value  = trim((string) $value);
        $parsed = '';
        if ($value !== '' && preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $value, $m)) {
            $parsed = checkdate((int) $m[2], (int) $m[1], (int) $m[3])
                ? sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1])
                : '';
        } elseif ($value !== '' && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m)) {
            $parsed = checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? $value : '';
        }

        return $parsed;
    }

    protected function formatDateDisplay(?string $ymd): string
    {
        $ymd = trim((string) $ymd);
        if ($ymd === '' || ! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $ymd, $m)) {
            return '';
        }

        return sprintf('%02d-%02d-%04d', (int) $m[3], (int) $m[2], (int) $m[1]);
    }

    protected function parseMoney(mixed $value): int
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return 0;
        }
        $raw = str_replace([' ', "\u{00A0}"], '', $raw);
        if (str_contains($raw, ',') && str_contains($raw, '.')) {
            $raw = str_replace('.', '', $raw);
            $raw = preg_replace('/,\d*$/', '', $raw) ?? $raw;
        } elseif (str_contains($raw, ',')) {
            if (preg_match('/,\d{1,2}$/', $raw)) {
                $raw = preg_replace('/,\d{1,2}$/', '', $raw) ?? $raw;
            }
            $raw = str_replace(',', '', $raw);
        } else {
            if (preg_match('/\.\d{1,2}$/', $raw)) {
                $raw = preg_replace('/\.\d{1,2}$/', '', $raw) ?? $raw;
            }
            $raw = str_replace('.', '', $raw);
        }

        return (int) preg_replace('/\D/', '', $raw);
    }

    protected function pdfResponse(string $binary, ?string $filename = null): ResponseInterface
    {
        $response = $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setBody($binary);

        if ($filename !== null && $filename !== '') {
            $safe = preg_replace('/[^A-Za-z0-9._-]+/', '-', $filename) ?? 'dokumen.pdf';
            $safe = trim($safe, '-.');
            if ($safe === '') {
                $safe = 'dokumen.pdf';
            }
            if (! str_ends_with(strtolower($safe), '.pdf')) {
                $safe .= '.pdf';
            }
            $response->setHeader(
                'Content-Disposition',
                'inline; filename="' . $safe . '"',
            );
        }

        return $response;
    }
}
