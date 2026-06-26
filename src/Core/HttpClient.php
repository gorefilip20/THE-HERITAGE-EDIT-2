<?php

declare(strict_types=1);

namespace HeritageEdit\Core;

/**
 * Lightweight HTTP client backed by PHP cURL — no dependencies.
 * Supports GET, POST with JSON body, custom headers, basic auth, timeouts.
 */
final class HttpClient
{
    private array $defaultHeaders;
    private int   $timeout;
    private ?array $basicAuth;

    public function __construct(array $options = [])
    {
        $this->defaultHeaders = $options['headers'] ?? [];
        $this->timeout        = $options['timeout']  ?? 30;
        $this->basicAuth      = $options['auth']     ?? null; // [user, pass]
    }

    public function get(string $url, array $headers = []): HttpResponse
    {
        return $this->request('GET', $url, null, $headers);
    }

    public function post(string $url, array $json = [], array $headers = []): HttpResponse
    {
        return $this->request('POST', $url, $json, $headers);
    }

    public function put(string $url, array $json = [], array $headers = []): HttpResponse
    {
        return $this->request('PUT', $url, $json, $headers);
    }

    private function request(string $method, string $url, ?array $json, array $extraHeaders): HttpResponse
    {
        $ch = curl_init();

        $mergedHeaders = array_merge($this->defaultHeaders, $extraHeaders);

        $curlHeaders = [];
        foreach ($mergedHeaders as $k => $v) {
            $curlHeaders[] = "$k: $v";
        }

        $body = null;
        if ($json !== null) {
            $body = json_encode($json, JSON_UNESCAPED_UNICODE);
            $curlHeaders[] = 'Content-Type: application/json';
            $curlHeaders[] = 'Content-Length: ' . strlen($body);
        }

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER     => $curlHeaders,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        if ($this->basicAuth) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->basicAuth[0] . ':' . ($this->basicAuth[1] ?? ''));
        }

        $responseBody = curl_exec($ch);
        $statusCode   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error        = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException("cURL error: $error");
        }

        return new HttpResponse($statusCode, (string) $responseBody);
    }
}

final class HttpResponse
{
    public function __construct(
        public readonly int    $status,
        public readonly string $body,
    ) {}

    public function json(): array
    {
        $data = json_decode($this->body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON response: ' . json_last_error_msg());
        }
        return (array) $data;
    }

    public function ok(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }

    public function throw(): static
    {
        if (!$this->ok()) {
            throw new \RuntimeException("HTTP {$this->status}: {$this->body}");
        }
        return $this;
    }
}
