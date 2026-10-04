<?php

/**
 * SpreadsheetReader
 * -----------------
 * Reads the first sheet of an .xlsx workbook (or a .csv file) into a list of
 * associative rows keyed by normalised header names.
 *
 * Uses only PHP built-ins (ZipArchive + SimpleXML), so no composer packages
 * are needed. Legacy binary .xls files are rejected with a helpful message
 * because parsing them reliably requires an external library.
 *
 * Example:
 *     $rows = SpreadsheetReader::read('/tmp/list.xlsx', 'list.xlsx');
 *     // [ ['student_code' => 'S001', 'first_name' => 'Ana', ...], ... ]
 */
class SpreadsheetReader {
    /** Safety guard so a huge file cannot exhaust memory. */
    const MAX_ROWS = 5000;

    /**
     * Read a spreadsheet from disk.
     *
     * @param string $path         Absolute path to the file (e.g. the upload tmp_name).
     * @param string $originalName Original file name; decides the parser (.xlsx/.csv).
     * @return array[] List of associative rows keyed by normalised header.
     * @throws RuntimeException with a user-facing message on any problem.
     */
    public static function read($path, $originalName = '') {
        if (!is_file($path) || filesize($path) === 0) {
            throw new RuntimeException('The uploaded file is empty.');
        }

        $extension = strtolower(pathinfo((string) ($originalName ?: $path), PATHINFO_EXTENSION));

        switch ($extension) {
            case 'csv':
                return self::readCsv($path);
            case 'xlsx':
                return self::readXlsx($path);
            case 'xls':
                throw new RuntimeException(
                    'Legacy .xls files are not supported. Please open the file and '
                    . 'save it as .xlsx (or .csv), then upload it again.'
                );
            default:
                throw new RuntimeException('Unsupported file type. Please upload an .xlsx or .csv file.');
        }
    }

    // ---------------------------------------------------------------------
    // CSV
    // ---------------------------------------------------------------------

    private static function readCsv($path) {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException('The file could not be opened.');
        }

        // Detect the delimiter from the header line: commas are common, but
        // Excel localised exports often use semicolons.
        $firstLine = (string) fgets($handle);
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        rewind($handle);

        $headers = null;
        $rows    = [];

        while (($cells = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
            if ($cells === [null] || $cells === [false]) {
                continue; // Blank line.
            }

            if ($headers === null) {
                $headers = self::uniqueHeaders(array_map([self::class, 'normaliseHeader'], $cells));
                continue;
            }

            $rows[] = self::zipRow($headers, $cells);

            if (count($rows) >= self::MAX_ROWS) {
                fclose($handle);
                throw new RuntimeException('The file has more than ' . self::MAX_ROWS . ' rows. Please split it into smaller files.');
            }
        }

        fclose($handle);

        if ($headers === null) {
            throw new RuntimeException('The file is empty.');
        }

        return self::dropEmptyRows($rows);
    }

    // ---------------------------------------------------------------------
    // XLSX
    // ---------------------------------------------------------------------

    private static function readXlsx($path) {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('The Excel file could not be opened. It may be corrupt or password protected.');
        }

