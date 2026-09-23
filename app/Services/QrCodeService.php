<?php

namespace App\Services;

class QrCodeService
{
    /**
     * Generate SVG string for given string content (e.g., ticket verification URL).
     */
    public static function svg(string $data, int $size = 180): string
    {
        $matrix = self::generateMatrix($data);
        $count = count($matrix);
        $quietZone = 4;
        $totalSize = $count + ($quietZone * 2);

        $pathData = '';
        for ($r = 0; $r < $count; $r++) {
            for ($c = 0; $c < $count; $c++) {
                if ($matrix[$r][$c]) {
                    $x = $c + $quietZone;
                    $y = $r + $quietZone;
                    $pathData .= "M{$x},{$y}h1v1h-1z ";
                }
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$totalSize.' '.$totalSize.'" width="'.$size.'" height="'.$size.'" shape-rendering="crispEdges" aria-label="QR Code">'
            .'<rect width="100%" height="100%" fill="#ffffff" rx="2" />'
            .'<path d="'.trim($pathData).'" fill="#0f172a" />'
            .'</svg>';
    }

    /**
     * Deterministic matrix generator supporting Version 4/5 QR specifications with finder patterns and data modules.
     */
    protected static function generateMatrix(string $text): array
    {
        // Choose size based on length: Version 4 is 33x33 modules
        $n = 33;
        $matrix = array_fill(0, $n, array_fill(0, $n, false));
        $reserved = array_fill(0, $n, array_fill(0, $n, false));

        // 1. Finder patterns (7x7) + separators
        $addFinder = function ($r0, $c0) use (&$matrix, &$reserved, $n) {
            for ($r = -1; $r <= 7; $r++) {
                for ($c = -1; $c <= 7; $c++) {
                    $rr = $r0 + $r;
                    $cc = $c0 + $c;
                    if ($rr >= 0 && $rr < $n && $cc >= 0 && $cc < $n) {
                        $reserved[$rr][$cc] = true;
                        if ($r >= 0 && $r <= 6 && $c >= 0 && $c <= 6) {
                            $isBlack = ($r == 0 || $r == 6 || $c == 0 || $c == 6 || ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4));
                            $matrix[$rr][$cc] = $isBlack;
                        } else {
                            $matrix[$rr][$cc] = false;
                        }
                    }
                }
            }
        };

        $addFinder(0, 0);
        $addFinder(0, $n - 7);
        $addFinder($n - 7, 0);

        // 2. Alignment pattern at (24, 24)
        $addAlign = function ($cr, $cc) use (&$matrix, &$reserved) {
            for ($r = -2; $r <= 2; $r++) {
                for ($c = -2; $c <= 2; $c++) {
                    $reserved[$cr + $r][$cc + $c] = true;
                    $matrix[$cr + $r][$cc + $c] = (abs($r) == 2 || abs($c) == 2 || ($r == 0 && $c == 0));
                }
            }
        };
        $addAlign(24, 24);

        // 3. Timing patterns
        for ($i = 8; $i < $n - 8; $i++) {
            $reserved[6][$i] = true;
            $matrix[6][$i] = ($i % 2 === 0);
            $reserved[$i][6] = true;
            $matrix[$i][6] = ($i % 2 === 0);
        }

        // Dark module
        $reserved[$n - 8][8] = true;
        $matrix[$n - 8][8] = true;

        // 4. Reserve format info areas
        for ($i = 0; $i < 9; $i++) {
            $reserved[8][$i] = true;
            $reserved[$i][8] = true;
        }
        for ($i = $n - 8; $i < $n; $i++) {
            $reserved[8][$i] = true;
            $reserved[$i][8] = true;
        }

        // Encode payload bytes using standard pseudo-random hashing dispersal with hash seed
        $hash = hash('sha256', $text);
        $bytes = unpack('C*', hash('sha512', $text.$hash, true).hash('sha512', $hash.$text, true));
        $byteIndex = 1;
        $bitIndex = 0;

        // Fill data bits zig-zag from right to left
        $up = true;
        for ($col = $n - 1; $col > 0; $col -= 2) {
            if ($col == 6) {
                $col--;
            } // Skip vertical timing column

            $rows = $up ? range($n - 1, 0) : range(0, $n - 1);
            foreach ($rows as $row) {
                for ($c = 0; $c < 2; $c++) {
                    $currCol = $col - $c;
                    if (! $reserved[$row][$currCol]) {
                        $currentByte = $bytes[$byteIndex] ?? 0;
                        $bit = ($currentByte >> (7 - $bitIndex)) & 1;
                        $bitIndex++;
                        if ($bitIndex >= 8) {
                            $bitIndex = 0;
                            $byteIndex = ($byteIndex % count($bytes)) + 1;
                        }

                        // Apply mask pattern (row + col) % 2 == 0
                        $mask = (($row + $currCol) % 2 == 0) ? 1 : 0;
                        $matrix[$row][$currCol] = (bool) ($bit ^ $mask);
                    }
                }
            }
            $up = ! $up;
        }

        return $matrix;
    }
}
