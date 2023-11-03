<?php

if (!function_exists('formatRupiah')) {
    function formatRupiah($angka) {
        if(!empty($angka)) {
            return 'Rp. ' . number_format($angka, 0);
        } else {
            return '';
        }
    }
}

if (!function_exists('numberFormat')) {
    function numberFormat($angka) {
        if(!empty($angka)) {
            if (is_numeric($angka) && strpos($angka, '.') !== false) {
                $format_angka = number_format($angka, 5);
                return rtrim($format_angka, '0');
            } else {
                return number_format($angka);
            }
        } else {
            return '';
        }
    }
}

if (!function_exists('indexToAlphabet')) {
    function indexToAlphabet($index) {
        $abjad = range('a', 'z');
        
        $loop = floor($index / 26);
        $index_abjad = ($index % 26);
        
        $result = "";
        for ($i = 0; $i <= $loop; $i++) {
              if($i > 0) {
                 $result = indexToAlphabet($i - 1);
              }
              $result .= $abjad[$index_abjad];
        }
        
        return $result;
    }
}