        try {
            $shared = self::parseSharedStrings($zip);
            [$sheetPath, $date1904] = self::firstSheetPath($zip);
            $dateStyles = self::parseDateStyles($zip);

            $sheetXml = $zip->getFromName($sheetPath);
            if ($sheetXml === false) {
                throw new RuntimeException('The Excel file has no readable worksheet.');
            }

            $sheet = simplexml_load_string($sheetXml);
            if ($sheet === false) {
                throw new RuntimeException('The Excel worksheet could not be parsed.');
            }

            $rows         = [];
            $headers      = null;
            $epoch        = $date1904 ? 24107 : 25569; // Excel serial -> unix days.

            foreach ($sheet->sheetData->row as $row) {
                $cells = [];
                foreach ($row->c as $cell) {
                    $cells[self::cellColumnIndex($cell)] = self::cellValue($cell, $shared, $dateStyles, $epoch);
                }

                if ($headers === null) {
                    if ($cells === []) {
                        continue; // Skip leading blank rows before the header.
                    }
                    $headers = self::uniqueHeaders(array_map(
                        [self::class, 'normaliseHeader'],
                        self::cellsToSequential($cells)
                    ));
                    continue;
                }

                $rows[] = self::zipRow($headers, self::cellsToSequential($cells));

                if (count($rows) >= self::MAX_ROWS) {
                    throw new RuntimeException('The file has more than ' . self::MAX_ROWS . ' rows. Please split it into smaller files.');
                }
            }

            if ($headers === null) {
                throw new RuntimeException('The Excel file has no header row. Please include a header row with the column names.');
            }

            return self::dropEmptyRows($rows);
        } finally {
            $zip->close();
        }
    }

    /**
     * Content of <si> entries in xl/sharedStrings.xml. Rich-text runs are
     * concatenated, which is what Excel does visually too.
     */
    private static function parseSharedStrings(ZipArchive $zip) {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }

        $root = simplexml_load_string($xml);
        if ($root === false) {
            return [];
        }

        $shared = [];
        // NOTE: iterating a SimpleXML element list yields the ELEMENT NAME as
        // the foreach key ('si'), not a numeric index - a manual counter is
        // required or every string would collapse onto key 0.
        $index = 0;
        foreach ($root->si as $item) {
            $text = '';
            foreach ($item->t as $t) {
                $text .= (string) $t;
            }
            // Rich text: <r><t>part</t></r> runs.
            foreach ($item->r as $run) {
                $text .= (string) $run->t;
            }
            $shared[$index] = $text;
            $index++;
        }

        return $shared;
    }

    /**
     * Resolve the path of the FIRST worksheet via workbook.xml and its
     * relationship file - the sheet file name is not always sheet1.xml.
     *
     * @return array{0: string, 1: bool} [sheet path, uses the 1904 date system]
     */
    private static function firstSheetPath(ZipArchive $zip) {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        if ($workbookXml === false) {
            throw new RuntimeException('The Excel file is not a valid workbook.');
        }

        $workbook = simplexml_load_string($workbookXml);
        if ($workbook === false || !isset($workbook->sheets->sheet[0])) {
            throw new RuntimeException('The Excel file has no worksheets.');
        }

        $date1904 = strtolower((string) ($workbook->workbookPr['date1904'] ?? '')) === 'true'
            || (string) ($workbook->workbookPr['date1904'] ?? '') === '1';

        $sheet    = $workbook->sheets->sheet[0];
        $ridAttrs = $sheet->attributes('r', true);
        $rid      = (string) ($ridAttrs->id ?? '');
        if ($rid === '') {
            return ['xl/worksheets/sheet1.xml', $date1904]; // Very old writers omit the id.
        }

        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($relsXml === false) {
            throw new RuntimeException('The Excel file is not a valid workbook.');
        }

        $rels = simplexml_load_string($relsXml);
        foreach ($rels->Relationship ?? [] as $rel) {
            if ((string) $rel['Id'] === $rid) {
                $target = (string) $rel['Target'];
                if ($target === '') {
                    break;
                }
                if ($target[0] === '/') {
                    return [ltrim($target, '/'), $date1904];          // Absolute part path.
                }
                if (strpos($target, 'xl/') === 0) {
                    return [$target, $date1904];                      // Already package-rooted.
                }
                return ['xl/' . $target, $date1904];                  // Relative to xl/.
            }
        }

        return ['xl/worksheets/sheet1.xml', $date1904];
    }

    /**
     * Style indexes that Excel formats as DATES, from styles.xml. A date cell
     * stores its value as a serial number, so without this check a date of
     * birth would arrive as something like "45123".
     */
    private static function parseDateStyles(ZipArchive $zip) {
        $xml = $zip->getFromName('xl/styles.xml');
        if ($xml === false) {
            return [];
        }

        $root = simplexml_load_string($xml);
        if ($root === false) {
            return [];
        }

        // Built-in date-ish number format ids.
        $builtinDateIds = array_fill_keys(array_merge(range(14, 22), range(27, 36), [45, 46, 47], range(50, 58)), true);

        // Custom formats (id >= 164) are dates when the code shows a y/d/h.
        $customDateIds = [];
        foreach ($root->numFmts->numFmt ?? [] as $numFmt) {
            $code = (string) $numFmt['formatCode'];
            if (preg_match('/[yYdD]/', $code) === 1) {
                $customDateIds[(string) $numFmt['numFmtId']] = true;
            }
        }

        $dateStyles = [];
        // Same SimpleXML gotcha as in parseSharedStrings(): the foreach key is
        // the element name, so the style index needs a manual counter.
        $styleIndex = 0;
        foreach ($root->cellXfs->xf ?? [] as $xf) {
            $numFmtId = (string) ($xf['numFmtId'] ?? '0');
            if (isset($builtinDateIds[$numFmtId]) || isset($customDateIds[$numFmtId])) {
                $dateStyles[$styleIndex] = true;
            }
            $styleIndex++;
        }

        return $dateStyles;
    }

    /**
     * Resolve one <c> cell to a plain string.
     */
    private static function cellValue($cell, array $shared, array $dateStyles, $epoch) {
        $type = (string) ($cell['t'] ?? '');
        $style = isset($cell['s']) ? (int) $cell['s'] : -1;

        switch ($type) {
            case 's': // Shared string.
                $key = (int) (string) ($cell->v ?? '-1');
                return isset($shared[$key]) ? trim((string) $shared[$key]) : '';
            case 'inlineStr': // String stored inline.
                $text = '';
                foreach ($cell->is->t as $t) {
                    $text .= (string) $t;
                }
                return trim($text);
            case 'b': // Boolean.
                return ((string) ($cell->v ?? '')) === '1' ? '1' : '0';
            default: // Numbers and formula results.
                $raw = trim((string) ($cell->v ?? ''));
                if ($raw === '') {
                    return '';
                }
                // A date-formatted cell holds an Excel serial number.
                if (isset($dateStyles[$style]) && is_numeric($raw)) {
                    return self::serialToDate((float) $raw, $epoch) ?? $raw;
                }
                return $raw;
        }
    }

    /**
     * "B12" -> 1 (zero-based column index), so cells always land in the right
     * column even when Excel skips empty ones. Cells without an r attribute
     * are handled by the sequential fallback in the caller.
     */
    private static function cellColumnIndex($cell) {
        $reference = (string) ($cell['r'] ?? '');
        if ($reference === '' || !preg_match('/^([A-Z]+)/i', $reference, $m)) {
            return PHP_INT_MAX; // Appended after known columns; sequential fallback covers it.
        }

        $index = 0;
        foreach (str_split(strtoupper($m[1])) as $letter) {
            $index = $index * 26 + (ord($letter) - ord('A') + 1);
        }

        return $index - 1;
    }

    /**
     * Turn the sparse [columnIndex => value] map into a sequential list. Cells
     * without an r attribute are appended after the referenced ones.
     */
    private static function cellsToSequential(array $cells) {
        if ($cells === []) {
            return [];
        }

        $sequential = [];
        $append     = [];

        foreach ($cells as $index => $value) {
            if ($index === PHP_INT_MAX) {
                $append[] = $value;
            } else {
                $sequential[$index] = $value;
            }
        }

        if ($sequential !== []) {
            for ($i = 0; $i <= max(array_keys($sequential)); $i++) {
                $sequential[$i] = $sequential[$i] ?? '';
            }
            ksort($sequential);
        }

        return array_merge($sequential, $append);
    }

    // ---------------------------------------------------------------------
    // Shared helpers
    // ---------------------------------------------------------------------

    /**
     * "Date of Birth" -> "date_of_birth", "E-Mail" -> "e_mail".
     */
    public static function normaliseHeader($value) {
        $value = trim((string) $value);
        // Strip a UTF-8 BOM that some CSV exports keep in the first cell.
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '_', $value);
        return trim((string) $value, '_');
    }

    /**
     * De-duplicate headers so two columns with the same name never overwrite
     * each other silently.
     */
    private static function uniqueHeaders(array $headers) {
        $seen  = [];
        $final = [];
        foreach ($headers as $header) {
            $header = (string) $header;
            if ($header === '') {
                $final[] = '';
                continue;
            }
            if (isset($seen[$header])) {
                $seen[$header]++;
                $final[] = $header . '_' . $seen[$header];
            } else {
                $seen[$header] = 1;
                $final[] = $header;
            }
        }
        return $final;
    }

    /**
     * Combine headers and cells into an assoc row. Missing cells become ''.
     */
    private static function zipRow(array $headers, array $cells) {
        $row = [];
        foreach ($headers as $i => $header) {
            if ($header === '') {
                continue; // Unnamed column - ignore.
            }
            $value = isset($cells[$i]) ? trim((string) $cells[$i]) : '';
            $row[$header] = $value;
        }
        return $row;
    }

    /**
     * Remove rows where every value is blank (trailing junk in exports).
     */
    private static function dropEmptyRows(array $rows) {
        return array_values(array_filter($rows, function ($row) {
            foreach ($row as $value) {
                if ((string) $value !== '') {
                    return true;
                }
            }
            return false;
        }));
    }

    /**
     * Excel serial date number -> Y-m-d string. 25569 is the serial of the
     * unix epoch in the default 1900 system; 24107 for the 1904 system.
     */
    public static function serialToDate($serial, $epoch = 25569) {
        $serial = (float) $serial;
        if ($serial <= 0) {
            return null;
        }

        $unix = (int) round(($serial - $epoch) * 86400);
        if ($unix < 0 || $unix > 32503680000) { // Year 3000 sanity cap.
            return null;
        }

        return gmdate('Y-m-d', $unix);
    }
}
