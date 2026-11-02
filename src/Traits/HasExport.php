<?php

namespace SteelAnts\DataTable\Traits;

use BackedEnum;
use DateTimeInterface;
use Illuminate\Support\Arr;
use Stringable;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * CSV export of every row matching the current search, filters and sorting
 * (not only the visible page), with the header labels as the first line.
 */
trait HasExport
{
    /**
     * File name without the extension. Set it with setFilename() in mount(), or override
     * exportFilename() - redeclaring this property in the component is a PHP fatal error.
     */
    public string $filename = "NoName";

    public function setFilename(string $filename)
    {
        $this->filename = $filename;
    }

    public function exportFilename(): string
    {
        return $this->filename;
    }

    public function exportCsv(): StreamedResponse
    {
        $headers = $this->getHeader();

        return response()->streamDownload(function () use ($headers) {
            $fp = fopen('php://output', 'w');

            // BOM, so Excel reads the file as UTF-8
            fputs($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($fp, array_map(fn ($label) => $this->exportValue($label), array_values($headers)), ';', '"', '');

            foreach ($this->exportRows() as $row) {
                $line = [];
                foreach (array_keys($headers) as $key) {
                    $line[] = $this->exportValue(Arr::get($row, $key));
                }
                fputcsv($fp, $line, ';', '"', '');
            }

            fclose($fp);
        }, $this->exportFilename() . '.csv', ['Content-Type' => 'text/csv; charset=utf-8']);
    }

    /**
     * Called by the export button - kept, so components overriding serv() keep working.
     */
    public function serv(): StreamedResponse
    {
        return $this->exportCsv();
    }

    /**
     * One CSV cell. Text starting with =, +, -, @ (or a tab / carriage return) is prefixed
     * with an apostrophe, so spreadsheet applications do not run it as a formula
     * (CSV injection). Numbers, including negative ones, are kept as they are.
     */
    protected function exportValue(mixed $value): string
    {
        $value = match (true) {
            $value === null => '',
            is_bool($value) => $value ? '1' : '0',
            $value instanceof BackedEnum => (string)$value->value,
            $value instanceof DateTimeInterface => $value->format('Y-m-d H:i:s'),
            is_scalar($value), $value instanceof Stringable => (string)$value,
            default => json_encode($value, JSON_UNESCAPED_UNICODE),
        };

        if ($value !== '' && !is_numeric($value) && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            $value = "'" . $value;
        }

        return $value;
    }
}
