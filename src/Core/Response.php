<?php

declare(strict_types=1);

namespace HeritageEdit\Core;

final class Response
{
    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function view(string $template, array $data = [], int $status = 200): never
    {
        http_response_code($status);
        extract($data, EXTR_SKIP);
        $templatePath = __DIR__ . '/../../templates/' . $template . '.php';
        if (!file_exists($templatePath)) {
            self::abort(404, "Template [$template] not found");
        }
        ob_start();
        include $templatePath;
        $content = ob_get_clean();

        // Wrap in layout unless template handles its own layout
        if (!str_starts_with($template, 'layout/')) {
            include __DIR__ . '/../../templates/layout/base.php';
        } else {
            echo $content;
        }
        exit;
    }

    public static function redirect(string $url, int $status = 302): never
    {
        http_response_code($status);
        header("Location: $url");
        exit;
    }

    public static function abort(int $status, string $message = ''): never
    {
        http_response_code($status);
        echo $message;
        exit;
    }
}
