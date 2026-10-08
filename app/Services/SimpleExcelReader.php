<?php

namespace App\Services;

use SimpleXMLElement;
use ZipArchive;

class SimpleExcelReader
{
    /**
     * Membaca file .xlsx secara native menggunakan ZipArchive dan SimpleXML
     * tanpa memerlukan dependensi library eksternal yang berat.
     *
     * @param string $filePath
     * @return array Baris dan kolom data
     */
    public static function parseXlsx(string $filePath): array
    {
        if (!class_exists('ZipArchive')) {
            return [];
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        // 1. Baca tabel string bersama (sharedStrings.xml)
        $sharedStrings = [];
        $stringIndex = $zip->locateName('xl/sharedStrings.xml');
        if ($stringIndex !== false) {
            $xmlContent = $zip->getFromIndex($stringIndex);
            if ($xmlContent) {
                $xml = simplexml_load_string($xmlContent);
                if ($xml && isset($xml->si)) {
                    foreach ($xml->si as $val) {
                        if (isset($val->t)) {
                            $sharedStrings[] = (string) $val->t;
                        } elseif (isset($val->r)) {
                            $text = '';
                            foreach ($val->r as $r) {
                                $text .= (string) $r->t;
                            }
                            $sharedStrings[] = $text;
                        } else {
                            $sharedStrings[] = '';
                        }
                    }
                }
            }
        }

        // 2. Baca data sheet pertama (xl/worksheets/sheet1.xml)
        $rows = [];
        $sheetIndex = $zip->locateName('xl/worksheets/sheet1.xml');
        if ($sheetIndex !== false) {
            $sheetContent = $zip->getFromIndex($sheetIndex);
            if ($sheetContent) {
                $xml = simplexml_load_string($sheetContent);
                if ($xml && isset($xml->sheetData->row)) {
                    foreach ($xml->sheetData->row as $row) {
                        $rowData = [];
                        foreach ($row->c as $cell) {
                            $type = (string) $cell['t'];
                            $val = (string) $cell->v;

                            if ($type === 's' && isset($sharedStrings[(int) $val])) {
                                $rowData[] = trim($sharedStrings[(int) $val]);
                            } elseif ($type === 'inlineStr' && isset($cell->is->t)) {
                                $rowData[] = trim((string) $cell->is->t);
                            } else {
                                $rowData[] = trim($val);
                            }
                        }
                        if (!empty(array_filter($rowData))) {
                            $rows[] = $rowData;
                        }
                    }
                }
            }
        }

        $zip->close();
        return $rows;
    }

    /**
     * Membaca file .csv dengan deteksi delimiter otomatis
     */
    public static function parseCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return [];
        }

        // Deteksi pemisah (koma atau titik koma)
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

        $rows = [];
        while (($row = fgetcsv($handle, 2000, $delimiter)) !== false) {
            if (!empty(array_filter($row))) {
                $rows[] = array_map('trim', $row);
            }
        }

        fclose($handle);
        return $rows;
    }
}
