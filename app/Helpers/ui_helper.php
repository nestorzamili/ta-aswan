<?php

if (! function_exists('avatar_url')) {
    function avatar_url(?string $foto): ?string
    {
        $foto = trim((string) $foto);
        if ($foto === '') {
            return null;
        }

        return site_url('profil/foto') . '?v=' . substr(md5($foto), 0, 8);
    }
}
