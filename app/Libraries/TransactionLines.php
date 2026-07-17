<?php

namespace App\Libraries;

use App\Exceptions\BusinessException;
use CodeIgniter\HTTP\RequestInterface;

class TransactionLines
{
    protected BarangLookup $lookup;

    public function __construct(?BarangLookup $lookup = null)
    {
        $this->lookup = $lookup ?? new BarangLookup();
    }

    public function parseFromRequest(RequestInterface $request, bool $usePostedHarga = false, ?callable $parseMoney = null): array
    {
        return $this->parse(
            (array) $request->getPost('id_barang'),
            (array) $request->getPost('quantity'),
            (array) $request->getPost('harga_satuan'),
            $usePostedHarga,
            $parseMoney,
        );
    }

    public function parse(
        array $idBar,
        array $qty,
        array $harga = [],
        bool $usePostedHarga = false,
        ?callable $parseMoney = null,
    ): array {
        $moneyFn = $parseMoney ?? static fn ($v): int => (int) preg_replace('/\D/', '', (string) $v);

        // Aggregate by id_barang so multi-row same item is one stock check.
        // Key: id_barang; for posted harga, last non-empty wins (or max if conflict).
        $agg = [];

        for ($i = 0, $n = count($idBar); $i < $n; $i++) {
            if (empty($idBar[$i]) || (int) ($qty[$i] ?? 0) <= 0) {
                continue;
            }

            $id = (int) $idBar[$i];
            $q  = (int) $qty[$i];

            if (! isset($agg[$id])) {
                $agg[$id] = [
                    'quantity'     => 0,
                    'harga_posted' => null,
                ];
            }
            $agg[$id]['quantity'] += $q;

            if ($usePostedHarga) {
                $h = (int) $moneyFn($harga[$i] ?? 0);
                // Prefer last posted price for the item when rows are merged.
                $agg[$id]['harga_posted'] = $h;
            }
        }

        $rows       = [];
        $totalQty   = 0;
        $totalHarga = 0.0;

        foreach ($agg as $id => $item) {
            $meta = $this->lookup->meta($id);

            $h = $usePostedHarga
                ? (int) ($item['harga_posted'] ?? 0)
                : (int) $meta['harga_jual'];

            if ($h < 0) {
                throw new BusinessException('Harga tidak valid untuk: ' . $meta['nama']);
            }

            $q   = (int) $item['quantity'];
            $sub = $q * $h;

            $rows[] = [
                'id_barang'    => $id,
                'quantity'     => $q,
                'harga_satuan' => $h,
                'subtotal'     => $sub,
            ];
            $totalQty += $q;
            $totalHarga += $sub;
        }

        if ($rows === []) {
            throw new BusinessException('Minimal satu item detail.');
        }

        return [
            'rows'  => $rows,
            'count' => count($rows),
            'qty'   => $totalQty,
            'total' => $totalHarga,
        ];
    }
}
