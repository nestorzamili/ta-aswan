<?php

namespace App\Libraries;

use Config\Store;
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    public function render(
        string $view,
        array $data = [],
        string $paper = 'A4',
        string $orientation = 'portrait',
    ): string {
        $data['store'] ??= config(Store::class);
        $data['pdfMeta'] ??= $this->defaultMeta($data);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view($view, $data));
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array{printed_at: string, printed_by: string, printed_level: string|null, doc_ref: string|null}
     */
    protected function defaultMeta(array $data): array
    {
        $nama  = session('nama');
        $user  = session('username');
        $level = session('level');

        $by = is_string($nama) && $nama !== ''
            ? $nama
            : (is_string($user) && $user !== '' ? $user : 'Sistem');

        return [
            'printed_at'    => date('d-m-Y H:i') . ' WIB',
            'printed_by'    => $by,
            'printed_level' => is_string($level) && $level !== '' ? $level : null,
            'doc_ref'       => isset($data['docRef']) ? (string) $data['docRef'] : null,
        ];
    }
}
