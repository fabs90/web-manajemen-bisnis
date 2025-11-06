<?php

if (!function_exists("format_rupiah")) {
    function format_rupiah($angka, $with_prefix = true)
    {
        if ($angka === null) {
            $angka = 0;
        }

        $hasil = number_format($angka, 0, ",", ".");
        return $with_prefix ? "Rp " . $hasil : $hasil;
    }
}
