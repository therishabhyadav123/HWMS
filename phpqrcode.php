<?php
// Minimal QR Code Generator Library
class QRcode {
    public static function png($text, $outfile = false, $level = 'L', $size = 4, $margin = 2) {
        include_once __DIR__ . '/phpqrcode_lib.php';
        QRcode_Lib::png($text, $outfile, $level, $size, $margin);
    }
}
?>
