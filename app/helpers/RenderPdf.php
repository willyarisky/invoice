<?php

declare(strict_types=1);

namespace App\Helpers;

use RuntimeException;
use Zero\Lib\View;

class RenderPdf
{
    protected string $signature = 'render_pdf';
    protected bool $cli = true;
    protected bool $web = true;

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     */
    public function handle(string $template, array $data = [], array $options = []): string
    {
        $template = strtolower(trim($template));

        if ($template !== 'invoice') {
            throw new RuntimeException('Unsupported PDF template.');
        }

        $html = View::render('invoices/pdf', $data);

        return $this->renderHtml($html, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function renderHtml(string $html, array $options): string
    {
        $command = $this->buildCommand($options);
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes, \base());

        if (!is_resource($process)) {
            throw new RuntimeException('Unable to start wkhtmltopdf process.');
        }

        fwrite($pipes[0], $html);
        fclose($pipes[0]);

        $pdf = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $status = proc_close($process);

        if ($status !== 0 || !is_string($pdf) || $pdf === '') {
            $message = trim((string) $stderr);
            if ($message === '') {
                $message = 'wkhtmltopdf failed to render PDF.';
            }

            throw new RuntimeException($message);
        }

        return $pdf;
    }

    /**
     * @param array<string, mixed> $options
     * @return array<int, string>
     */
    private function buildCommand(array $options): array
    {
        $binary = trim((string) ($options['binary'] ?? \env('WKHTMLTOPDF_PATH', 'wkhtmltopdf')));
        if ($binary === '') {
            $binary = 'wkhtmltopdf';
        }

        $command = [
            $binary,
            '--quiet',
            '--encoding',
            'utf-8',
            '--print-media-type',
            '--enable-local-file-access',
            '--page-size',
            'A4',
            '--margin-top',
            '20mm',
            '--margin-right',
            '20mm',
            '--margin-bottom',
            '20mm',
            '--margin-left',
            '20mm',
        ];

        if (!empty($options['disable_smart_shrinking'])) {
            $command[] = '--disable-smart-shrinking';
        }

        $command[] = '-';
        $command[] = '-';

        return $command;
    }
}
