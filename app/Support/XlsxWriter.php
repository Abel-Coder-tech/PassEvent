<?php

namespace App\Support;

use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

/**
 * Générateur minimal de fichiers Excel (.xlsx), sans dépendance : un .xlsx est
 * une archive ZIP de fichiers XML (Office Open XML).
 *
 * Gère plusieurs onglets, une ligne d'en-tête en gras, les montants en F CFA,
 * les dates et la largeur des colonnes.
 *
 *   (new XlsxWriter)
 *       ->onglet('Retraits', ['Date', 'Montant'], [[now(), 5000]], ['Montant' => 'montant'])
 *       ->telecharger('retraits.xlsx');
 */
class XlsxWriter
{
    // Index des styles déclarés dans styles.xml
    private const STYLE_TEXTE = 0;
    private const STYLE_ENTETE = 1;
    private const STYLE_MONTANT = 2;
    private const STYLE_DATE = 3;
    private const STYLE_TITRE = 4;
    private const STYLE_MONTANT_GRAS = 5;

    /** @var array<int, array{nom: string, xml: string}> */
    private array $onglets = [];

    /**
     * @param  array<int, string>  $entetes
     * @param  array<int, array<int, mixed>>  $lignes  valeurs : string, int/float, \DateTimeInterface ou null
     * @param  array<string, string>  $formats  par en-tête : 'montant' | 'date' | 'texte'
     * @param  array<int, string>  $titre  lignes de titre affichées au-dessus du tableau
     * @param  array<int, mixed>|null  $total  ligne de total (en gras) sous le tableau
     */
    public function onglet(string $nom, array $entetes, array $lignes, array $formats = [], array $titre = [], ?array $total = null): self
    {
        $styles = array_map(fn ($e) => match ($formats[$e] ?? 'texte') {
            'montant' => self::STYLE_MONTANT,
            'date' => self::STYLE_DATE,
            default => self::STYLE_TEXTE,
        }, $entetes);

        $rows = [];
        $r = 1;
        foreach ($titre as $ligneTitre) {
            $rows[] = $this->ligne($r++, [$ligneTitre], [self::STYLE_TITRE]);
        }
        if ($titre) {
            $r++; // ligne vide
        }
        $rows[] = $this->ligne($r++, $entetes, array_fill(0, count($entetes), self::STYLE_ENTETE));
        foreach ($lignes as $ligne) {
            $rows[] = $this->ligne($r++, array_values($ligne), $styles);
        }
        if ($total !== null) {
            $stylesTotal = array_map(fn ($s) => $s === self::STYLE_MONTANT ? self::STYLE_MONTANT_GRAS : self::STYLE_ENTETE, $styles);
            $rows[] = $this->ligne($r, array_values($total), $stylesTotal);
        }

        // Largeur des colonnes d'après le contenu le plus long (bornée)
        $largeurs = [];
        foreach (array_merge([$entetes], $lignes) as $ligne) {
            foreach (array_values($ligne) as $i => $v) {
                $len = $v instanceof \DateTimeInterface ? 17 : mb_strlen((string) (is_float($v) ? number_format($v, 0, ',', ' ').' F' : $v));
                $largeurs[$i] = max($largeurs[$i] ?? 8, min(60, $len + 3));
            }
        }
        $cols = '';
        foreach ($largeurs as $i => $l) {
            $n = $i + 1;
            $cols .= "<col min=\"{$n}\" max=\"{$n}\" width=\"{$l}\" customWidth=\"1\"/>";
        }

        $this->onglets[] = [
            'nom' => mb_substr(preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $nom), 0, 31),
            'xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                .($cols ? "<cols>{$cols}</cols>" : '')
                .'<sheetData>'.implode('', $rows).'</sheetData></worksheet>',
        ];

