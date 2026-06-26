#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * THE HERITAGE EDIT — AI Product Enrichment Worker
 * Pure PHP — zero dependencies.
 *
 * Run manually:  php src/Workers/ProductEnrichmentWorker.php
 * Cron (every minute):
 *   * * * * * php /var/www/heritage-edit/src/Workers/ProductEnrichmentWorker.php >> /var/log/heritage_ai.log 2>&1
 */

define('APP_ROOT', dirname(__DIR__, 2));

require APP_ROOT . '/src/Core/Autoloader.php';

use HeritageEdit\Core\Env;
use HeritageEdit\Core\Database;
use HeritageEdit\Services\AIEnrichmentService;

Env::load(APP_ROOT . '/.env');

$db          = Database::getInstance();
$service     = new AIEnrichmentService();
$batchSize   = 5;
$maxAttempts = 3;

$ts = fn() => '[' . date('Y-m-d H:i:s') . ']';

echo $ts() . " Heritage Edit AI Worker starting...\n";

$jobs = $db->fetchAll(
    "SELECT id, product_id, attempts
     FROM ai_job_queue
     WHERE status = 'pending' AND attempts < ?
     ORDER BY scheduled_at ASC
     LIMIT ?",
    [$maxAttempts, $batchSize]
);

if (empty($jobs)) {
    echo $ts() . " No pending jobs. Exiting.\n";
    exit(0);
}

echo $ts() . " Processing " . count($jobs) . " job(s)...\n";

foreach ($jobs as $job) {
    $productId  = $job['product_id'];
    $attempts   = (int) $job['attempts'] + 1;

    $db->update('ai_job_queue', [
        'status'     => 'processing',
        'started_at' => date('Y-m-d H:i:s'),
        'attempts'   => $attempts,
    ], 'id = ?', [$job['id']]);

    echo $ts() . " Enriching product: $productId (attempt $attempts)\n";

    try {
        $success = $service->enrich($productId);

        if ($success) {
            $db->update('ai_job_queue', [
                'status'      => 'done',
                'finished_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$job['id']]);
            echo $ts() . " ✓ Done: $productId\n";
        } else {
            $newStatus = ($attempts >= $maxAttempts) ? 'failed' : 'pending';
            $db->update('ai_job_queue', [
                'status' => $newStatus,
                'error'  => 'Enrichment returned false',
            ], 'id = ?', [$job['id']]);
            echo $ts() . " ✗ Failed (no exception): $productId → $newStatus\n";
        }
    } catch (\Throwable $e) {
        $newStatus = ($attempts >= $maxAttempts) ? 'failed' : 'pending';
        $db->update('ai_job_queue', [
            'status' => $newStatus,
            'error'  => substr($e->getMessage(), 0, 500),
        ], 'id = ?', [$job['id']]);
        echo $ts() . " ✗ Exception for $productId: " . $e->getMessage() . "\n";
    }

    usleep(500_000); // 0.5s between API calls
}

echo $ts() . " Worker cycle complete.\n";
