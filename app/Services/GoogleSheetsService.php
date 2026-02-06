<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Arr;

class GoogleSheetsService
{
    public function appendRow(array $payload): void
    {
        $sheetId = config('services.google.sheets.spreadsheet_id');
        $sheetName = config('services.google.sheets.sheet_name', 'Sheet1');
        $credentials = config('services.google.sheets.credentials_path');

        if (! $sheetId || ! $credentials) {
            throw new \RuntimeException('Google Sheets configuration is missing.');
        }

        $client = new GoogleClient();
        $client->setApplicationName('Pulse Co-Builder Survey');
        $client->setScopes([Sheets::SPREADSHEETS]);

        // Support both JSON string and file path
        if (is_string($credentials) && str_starts_with(trim($credentials), '{')) {
            // JSON credentials passed directly
            $client->setAuthConfig(json_decode($credentials, true));
        } else {
            // File path to credentials
            $client->setAuthConfig($credentials);
        }

        $service = new Sheets($client);
        $headerRange = sprintf('%s!1:1', $sheetName);
        $existing = $service->spreadsheets_values->get($sheetId, $headerRange)->getValues();

        $preferredOrder = [
            'Probability Score',
            'Timestamp',
            'Email',
            'First Name',
            'Last Name',
        ];

        $payloadKeys = array_keys($payload);
        $orderedPayloadKeys = array_values(array_unique(array_merge($preferredOrder, $payloadKeys)));

        $headers = $existing[0] ?? [];
        $missingHeaders = array_values(array_diff($orderedPayloadKeys, $headers));
        $mergedHeaders = $headers;

        if (count($missingHeaders) > 0) {
            $mergedHeaders = array_values(array_merge($headers, $missingHeaders));
            $body = new ValueRange([
                'values' => [$mergedHeaders],
            ]);
            $service->spreadsheets_values->update($sheetId, $headerRange, $body, [
                'valueInputOption' => 'RAW',
            ]);
        }

        if (empty($mergedHeaders)) {
            $mergedHeaders = $orderedPayloadKeys;
            $body = new ValueRange([
                'values' => [$mergedHeaders],
            ]);
            $service->spreadsheets_values->update($sheetId, $headerRange, $body, [
                'valueInputOption' => 'RAW',
            ]);
        }

        $row = [];
        foreach ($mergedHeaders as $header) {
            $row[] = Arr::get($payload, $header, '');
        }

        $appendRange = sprintf('%s!A:Z', $sheetName);
        $appendBody = new ValueRange([
            'values' => [$row],
        ]);
        $service->spreadsheets_values->append($sheetId, $appendRange, $appendBody, [
            'valueInputOption' => 'RAW',
            'insertDataOption' => 'INSERT_ROWS',
        ]);
    }
}
