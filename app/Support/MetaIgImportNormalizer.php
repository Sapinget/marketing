<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

class MetaIgImportNormalizer
{
    private const META_EXPORT_SOURCE_TIMEZONE = 'America/Los_Angeles';

    private const META_DASHBOARD_TARGET_TIMEZONE = '+08:00';

    private const DATASET_SIGNATURES = [
        'story' => ['navigation', 'sticker_taps', 'link_clicks', 'profile_visits'],
        'feed' => ['comments', 'saves', 'shares'],
    ];

    private const METRIC_FIELDS = [
        'views',
        'reach',
        'likes',
        'shares',
        'comments',
        'saves',
        'follows',
        'profile_visits',
        'replies',
        'navigation',
        'link_clicks',
        'sticker_taps',
    ];

    private const FIELD_ALIASES = [
        'post_id' => ['post id', 'postid', 'media id'],
        'account' => ['account', 'account username', 'username', 'account handle'],
        'account_name' => ['account name', 'profile name'],
        'description' => ['description', 'caption', 'post caption', 'content'],
        'duration' => ['duration', 'duration sec', 'duration second', 'duration seconds', 'duration (sec)'],
        'publish_time' => ['publish time', 'published time', 'publish date', 'date published', 'posted at'],
        'permalink' => ['permalink', 'link', 'post link', 'media link'],
        'post_type' => ['post type', 'content type', 'media type', 'type'],
        'views' => ['views', 'impressions'],
        'reach' => ['reach'],
        'likes' => ['likes'],
        'shares' => ['shares'],
        'comments' => ['comments'],
        'saves' => ['saves'],
        'follows' => ['follows', 'followers'],
        'profile_visits' => ['profile visits', 'profile visit'],
        'replies' => ['replies', 'reply'],
        'navigation' => ['navigation', 'navigations'],
        'link_clicks' => ['link clicks', 'link click'],
        'sticker_taps' => ['sticker taps', 'sticker tap'],
    ];

    public function normalizeImportRows(array $rows, string $dataset): array
    {
        $normalizedRows = [];

        foreach ($rows as $row) {
            $normalized = $this->normalizeRow((array) $row, $dataset);

            if ($normalized !== null) {
                $normalizedRows[] = $normalized;
            }
        }

        return $normalizedRows;
    }

