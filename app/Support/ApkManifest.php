<?php

namespace App\Support;

use ZipArchive;

/**
 * Lit le package, le versionCode et le versionName déclarés dans un APK, sans
 * outil externe : AndroidManifest.xml y est stocké au format XML binaire
 * d'Android (AXML). Seule la balise <manifest> est décodée.
 */
class ApkManifest
{
    /** @return array{package: ?string, version_code: ?int, version_name: ?string}|null */
    public static function lire(string $apk): ?array
    {
        $zip = new ZipArchive;
        if ($zip->open($apk) !== true) {
            return null;
        }
        $xml = $zip->getFromName('AndroidManifest.xml');
        $zip->close();

        return is_string($xml) ? self::decoder($xml) : null;
    }

    /** @return array{package: ?string, version_code: ?int, version_name: ?string}|null */
    public static function decoder(string $d): ?array
    {
        $len = strlen($d);
        if ($len < 8 || self::u16($d, 0) !== 0x0003) {
            return null;
        }

        $chaines = [];
        $pos = self::u16($d, 2);
        while ($pos + 8 <= $len) {
            $type = self::u16($d, $pos);
            $taille = self::u32($d, $pos + 4);
            if ($taille < 8) {
                return null;
            }

            if ($type === 0x0001) { // table des chaînes
                $chaines = self::chaines($d, $pos);
            } elseif ($type === 0x0102) { // début d'élément
                $nom = $chaines[self::u32($d, $pos + 20)] ?? null;
                if ($nom === 'manifest') {
                    return self::attributs($d, $pos, $chaines);
                }
            }
            $pos += $taille;
        }

        return null;
    }

    private static function attributs(string $d, int $pos, array $chaines): array
    {
        $debut = $pos + 16 + self::u16($d, $pos + 24);
        $taille = self::u16($d, $pos + 26);
        $nombre = self::u16($d, $pos + 28);
        $res = ['package' => null, 'version_code' => null, 'version_name' => null];

        for ($i = 0; $i < $nombre; $i++) {
            $a = $debut + $i * $taille;
            $nom = $chaines[self::u32($d, $a + 4)] ?? null;
            $brut = self::u32($d, $a + 8);
            $typeValeur = ord($d[$a + 15]);
            $valeur = self::u32($d, $a + 16);
            $texte = $brut !== 0xFFFFFFFF ? ($chaines[$brut] ?? null) : null;

            match ($nom) {
                'package' => $res['package'] = $texte,
                'versionName' => $res['version_name'] = $texte ?? ($typeValeur === 0x03 ? ($chaines[$valeur] ?? null) : null),
                'versionCode' => $res['version_code'] = $typeValeur >= 0x10 && $typeValeur <= 0x1F ? $valeur : ($texte !== null ? (int) $texte : null),
                default => null,
            };
        }

        return $res;
    }

    private static function chaines(string $d, int $pos): array
    {
        $nombre = self::u32($d, $pos + 8);
        $utf8 = (self::u32($d, $pos + 16) & 0x100) !== 0;
        $debutChaines = $pos + self::u32($d, $pos + 20);
        $index = $pos + self::u16($d, $pos + 2);
        $chaines = [];

        for ($i = 0; $i < $nombre; $i++) {
            $p = $debutChaines + self::u32($d, $index + $i * 4);
            if ($utf8) {
                $p += (ord($d[$p]) & 0x80) ? 2 : 1;          // longueur en caractères
                $n = ord($d[$p]);
                if ($n & 0x80) {
                    $n = (($n & 0x7F) << 8) | ord($d[$p + 1]);
                    $p += 2;
                } else {
                    $p += 1;
                }
                $chaines[] = substr($d, $p, $n);
            } else {
                $n = self::u16($d, $p);
                $p += 2;
                if ($n & 0x8000) {
                    $n = (($n & 0x7FFF) << 16) | self::u16($d, $p);
                    $p += 2;
                }
                $chaines[] = (string) mb_convert_encoding(substr($d, $p, $n * 2), 'UTF-8', 'UTF-16LE');
            }
        }

        return $chaines;
    }

    private static function u16(string $d, int $o): int
    {
        return unpack('v', substr($d, $o, 2))[1];
    }

    private static function u32(string $d, int $o): int
    {
        return unpack('V', substr($d, $o, 4))[1];
    }
}
