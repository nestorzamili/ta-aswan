<?php

function form_errors(): array
{
    static $cached = null;

    if ($cached !== null) {
        return $cached;
    }

    $errors = session()->getFlashdata('errors');
    if (! is_array($errors)) {
        return $cached = [];
    }

    $out = [];

    foreach ($errors as $field => $message) {
        if (is_string($field) && (is_string($message) || is_numeric($message))) {
            $out[$field] = (string) $message;
        }
    }

    return $cached = $out;
}

function field_error(string $field): string
{
    return form_errors()[$field] ?? '';
}

function field_is_invalid(string $field): bool
{
    return field_error($field) !== '';
}

/**
 * HTML attribute fragment for invalid fields (single source for aria-invalid).
 */
function aria_invalid_attr(string $field): string
{
    return field_is_invalid($field) ? ' aria-invalid="true"' : '';
}

function input_class(string $field, string $base = 'form-control'): string
{
    return field_is_invalid($field) ? trim($base . ' is-invalid') : $base;
}

function field_feedback(string $field): string
{
    $msg = field_error($field);
    if ($msg === '') {
        return '';
    }

    return '<div class="invalid-feedback d-block">' . esc($msg) . '</div>';
}

/**
 * Rebuild transaction line rows from flashed old() input (after validation failure).
 *
 * @param array<string, mixed>|null $source Optional POST-like map for tests; null → session old()
 *
 * @return list<array{id_barang: int, quantity: int, harga_satuan?: float|int}>
 */
function old_transaction_lines(bool $includeHarga = false, ?array $source = null): array
{
    if ($source === null) {
        $ids    = old('id_barang');
        $qtys   = old('quantity');
        $hargas = old('harga_satuan');
    } else {
        $ids    = $source['id_barang'] ?? null;
        $qtys   = $source['quantity'] ?? null;
        $hargas = $source['harga_satuan'] ?? null;
    }

    if (! is_array($ids) || $ids === []) {
        return [];
    }

    $qtys   = is_array($qtys) ? $qtys : [];
    $hargas = is_array($hargas) ? $hargas : [];

    $out = [];

    foreach ($ids as $i => $id) {
        if ($id === null || $id === '' || (int) $id <= 0) {
            continue;
        }

        $line = [
            'id_barang' => (int) $id,
            'quantity'  => max(1, (int) ($qtys[$i] ?? 1)),
        ];

        if ($includeHarga && array_key_exists($i, $hargas)) {
            $raw                  = (string) $hargas[$i];
            $line['harga_satuan'] = (int) preg_replace('/\D/', '', $raw);
        }

        $out[] = $line;
    }

    return $out;
}
