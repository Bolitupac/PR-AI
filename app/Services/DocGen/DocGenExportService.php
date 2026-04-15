<?php

namespace App\Services\DocGen;

use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class DocGenExportService
{
    public function export(string $format, string $markdown, ?string $title = null): array
    {
        $normalizedFormat = strtolower(trim($format));
        $normalizedFormat = $normalizedFormat === 'md' ? 'markdown' : $normalizedFormat;
        $normalizedFormat = $normalizedFormat === 'tex' ? 'latex' : $normalizedFormat;

        $safeTitle = $this->sanitizeFilename($title ?: 'docgen-export');
        $plainText = $this->markdownToPlainText($markdown);
        $html = $this->markdownToHtml($markdown, $safeTitle);

        return match ($normalizedFormat) {
            'pdf' => $this->buildBinary("{$safeTitle}.pdf", 'application/pdf', $this->buildPdf($plainText)),
            'docx' => $this->buildBinary("{$safeTitle}.docx", 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', $this->buildDocx($plainText)),
            'markdown' => $this->buildBinary("{$safeTitle}.md", 'text/markdown; charset=UTF-8', $markdown),
            'txt' => $this->buildBinary("{$safeTitle}.txt", 'text/plain; charset=UTF-8', $plainText),
            'html' => $this->buildBinary("{$safeTitle}.html", 'text/html; charset=UTF-8', $html),
            'json' => $this->buildBinary("{$safeTitle}.json", 'application/json', $this->buildJson($safeTitle, $markdown, $plainText)),
            'yaml' => $this->buildBinary("{$safeTitle}.yaml", 'application/x-yaml; charset=UTF-8', $this->buildYaml($safeTitle, $markdown, $plainText)),
            'csv' => $this->buildBinary("{$safeTitle}.csv", 'text/csv; charset=UTF-8', $this->buildCsv($plainText)),
            'xlsx' => $this->buildBinary("{$safeTitle}.xlsx", 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $this->buildXlsx($plainText)),
            'pptx' => $this->buildBinary("{$safeTitle}.pptx", 'application/vnd.openxmlformats-officedocument.presentationml.presentation', $this->buildPptx($plainText, $safeTitle)),
            'latex' => $this->buildBinary("{$safeTitle}.tex", 'application/x-tex; charset=UTF-8', $this->buildLatex($safeTitle, $plainText)),
            default => throw new RuntimeException('Unsupported export format.'),
        };
    }

    private function buildBinary(string $filename, string $mime, string $content): array
    {
        return [
            'filename' => $filename,
            'mime' => $mime,
            'content' => $content,
        ];
    }

    private function sanitizeFilename(string $value): string
    {
        $slug = Str::slug($value);
        return $slug !== '' ? $slug : 'docgen-export';
    }

    private function markdownToPlainText(string $markdown): string
    {
        $text = preg_replace('/```[\s\S]*?```/m', '', $markdown) ?? '';
        $text = preg_replace('/^#{1,6}\s*/m', '', $text) ?? $text;
        $text = preg_replace('/[*_`>#-]/', '', $text) ?? $text;
        $text = preg_replace('/\[(.*?)\]\((.*?)\)/', '$1 ($2)', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;
        return trim($text);
    }

    private function markdownToHtml(string $markdown, string $title): string
    {
        $lines = preg_split("/\r\n|\n|\r/", $markdown) ?: [];
        $html = [];
        $inList = false;
        $inCode = false;
        $codeLines = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (str_starts_with($trimmed, '```')) {
                if ($inCode) {
                    $html[] = '<pre><code>'.e(implode("\n", $codeLines)).'</code></pre>';
                    $inCode = false;
                    $codeLines = [];
                } else {
                    $inCode = true;
                }
                continue;
            }

            if ($inCode) {
                $codeLines[] = $line;
                continue;
            }

            if (preg_match('/^\s*[-*]\s+(.+)$/', $line, $matches)) {
                if (!$inList) {
                    $html[] = '<ul>';
                    $inList = true;
                }
                $html[] = '<li>'.$this->formatInline($matches[1]).'</li>';
                continue;
            }

            if ($inList) {
                $html[] = '</ul>';
                $inList = false;
            }

            if ($trimmed === '') {
                continue;
            }

            if (preg_match('/^(#{1,6})\s+(.+)$/', $trimmed, $matches)) {
                $level = strlen($matches[1]);
                $html[] = sprintf('<h%d>%s</h%d>', $level, $this->formatInline($matches[2]), $level);
                continue;
            }

            $html[] = '<p>'.$this->formatInline($trimmed).'</p>';
        }

        if ($inList) {
            $html[] = '</ul>';
        }

        return '<!doctype html><html><head><meta charset="utf-8"><title>'.e($title).'</title></head><body>'
            .implode('', $html)
            .'</body></html>';
    }

    private function formatInline(string $text): string
    {
        $escaped = e($text);
        $escaped = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $escaped) ?? $escaped;
        $escaped = preg_replace('/`(.+?)`/', '<code>$1</code>', $escaped) ?? $escaped;
        return $escaped;
    }

    private function buildJson(string $title, string $markdown, string $plainText): string
    {
        return (string) json_encode([
            'title' => $title,
            'markdown' => $markdown,
            'plain_text' => $plainText,
            'exported_at' => now()->toIso8601String(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function buildYaml(string $title, string $markdown, string $plainText): string
    {
        $escape = static function (string $value): string {
            return str_replace('"', '\"', $value);
        };

        return implode("\n", [
            'title: "'.$escape($title).'"',
            'exported_at: "'.now()->toIso8601String().'"',
            'plain_text: |',
            ...array_map(fn ($line) => '  '.$line, preg_split("/\r\n|\n|\r/", $plainText) ?: []),
            'markdown: |',
            ...array_map(fn ($line) => '  '.$line, preg_split("/\r\n|\n|\r/", $markdown) ?: []),
        ]);
    }

    private function buildCsv(string $plainText): string
    {
        $rows = preg_split("/\r\n|\n|\r/", $plainText) ?: [];
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['line_number', 'content']);
        foreach ($rows as $index => $row) {
            fputcsv($handle, [$index + 1, $row]);
        }
        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);
        return $csv;
    }

    private function buildLatex(string $title, string $plainText): string
    {
        $escapedTitle = $this->escapeLatex($title);
        $escapedBody = implode("\\\\\n", array_map(fn ($line) => $this->escapeLatex($line), preg_split("/\r\n|\n|\r/", $plainText) ?: []));

        return "\\documentclass{article}\n"
            ."\\usepackage[utf8]{inputenc}\n"
            ."\\begin{document}\n"
            ."\\title{{$escapedTitle}}\n"
            ."\\maketitle\n"
            ."{$escapedBody}\n"
            ."\\end{document}\n";
    }

    private function escapeLatex(string $value): string
    {
        return strtr($value, [
            '\\' => '\\textbackslash{}',
            '{' => '\{',
            '}' => '\}',
            '$' => '\$',
            '&' => '\&',
            '%' => '\%',
            '#' => '\#',
            '_' => '\_',
        ]);
    }

    private function buildPdf(string $plainText): string
    {
        $lines = preg_split("/\r\n|\n|\r/", $plainText) ?: [];
        $content = "BT\n/F1 12 Tf\n14 TL\n72 760 Td\n";
        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $content .= "T*\n";
            }
            $content .= '(' . $this->escapePdfText($line) . ") Tj\n";
        }
        $content .= "ET";

        $objects = [];
        $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>";
        $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $objects[] = "<< /Length ".strlen($content)." >>\nstream\n{$content}\nendstream";

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $index => $object) {
            $offsets[$index + 1] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n{$object}\nendobj\n";
        }

        $xrefStart = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf('%010d 00000 n ', $offset)."\n";
        }
        $pdf .= "trailer << /Size ".(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefStart}\n%%EOF";

        return $pdf;
    }

    private function escapePdfText(string $value): string
    {
        $converted = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value);
        $safe = $converted === false ? $value : $converted;
        return str_replace(['\\', '(', ')'], ['\\\\', '\(', '\)'], $safe);
    }

    private function buildDocx(string $plainText): string
    {
        $document = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas" '
            .'xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" '
            .'xmlns:o="urn:schemas-microsoft-com:office:office" '
            .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" '
            .'xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math" '
            .'xmlns:v="urn:schemas-microsoft-com:vml" '
            .'xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing" '
            .'xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" '
            .'xmlns:w10="urn:schemas-microsoft-com:office:word" '
            .'xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" '
            .'xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml" '
            .'xmlns:wpg="http://schemas.microsoft.com/office/word/2010/wordprocessingGroup" '
            .'xmlns:wpi="http://schemas.microsoft.com/office/word/2010/wordprocessingInk" '
            .'xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml" '
            .'xmlns:wps="http://schemas.microsoft.com/office/word/2010/wordprocessingShape" mc:Ignorable="w14 wp14">'
            .'<w:body>';

        foreach (preg_split("/\r\n|\n|\r/", $plainText) ?: [] as $line) {
            $document .= '<w:p><w:r><w:t xml:space="preserve">'.e($line).'</w:t></w:r></w:p>';
        }

        $document .= '<w:sectPr><w:pgSz w:w="12240" w:h="15840"/><w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440"/></w:sectPr></w:body></w:document>';

        return $this->buildZipArchive([
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8"?>'
                .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                .'<Default Extension="xml" ContentType="application/xml"/>'
                .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
                .'</Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8"?>'
                .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
                .'</Relationships>',
            'word/document.xml' => $document,
        ]);
    }

    private function buildXlsx(string $plainText): string
    {
        $rowsXml = [];
        foreach (preg_split("/\r\n|\n|\r/", $plainText) ?: [] as $index => $line) {
            $rowNumber = $index + 1;
            $rowsXml[] = '<row r="'.$rowNumber.'"><c r="A'.$rowNumber.'" t="inlineStr"><is><t>'.e($line).'</t></is></c></row>';
        }

        return $this->buildZipArchive([
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8"?>'
                .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                .'<Default Extension="xml" ContentType="application/xml"/>'
                .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
                .'</Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8"?>'
                .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
                .'</Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8"?>'
                .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                .'<sheets><sheet name="Document" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8"?>'
                .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
                .'</Relationships>',
            'xl/worksheets/sheet1.xml' => '<?xml version="1.0" encoding="UTF-8"?>'
                .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'
                .implode('', $rowsXml)
                .'</sheetData></worksheet>',
        ]);
    }

    private function buildPptx(string $plainText, string $title): string
    {
        $body = e(mb_substr($plainText, 0, 4000));

        return $this->buildZipArchive([
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8"?>'
                .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                .'<Default Extension="xml" ContentType="application/xml"/>'
                .'<Override PartName="/ppt/presentation.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.presentation.main+xml"/>'
                .'<Override PartName="/ppt/slides/slide1.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml"/>'
                .'</Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8"?>'
                .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="ppt/presentation.xml"/>'
                .'</Relationships>',
            'ppt/presentation.xml' => '<?xml version="1.0" encoding="UTF-8"?>'
                .'<p:presentation xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
                .'<p:sldIdLst><p:sldId id="256" r:id="rId1"/></p:sldIdLst></p:presentation>',
            'ppt/_rels/presentation.xml.rels' => '<?xml version="1.0" encoding="UTF-8"?>'
                .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slide" Target="slides/slide1.xml"/>'
                .'</Relationships>',
            'ppt/slides/slide1.xml' => '<?xml version="1.0" encoding="UTF-8"?>'
                .'<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
                .'<p:cSld><p:spTree>'
                .'<p:sp><p:nvSpPr><p:cNvPr id="2" name="Title"/><p:cNvSpPr/><p:nvPr/></p:nvSpPr><p:spPr/>'
                .'<p:txBody><a:bodyPr/><a:lstStyle/><a:p><a:r><a:t>'.e($title).'</a:t></a:r></a:p></p:txBody></p:sp>'
                .'<p:sp><p:nvSpPr><p:cNvPr id="3" name="Content"/><p:cNvSpPr/><p:nvPr/></p:nvSpPr><p:spPr/>'
                .'<p:txBody><a:bodyPr/><a:lstStyle/><a:p><a:r><a:t>'.$body.'</a:t></a:r></a:p></p:txBody></p:sp>'
                .'</p:spTree></p:cSld></p:sld>',
        ]);
    }

    private function buildZipArchive(array $files): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('ZipArchive is required for this export format.');
        }

        $tmp = tempnam(sys_get_temp_dir(), 'docgen_');
        if ($tmp === false) {
            throw new RuntimeException('Unable to create temporary file.');
        }

        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($files as $path => $content) {
            $zip->addFromString($path, $content);
        }
        $zip->close();

        $binary = file_get_contents($tmp);
        @unlink($tmp);

        if ($binary === false) {
            throw new RuntimeException('Failed to read generated archive.');
        }

        return $binary;
    }
}
