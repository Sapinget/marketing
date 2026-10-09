<?php

namespace App\Support;

use DOMDocument;
use DOMElement;

/**
 * Append-only view over xl/sharedStrings.xml used when rewriting a workbook.
 */
class SharedStringTable
{
    private const NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    /** @var array<int, string> */
    private array $strings = [];

    /** @var array<string, int> */
    private array $lookup = [];

    private int $addedRefs = 0;

    public function __construct(private readonly DOMDocument $doc)
    {
        foreach ($doc->documentElement->childNodes as $si) {
            if (! $si instanceof DOMElement || $si->localName !== 'si') {
                continue;
            }
            $text = '';
            foreach ($si->getElementsByTagNameNS(self::NS, 't') as $t) {
                if ($t->parentNode instanceof DOMElement && $t->parentNode->localName === 'rPh') {
                    continue;
                }
                $text .= $t->textContent;
            }
            $this->strings[] = $text;
            // Only plain (non rich-text) entries are safe to reuse for new cells.
            if (! isset($this->lookup[$text]) && $si->getElementsByTagNameNS(self::NS, 'r')->length === 0) {
                $this->lookup[$text] = count($this->strings) - 1;
            }
        }
    }

    /**
     * @return array<int, string>
     */
    public function all(): array
    {
        return $this->strings;
    }

    public function indexOf(string $value): int
    {
        $this->addedRefs++;

        if (isset($this->lookup[$value])) {
            return $this->lookup[$value];
        }

        $si = $this->doc->createElementNS(self::NS, 'si');
        $t = $this->doc->createElementNS(self::NS, 't');
        $t->appendChild($this->doc->createTextNode($value));
        if ($value !== trim($value) || str_contains($value, "\n")) {
            $t->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
        }
        $si->appendChild($t);
        $this->doc->documentElement->appendChild($si);

        $this->strings[] = $value;

        return $this->lookup[$value] = count($this->strings) - 1;
    }

    public function save(): string
    {
        // count is advisory; keep uniqueCount exact and count >= uniqueCount.
        $root = $this->doc->documentElement;
        $root->setAttribute('uniqueCount', (string) count($this->strings));
        $root->setAttribute('count', (string) max(count($this->strings), (int) $root->getAttribute('count') + $this->addedRefs));

        return $this->doc->saveXML();
    }
}
