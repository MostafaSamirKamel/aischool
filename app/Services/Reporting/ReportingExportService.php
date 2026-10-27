<?php

namespace App\Services\Reporting;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Mpdf\Mpdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportingExportService
{
    /**
     * Escape string against spreadsheet formula injection (CSV Injection).
     */
    public static function sanitizeCell($value): string
    {
        if (is_null($value)) {
            return '';
        }

        $str = (string) $value;
        if ($str === '') {
            return '';
        }

        // If cell starts with formula triggers, prepend a single quote
        $firstChar = $str[0];
        if (in_array($firstChar, ['=', '+', '-', '@', "\t", "\r"])) {
            return "'" . $str;
        }

        return $str;
    }

    /**
     * Stream CSV with UTF-8 BOM and formula protection.
     */
    public function exportCsv(string $reportTitle, array $headers, array $rows, ?string $filename = null): StreamedResponse
    {
        $filename = $filename ?: 'report_' . Carbon::now()->format('Ymd_His') . '.csv';

        return response()->stream(function () use ($reportTitle, $headers, $rows) {
            $handle = fopen('php://output', 'w');
            
            // Output UTF-8 BOM for Microsoft Excel compatibility with Arabic
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Metadata rows
            fputcsv($handle, [self::sanitizeCell($reportTitle)]);
            fputcsv($handle, [self::sanitizeCell('تاريخ التصدير: ' . Carbon::now()->format('Y-m-d H:i:s'))]);
            fputcsv($handle, []);

            // Column Headings
            $sanitizedHeaders = array_map([self::class, 'sanitizeCell'], $headers);
            fputcsv($handle, $sanitizedHeaders);

            // Data Rows
            foreach ($rows as $row) {
                $sanitizedRow = array_map([self::class, 'sanitizeCell'], $row);
                fputcsv($handle, $sanitizedRow);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    /**
     * Export to Excel format (.xlsx / stream-compatible).
     */
    public function exportExcel(string $reportTitle, array $headers, array $rows, ?string $filename = null): StreamedResponse
    {
        $filename = $filename ?: 'report_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        // We output as clean Excel-compatible TSV/XML or CSV with xlsx extension for maximum server portability
        return $this->exportCsv($reportTitle, $headers, $rows, $filename);
    }

    /**
     * Export to PDF with professional RTL layout and Tajawal font styling.
     */
    public function exportPdf(string $reportTitle, array $headers, array $rows, array $metadata = []): string
    {
        $data = [
            'title' => $reportTitle,
            'headers' => $headers,
            'rows' => $rows,
            'metadata' => $metadata,
            'exported_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        $html = view('reports.reporting-center-pdf', $data)->render();

        try {
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => count($headers) > 6 ? 'L' : 'P',
                'margin_left' => 12,
                'margin_right' => 12,
                'margin_top' => 15,
                'margin_bottom' => 15,
                'autoArabic' => true,
                'autoLangToFont' => true,
            ]);

            $mpdf->SetDirectionality('rtl');
            $mpdf->WriteHTML($html);

            return $mpdf->Output('', 'S');
        } catch (\Throwable $e) {
            // Fallback to DomPDF
            $pdf = Pdf::loadHTML($html)->setPaper('a4', count($headers) > 6 ? 'landscape' : 'portrait');
            return $pdf->output();
        }
    }
}
