<?php

namespace SteelAnts\DataTable\Traits;

use Symfony\Component\HttpFoundation\StreamedResponse;

trait HasExport
{
    public string $filename = "NoName";

    public function setFilename(string $filename)
    {
        $this->filename = $filename;
    }

    private function exportHeaders($filename): array
    {
        return [
            'Pragma'                    => 'public',
            'Expires'                   => '0',
            'Cache-Control'             => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Description'       => 'File Transfer',
            'Content-Type'              => 'text/csv; charset=utf-8',
            'Content-Disposition'       => 'attachment; filename=' . $filename . '.csv',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Encoding'          => 'UTF-8',
        ];
    }

    public function serv(): StreamedResponse
    {
        $data = $this->dataset;
        $callback = function () use ($data) {
            //open file pointer to standard output
            $fp = fopen('php://output', 'w');

            //add BOM to fix UTF-8 in Excel
            fputs($fp, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            fputcsv($fp, array_keys($data[0]), ";", '"');

            foreach ($data as $row) {
                fputcsv($fp, $row, ";", '"');
            }
            fclose($fp);
        };

        return response()->stream($callback, 200, $this->exportHeaders($this->filename));
    }
}
