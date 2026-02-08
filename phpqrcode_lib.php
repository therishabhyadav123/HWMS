<?php
include __DIR__ . "/phpqrcode.php";

// create local QR
$qr_text = "https://yourdomain.com/scan_handler.php?token=" . $qr_token;

$qr_file = __DIR__ . "/qrcodes/qr_" . $report_id . ".png";

QRcode::png($qr_text, $qr_file, 'L', 8, 2);

class QRcode_Lib {
    public static function png($text, $outfile = false, $level = 'L', $size = 4, $margin = 2) {
        $tempDir = __DIR__ . "/qrcodes/";
        if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);

        $filename = $tempDir . "qr_" . time() . ".png";

        include_once __DIR__ . "/qrlib_full.php"; // full generator

        QRcode::png($text, $filename, $level, $size, $margin);

        if ($outfile === false) {
            header("Content-Type: image/png");
            readfile($filename);
            unlink($filename);
        } else {
            return $filename;
        }
    }
}
?>