    public function loadImportRowsFromDirectory(string $directory, string $dataset): array
    {
        $this->assertDataset($dataset);

        if (! is_dir($directory)) {
            return ['rows' => [], 'files_scanned' => 0, 'files_matched' => 0];
        }

        $files = glob(rtrim($directory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'*.csv') ?: [];
        sort($files);

        $rows = [];
        $matched = 0;

        foreach ($files as $file) {
            $rawRows = $this->readCsvRows($file);
            if ($rawRows === [] || ! $this->rowsMatchDataset($rawRows, $dataset)) {
                continue;
            }

            $matched++;
            $rows = [...$rows, ...$rawRows];
        }

        return [
            'rows' => $rows,
            'files_scanned' => count($files),
            'files_matched' => $matched,
        ];
    }

    public function normalizeRow(array $row, string $dataset): ?array
    {
        $this->assertDataset($dataset);

        $mapped = [];

        foreach ($row as $header => $value) {
            $canonicalKey = $this->canonicalKeyForHeader($header);
            if ($canonicalKey === null) {
                continue;
            }

            $mapped[$canonicalKey] = $value;
        }

        $postId = $this->normalizeString($mapped['post_id'] ?? null);
        if ($postId === null) {
            return null;
        }

        $normalized = [
            'post_id' => $postId,
            'account' => $this->normalizeString($mapped['account'] ?? null),
            'account_name' => $this->normalizeString($mapped['account_name'] ?? null),
            'description' => $this->normalizeString($mapped['description'] ?? null),
            'duration' => $this->normalizeInteger($mapped['duration'] ?? null),
            'publish_time' => $this->normalizeDateTime($mapped['publish_time'] ?? null),
            'permalink' => $this->normalizeString($mapped['permalink'] ?? null),
            'post_type' => $this->normalizeString($mapped['post_type'] ?? null),
            'raw_payload' => $row['raw_payload'] ?? $row,
        ];

        foreach (self::METRIC_FIELDS as $field) {
            $normalized[$field] = $this->normalizeInteger($mapped[$field] ?? null, true);
        }

        return $normalized;
    }

    private function assertDataset(string $dataset): void
    {
        if (! in_array($dataset, ['story', 'feed'], true)) {
            throw new InvalidArgumentException('Unsupported Meta dataset.');
        }
    }

    private function canonicalKeyForHeader(mixed $header): ?string
    {
        $normalizedHeader = $this->normalizeHeader($header);
        if ($normalizedHeader === '') {
            return null;
        }

        foreach (self::FIELD_ALIASES as $canonicalKey => $aliases) {
            if ($normalizedHeader === $canonicalKey) {
                return $canonicalKey;
            }

            if (in_array($normalizedHeader, $aliases, true)) {
                return $canonicalKey;
            }
        }

        return null;
    }

    private function normalizeHeader(mixed $header): string
    {
        $value = strtolower(trim((string) $header));
        $value = str_replace(["\xEF\xBB\xBF", '\\ufeff', '﻿'], '', $value);
        $value = preg_replace('/[_\-]+/', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }

    private function rowsMatchDataset(array $rows, string $dataset): bool
    {
        $firstRow = (array) ($rows[0] ?? []);
        $canonicalKeys = [];

        foreach (array_keys($firstRow) as $header) {
            $canonicalKey = $this->canonicalKeyForHeader($header);
            if ($canonicalKey !== null) {
                $canonicalKeys[] = $canonicalKey;
            }
        }

        $canonicalKeys = array_values(array_unique($canonicalKeys));
        $signature = self::DATASET_SIGNATURES[$dataset] ?? [];

        foreach ($signature as $requiredKey) {
            if (in_array($requiredKey, $canonicalKeys, true)) {
                return true;
            }
        }

        return false;
    }

    private function readCsvRows(string $filePath): array
    {
        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            return [];
        }

        try {
            $headers = fgetcsv($handle);
            if (! is_array($headers) || $headers === []) {
                return [];
            }

            $rows = [];

            while (($values = fgetcsv($handle)) !== false) {
                if ($values === [null] || $values === []) {
                    continue;
                }

                $row = [];
                foreach ($headers as $index => $header) {
                    $row[(string) $header] = $values[$index] ?? null;
                }
                $rows[] = $row;
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }

    private function normalizeString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function normalizeInteger(mixed $value, bool $defaultZero = false): ?int
    {
        if ($value === null) {
            return $defaultZero ? 0 : null;
        }

        $normalized = preg_replace('/[^0-9\-]/', '', (string) $value);
        if ($normalized === null || $normalized === '') {
            return $defaultZero ? 0 : null;
        }

        return (int) $normalized;
    }

    private function normalizeDateTime(mixed $value): ?string
    {
        $normalized = $this->normalizeString($value);
        if ($normalized === null) {
            return null;
        }

        $formats = [
            'm/d/Y H:i:s',
            'm/d/Y H:i',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
        ];

        foreach ($formats as $format) {
            try {
                return CarbonImmutable::createFromFormat($format, $normalized, self::META_EXPORT_SOURCE_TIMEZONE)
                    ->setTimezone(self::META_DASHBOARD_TARGET_TIMEZONE)
                    ->format('Y-m-d H:i:s');
            } catch (\Throwable) {
            }
        }

        try {
            return CarbonImmutable::parse($normalized)
                ->setTimezone(self::META_DASHBOARD_TARGET_TIMEZONE)
                ->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }
}