        return $this;
    }

    public function telecharger(string $nomFichier): BinaryFileResponse
    {
        $chemin = $this->enregistrer();

        return response()->download($chemin, $nomFichier, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // Écrit le fichier dans un dossier temporaire et renvoie son chemin.
    public function enregistrer(): string
    {
        if (! $this->onglets) {
            throw new RuntimeException('Aucun onglet à écrire.');
        }

        $chemin = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive;
        if ($zip->open($chemin, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Impossible de créer le fichier Excel.');
        }

        $sheets = $rels = $overrides = '';
        foreach ($this->onglets as $i => $onglet) {
            $n = $i + 1;
            $zip->addFromString("xl/worksheets/sheet{$n}.xml", $onglet['xml']);
            $sheets .= '<sheet name="'.$this->esc($onglet['nom'])."\" sheetId=\"{$n}\" r:id=\"rId{$n}\"/>";
            $rels .= "<Relationship Id=\"rId{$n}\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet\" Target=\"worksheets/sheet{$n}.xml\"/>";
            $overrides .= "<Override PartName=\"/xl/worksheets/sheet{$n}.xml\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml\"/>";
        }
        $nStyles = count($this->onglets) + 1;

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .$overrides.'</Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            ."<sheets>{$sheets}</sheets></workbook>");
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .$rels
            ."<Relationship Id=\"rId{$nStyles}\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles\" Target=\"styles.xml\"/>"
            .'</Relationships>');
        $zip->addFromString('xl/styles.xml', $this->styles());
        $zip->close();

        return $chemin;
    }

    /** @param array<int, mixed> $valeurs @param array<int, int> $styles */
    private function ligne(int $r, array $valeurs, array $styles): string
    {
        $cells = '';
        foreach ($valeurs as $i => $v) {
            $ref = $this->colonne($i).$r;
            $s = $styles[$i] ?? self::STYLE_TEXTE;
            if ($v === null || $v === '') {
                $cells .= "<c r=\"{$ref}\" s=\"{$s}\"/>";
            } elseif ($v instanceof \DateTimeInterface) {
                // Date Excel : jours depuis le 30/12/1899 (heure locale de l'application)
                $serial = ($v->getTimestamp() + $v->getOffset()) / 86400 + 25569;
                $cells .= "<c r=\"{$ref}\" s=\"".self::STYLE_DATE."\"><v>{$serial}</v></c>";
            } elseif (is_int($v) || is_float($v)) {
                $cells .= "<c r=\"{$ref}\" s=\"{$s}\"><v>{$v}</v></c>";
            } else {
                $cells .= "<c r=\"{$ref}\" s=\"{$s}\" t=\"inlineStr\"><is><t xml:space=\"preserve\">".$this->esc((string) $v).'</t></is></c>';
            }
        }

        return "<row r=\"{$r}\">{$cells}</row>";
    }

    private function colonne(int $i): string
    {
        $lettres = '';
        for ($i++; $i > 0; $i = intdiv($i - 1, 26)) {
            $lettres = chr(65 + ($i - 1) % 26).$lettres;
        }

        return $lettres;
    }

    private function esc(string $s): string
    {
        // Retire les caractères de contrôle interdits en XML
        $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $s) ?? '';

        return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<numFmts count="2">'
            .'<numFmt numFmtId="164" formatCode="#,##0 &quot;F&quot;"/>'
            .'<numFmt numFmtId="165" formatCode="dd/mm/yyyy hh:mm"/>'
            .'</numFmts>'
            .'<fonts count="3">'
            .'<font><sz val="11"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="13"/><color rgb="FF5B2A86"/><name val="Calibri"/></font>'
            .'</fonts>'
            .'<fills count="3">'
            .'<fill><patternFill patternType="none"/></fill>'
            .'<fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF6B3FA0"/><bgColor indexed="64"/></patternFill></fill>'
            .'</fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="6">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'                                   // texte
            .'<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'       // en-tête
            .'<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'           // montant
            .'<xf numFmtId="165" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'           // date
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>'                     // titre
            .'<xf numFmtId="164" fontId="1" fillId="2" borderId="0" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1"/>' // montant total
            .'</cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }
}
