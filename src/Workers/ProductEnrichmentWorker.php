#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * THE HERITAGE EDIT — AI Product Enrichment Worker
 *
 * Run: php src/Workers/ProductEnrichmentWorker.php
 * Or schedule: * * * * * php /path/to/src/Workers/ProductEnrichmentWorker.php >> /var/log/heritage_worker.log 2>&1
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use HeritageEdit\Core\Database;
use HeritageEdit\Services\AIEnrichmentService;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$db      = Database::getInstance();
$service = new AIEnrichmentService();

$batchSize = 5;
$maxAttempts = 3;

echo "[" . date('Y-m-d H:i:s') . "] Heritage Edit AI Worker starting...\n";

// Pick pending jobs
$jobs = $db->fetchAll(
    "SELECT j.id, j.product_id, j.attempts
     FROM ai_job_queue j
     WHERE j.status = 'pending' AND j.attempts < ?
     ORDER BY j.scheduled_at ASC
     LIMIT ?",
    [$maxAttempts, $batchSize]
);

if (empty($jobs)) {
    echo "[" . date('Y-m-d H:i:s') . "] No pending jobs. Exiting.\n";
    exit(0);
}

echo "[" . date('Y-m-d H:i:s') . "] Processing " . count($jobs) . " job(s)...\n";

foreach ($jobs as $job) {
    $productId = $job['product_id'];

    // Mark as processing
    $db->update('ai_job_queue', [
        'status'     => 'processing',
        'started_at' => date('Y-m-d H:i:s'),
        'attempts'   => $job['attempts'] + 1,
    ], 'id = ?', [$job['id']]);

    echo "[" . date('Y-m-d H:i:s') . "] Enriching product: $productId\n";

    try {
        $success = $service->enrich($productId);

        if ($success) {
            $db->update('ai_job_queue', [
                'status'      => 'done',
                'finished_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$job['id']]);
            echo "[" . date('Y-m-d H:i:s') . "] ✓ Done: $productId\n";
        } else {
            $db->update('ai_job_queue', [
                'status' => ($job['attempts'] + 1 >= $maxAttempts) ? 'failed' : 'pending',
                'error'  => 'Enrichment returned false',
            ], 'id = ?', [$job['id']]);
            echo "[" . date('Y-m-d H:i:s') . "] ✗ Failed (non-exception): $productId\n";
        }
    } catch (\Throwable $e) {
        $status = ($job['attempts'] + 1 >= $maxAttempts) ? 'failed' : 'pending';
        $db->update('ai_job_queue', [
            'status' => $status,
            'error'  => substr($e->getMessage(), 0, 500),
        ], 'id = ?', [$job['id']]);
        echo "[" . date('Y-m-d H:i:s') . "] ✗ Exception for $productId: " . $e->getMessage() . "\n";
    }

    // Respect rate limits
    usleep(500_000); // 0.5s between calls
}

echo "[" . date('Y-m-d H:i:s') . "] Worker cycle complete.\n";
