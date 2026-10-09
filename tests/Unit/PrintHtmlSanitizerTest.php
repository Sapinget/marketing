<?php

namespace Tests\Unit;

use App\Support\PrintHtmlSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PrintHtmlSanitizerTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: list<string>}> input => fragmen yang TIDAK boleh ada di keluaran
     */
    public static function hostileInputs(): array
    {
        return [
            'unquoted onerror' => ['<img src=x onerror=alert(1)>', ['onerror', 'alert(1)']],
            'unquoted onmouseover' => ['<div onmouseover=alert(2)>m</div>', ['onmouseover']],
            'quoted onclick, mixed case' => ['<p OnClick="alert(1)">x</p>', ['onclick']],
            'javascript href mixed case' => ['<a href="JaVaScRiPt:alert(1)">a</a>', ['javascript']],
            'javascript href with tab entity' => ['<a href="java&#x09;script:alert(1)">a</a>', ['script:']],
            'javascript href with leading space' => ['<a href=" javascript:alert(1)">a</a>', ['javascript']],
            'vbscript href' => ['<a href="vbscript:msgbox(1)">a</a>', ['vbscript']],
            'data html img' => ['<img src="data:text/html;base64,PHNjcmlwdD4=">', ['data:text/html']],
            'protocol relative link' => ['<a href="//evil.example/x">a</a>', ['evil.example']],
            'svg onload' => ['<svg onload=alert(1)></svg>', ['onload', '<svg']],
            'script tag' => ['<script>alert(1)</script>', ['<script']],
            'meta refresh' => ['<meta http-equiv="refresh" content="0;url=https://evil.example">', ['refresh', 'evil.example']],
            'external stylesheet' => ['<link rel="stylesheet" href="https://evil.example/a.css">', ['evil.example']],
            'css import' => ['<style>@import url(https://evil.example/a.css); p{color:red}</style>', ['@import']],
            'css expression in style attr' => ['<p style="width:expression(alert(1))">s</p>', ['expression']],
            'css javascript url in style attr' => ['<p style="background:url(javascript:alert(1))">s</p>', ['javascript']],
        ];
    }

    /**
     * @param  list<string>  $forbidden
     */
    #[DataProvider('hostileInputs')]
    public function test_it_removes_executable_or_exfiltrating_markup(string $input, array $forbidden): void
    {
        $output = strtolower(PrintHtmlSanitizer::sanitize($input));

        foreach ($forbidden as $fragment) {
            $this->assertStringNotContainsString(strtolower($fragment), $output, "'{$fragment}' survived in: {$output}");
        }
    }

    public function test_it_keeps_normal_print_markup(): void
    {
        $output = PrintHtmlSanitizer::sanitize(
            '<title>Laporan</title><style>p{color:red}</style><link rel="stylesheet" href="/build/a.css">'
            .'<table><tr><td style="color:red">1</td></tr></table>'
            .'<a href="https://ok.example/x">tautan</a><img src="data:image/png;base64,iVBORw0KGgo=" alt="x">'
        );

        $this->assertStringContainsString('<title>Laporan</title>', $output);
        $this->assertStringContainsString('p{color:red}', $output);
        $this->assertStringContainsString('href="/build/a.css"', $output);
        $this->assertStringContainsString('<td style="color:red">1</td>', $output);
        $this->assertStringContainsString('href="https://ok.example/x" rel="noopener noreferrer"', $output);
        $this->assertStringContainsString('src="data:image/png;base64,iVBORw0KGgo="', $output);
    }
}
