<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Membersihkan HTML dokumen cetak sebelum disimpan untuk /print-job.
 *
 * strip_tags hanya menyaring nama tag; atribut berbahaya (onerror=, onmouseover= tanpa tanda kutip,
 * href/src javascript:, meta refresh, @import) dibuang di sini lewat DOM. Ini bukan satu-satunya
 * pertahanan: halaman cetak juga disajikan dengan CSP ber-nonce dan hanya untuk pembuatnya.
 */
class PrintHtmlSanitizer
{
    private const ALLOWED_TAGS = '<div><span><p><br><hr><table><thead><tbody><tr><th><td><h1><h2><h3><h4><h5><h6><ul><ol><li><img><a><strong><em><b><i><u><s><pre><code><blockquote><section><article><header><footer><main><aside><figure><figcaption><style><link><meta><title>';

    private const URL_ATTRIBUTES = ['href', 'src', 'action', 'formaction', 'xlink:href', 'poster', 'background', 'data', 'ping', 'cite'];

    private const DANGEROUS_CSS = '/expression\s*\(|javascript\s*:|vbscript\s*:|behavior\s*:|-moz-binding|@import/i';

    public static function sanitize(string $html): string
    {
        $stripped = strip_tags($html, self::ALLOWED_TAGS);

        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><html><head></head><body><div id="ppp-print-root">'.$stripped.'</div></body></html>',
            LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($dom);

        foreach (iterator_to_array($xpath->query('//*') ?: []) as $element) {
            if ($element instanceof DOMElement && $element->parentNode !== null) {
                self::sanitizeElement($element);
            }
        }

        $output = '';
        $head = $dom->getElementsByTagName('head')->item(0);
        if ($head !== null) {
            foreach ($head->childNodes as $child) {
                $output .= $dom->saveHTML($child);
            }
        }

        $root = $dom->getElementById('ppp-print-root');
        if ($root !== null) {
            foreach ($root->childNodes as $child) {
                $output .= $dom->saveHTML($child);
            }
        }

        return $output;
    }

    private static function sanitizeElement(DOMElement $element): void
    {
        $tag = strtolower($element->tagName);

        if ($tag === 'meta' && $element->hasAttribute('http-equiv')) {
            $element->parentNode->removeChild($element);

            return;
        }

        if ($tag === 'link' && ! self::isSameOriginStylesheet($element)) {
            $element->parentNode->removeChild($element);

            return;
        }

        if ($tag === 'style') {
            $css = (string) $element->textContent;
            if (preg_match(self::DANGEROUS_CSS, $css) === 1) {
                $element->textContent = (string) preg_replace(self::DANGEROUS_CSS, '', $css);
            }
        }

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->name);
            $value = (string) $attribute->value;

            if (str_starts_with($name, 'on')) {
                $element->removeAttribute($attribute->name);

                continue;
            }

            if ($name === 'style' && preg_match(self::DANGEROUS_CSS, $value) === 1) {
                $element->removeAttribute($attribute->name);

                continue;
            }

            if (in_array($name, self::URL_ATTRIBUTES, true) && ! self::isSafeUrl($value, $tag === 'img' && $name === 'src')) {
                $element->removeAttribute($attribute->name);
            }
        }

        if ($tag === 'a' && $element->hasAttribute('href')) {
            $element->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private static function isSafeUrl(string $value, bool $allowDataImage): bool
    {
        // Spasi/karakter kontrol di dalam skema ("java\tscript:") diabaikan browser, jadi buang dulu.
        $normalized = strtolower((string) preg_replace('/[\x00-\x20\x7f]+/', '', $value));

        if ($normalized === '') {
            return true;
        }

        if (str_starts_with($normalized, '//')) {
            return false;
        }

        if (preg_match('/^([a-z][a-z0-9+.\-]*):/', $normalized, $m) === 1) {
            if (in_array($m[1], ['http', 'https', 'mailto', 'tel'], true)) {
                return true;
            }

            return $allowDataImage && preg_match('#^data:image/(png|jpe?g|gif|webp);base64,#', $normalized) === 1;
        }

        return true;
    }

    private static function isSameOriginStylesheet(DOMElement $link): bool
    {
        if (! str_contains(strtolower($link->getAttribute('rel')), 'stylesheet')) {
            return false;
        }

        $href = (string) preg_replace('/[\x00-\x20\x7f]+/', '', $link->getAttribute('href'));

        return str_starts_with($href, '/') && ! str_starts_with($href, '//');
    }
}
