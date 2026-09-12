<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Google\Service\Sheets\ClearValuesRequest;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\Request as SheetRequest;
use Google\Service\Sheets\RepeatCellRequest;
use Google\Service\Sheets\CellData;
use Google\Service\Sheets\CellFormat;
use Google\Service\Sheets\Color;
use Google\Service\Sheets\TextFormat;
use Google\Service\Sheets\GridRange;
use Illuminate\Support\Facades\Log;

class GoogleSheetService
{
    private ?Sheets $sheetsService = null;

    private function getService(): Sheets
    {
        if ($this->sheetsService) {
            return $this->sheetsService;
        }

        $client = new Client();
        $client->setAuthConfig(storage_path(config('services.google.credentials_path')));
        $client->setScopes([Sheets::SPREADSHEETS]);

        $this->sheetsService = new Sheets($client);
        return $this->sheetsService;
    }

    /**
     * Sync data ke sheet tertentu.
     * Clear seluruh isi sheet, lalu tulis header + data baru.
     *
     * @param string $spreadsheetId
     * @param string $sheetName  Nama tab/sheet (misal: "Work Orders")
     * @param array  $headers    Array 1D nama kolom
     * @param array  $rows       Array 2D data baris
     */
    public function syncSheet(string $spreadsheetId, string $sheetName, array $headers, array $rows): int
    {
        $service = $this->getService();
        $range = $sheetName . '!A1';

        // 1. Clear sheet
        $service->spreadsheets_values->clear(
            $spreadsheetId,
            $sheetName,
            new ClearValuesRequest()
        );

        // 2. Tulis header + data
        $values = array_merge([$headers], $rows);
        $body = new ValueRange(['values' => $values]);

        $service->spreadsheets_values->update(
            $spreadsheetId,
            $range,
            $body,
            ['valueInputOption' => 'RAW']
        );

        // 3. Format header bold + background
        $this->formatHeader($spreadsheetId, $sheetName, count($headers));

        return count($rows);
    }

    /**
     * Bold + background warna pada baris header (row 1).
     */
    private function formatHeader(string $spreadsheetId, string $sheetName, int $columnCount): void
    {
        try {
            $service = $this->getService();

            // Cari sheetId dari nama sheet
            $spreadsheet = $service->spreadsheets->get($spreadsheetId);
            $sheetId = null;
            foreach ($spreadsheet->getSheets() as $sheet) {
                if ($sheet->getProperties()->getTitle() === $sheetName) {
                    $sheetId = $sheet->getProperties()->getSheetId();
                    break;
                }
            }

            if ($sheetId === null) {
                return;
            }

            $requests = [
                new SheetRequest([
                    'repeatCell' => [
                        'range' => [
                            'sheetId' => $sheetId,
                            'startRowIndex' => 0,
                            'endRowIndex' => 1,
                            'startColumnIndex' => 0,
                            'endColumnIndex' => $columnCount,
                        ],
                        'cell' => [
                            'userEnteredFormat' => [
                                'backgroundColor' => [
                                    'red' => 0.2,
                                    'green' => 0.66,
                                    'blue' => 0.33,
                                    'alpha' => 1,
                                ],
                                'textFormat' => [
                                    'bold' => true,
                                    'foregroundColor' => [
                                        'red' => 1,
                                        'green' => 1,
                                        'blue' => 1,
                                    ],
                                ],
                            ],
                        ],
                        'fields' => 'userEnteredFormat(backgroundColor,textFormat)',
                    ],
                ]),
                // Freeze header row
                new SheetRequest([
                    'updateSheetProperties' => [
                        'properties' => [
                            'sheetId' => $sheetId,
                            'gridProperties' => [
                                'frozenRowCount' => 1,
                            ],
                        ],
                        'fields' => 'gridProperties.frozenRowCount',
                    ],
                ]),
            ];

            $batchRequest = new BatchUpdateSpreadsheetRequest(['requests' => $requests]);
            $service->spreadsheets->batchUpdate($spreadsheetId, $batchRequest);
        } catch (\Exception $e) {
            Log::warning('GoogleSheetService: gagal format header', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Pastikan sheet/tab ada. Jika belum ada, buat baru.
     */
    public function ensureSheetExists(string $spreadsheetId, string $sheetName): void
    {
        $service = $this->getService();
        $spreadsheet = $service->spreadsheets->get($spreadsheetId);

        foreach ($spreadsheet->getSheets() as $sheet) {
            if ($sheet->getProperties()->getTitle() === $sheetName) {
                return; // sudah ada
            }
        }

        // Buat sheet baru
        $request = new BatchUpdateSpreadsheetRequest([
            'requests' => [
                new SheetRequest([
                    'addSheet' => [
                        'properties' => [
                            'title' => $sheetName,
                        ],
                    ],
                ]),
            ],
        ]);

        $service->spreadsheets->batchUpdate($spreadsheetId, $request);
    }
}
