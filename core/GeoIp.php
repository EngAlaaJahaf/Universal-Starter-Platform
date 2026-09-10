<?php

/**
 * GeoIp — compact offline IP → Country resolver.
 *
 * Reads storage/data/geoip_db.bin (built by tools/build_geoip_db.php from
 * IP2Location-Lite country data) using a binary search over a sorted array of
 * IPv4 ranges. No external HTTP calls, tiny memory footprint (fseek-based),
 * and results are cached per request. Unknown ranges return null.
 */
class GeoIp
{
    private const HEADER_LEN = 6; // 'GIP1' + uint16 cc count
    private const REC_LEN = 9;    // start(4) end(4) ccIndex(1)

    /** File path of the database. */
    protected static $file = null;

    /** Opened handle (lazy). */
    protected static $fh = null;

    /** Country table: index => CC. */
    protected static $table = null;

    /** Validated country codes for flag emoji lookup (ISO-3166). */
    protected static $flagCodes = null;

    /** Per-request result cache: IP => [code, index]. */
    protected static $cache = [];

    /** Load the DB file. Returns false if unavailable. */
    protected static function open(): bool
    {
        if (self::$file === null) {
            self::$file = dirname(__DIR__) . '/storage/data/geoip_db.bin';
        }
        if (!is_file(self::$file)) {
            return false;
        }
        if (self::$fh === null) {
            $fh = @fopen(self::$file, 'rb');
            if (!$fh) {
                return false;
            }
            $magic = fread($fh, 4);
            if ($magic !== 'GIP1') {
                fclose($fh);
                return false;
            }
            $ccCount = unpack('n', fread($fh, 2))[1] ?? 0;
            $tableRaw = @fread($fh, $ccCount * 2);
            if ($ccCount === 0 || strlen($tableRaw) !== $ccCount * 2) {
                fclose($fh);
                return false;
            }
            for ($i = 0; $i < $ccCount; $i++) {
                self::$table[$i] = strtoupper(substr($tableRaw, $i * 2, 2));
            }
            self::$flagCodes = array_flip(self::$table);
            self::$fh = $fh;
        }
        return true;
    }

    /** Num of records. */
    protected static function recordCount(): int
    {
        if (!self::$fh) {
            return 0;
        }
        $size = 0;
        $pos = 0;
        // The end-of-file is header + table + records.
        fseek(self::$fh, 0, SEEK_END);
        $size = ftell(self::$fh);
        $tableLen = count(self::$table) * 2;
        return max(0, intdiv($size - self::HEADER_LEN - $tableLen, self::REC_LEN));
    }

    /** Read a single record at zero-based index (start, end, ccIndex). */
    protected static function readRecord(int $i): ?array
    {
        if (!self::$fh) {
            return null;
        }
        $tableLen = count(self::$table) * 2;
        $offset = self::HEADER_LEN + $tableLen + $i * self::REC_LEN;
        fseek(self::$fh, $offset);
        $data = fread(self::$fh, self::REC_LEN);
        if (strlen($data) !== self::REC_LEN) {
            return null;
        }
        return unpack('Nstart/Nend/Ccc', $data);
    }

    /**
     * Binary search for the range containing $ipLong.
     * Returns CC string, or null.
     */
    protected static function search(int $ipLong): ?string
    {
        if (!self::open()) {
            return null;
        }
        $lo = 0;
        $hi = self::recordCount() - 1;
        while ($lo <= $hi) {
            $mid = intdiv($lo + $hi, 2);
            $rec = self::readRecord($mid);
            if ($rec === null) {
                return null;
            }
            if ($ipLong < $rec['start']) {
                $hi = $mid - 1;
            } elseif ($ipLong > $rec['end']) {
                $lo = $mid + 1;
            } else {
                return self::$table[$rec['cc']] ?? null;
            }
        }
        return null;
    }

    /** Resolve an IP (v4 dotted quad, or v6 containing an embedded v4) to CC. */
    public static function lookup(?string $ip): ?string
    {
        $ip = trim((string) ($ip ?? ''));
        if ($ip === '' || $ip === '127.0.0.1' || $ip === '::1') {
            return null;
        }

        if (isset(self::$cache[$ip])) {
            return self::$cache[$ip];
        }

        $long = ip2long($ip);
        if ($long === false) {
            // Try IPv6 with embedded IPv4 (e.g. ::ffff:1.2.3.4)
            if (strpos($ip, ':') !== false && preg_match('/(\d{1,3}(?:\.\d{1,3}){3})$/', $ip, $m)) {
                $long = ip2long($m[1]);
            } else {
                return null;
            }
        }
        if ($long === false) {
            return null;
        }

        // ip2long returns negative for high addresses on 32-bit-safe ints; cast.
        $long = (int) $long;
        if ($long < 0) {
            $long += 0x100000000;
        }

        $code = self::search($long);

        // Simple per-request LRU-ish cache.
        if (count(self::$cache) >= 2000) {
            self::$cache = [];
        }
        self::$cache[$ip] = $code;
        return $code;
    }

    /** Emoji flag for a code; globe fallback. */
    public static function flag(?string $code): string
    {
        static $map = [
            'SA' => '🇸🇦', 'AE' => '🇦🇪', 'EG' => '🇪🇬', 'YE' => '🇾🇪', 'IQ' => '🇮🇶',
            'JO' => '🇯🇴', 'LB' => '🇱🇧', 'SY' => '🇸🇾', 'PS' => '🇵🇸', 'KW' => '🇰🇼',
            'QA' => '🇶🇦', 'BH' => '🇧🇭', 'OM' => '🇴🇲', 'MA' => '🇲🇦', 'DZ' => '🇩🇿',
            'TN' => '🇹🇳', 'LY' => '🇱🇾', 'SD' => '🇸🇩', 'MR' => '🇲🇷', 'SO' => '🇸🇴',
            'DJ' => '🇩🇯', 'KM' => '🇰🇲', 'TR' => '🇹🇷', 'US' => '🇺🇸', 'GB' => '🇬🇧',
            'DE' => '🇩🇪', 'NL' => '🇳🇱', 'FR' => '🇫🇷', 'IE' => '🇮🇪', 'CA' => '🇨🇦',
            'AU' => '🇦🇺', 'SG' => '🇸🇬', 'JP' => '🇯🇵', 'KR' => '🇰🇷', 'CN' => '🇨🇳',
            'RU' => '🇷🇺', 'IN' => '🇮🇳', 'PK' => '🇵🇰', 'BR' => '🇧🇷', 'ES' => '🇪🇸',
            'IT' => '🇮🇹', 'ZA' => '🇿🇦', 'MX' => '🇲🇽', 'NG' => '🇳🇬',
        ];
        return $map[$code] ?? '🌐';
    }

    /** Arabic country name for a code; falls back to the code itself. */
    public static function countryName(?string $code): string
    {
        static $names = [
            'SA' => 'السعودية', 'AE' => 'الإمارات', 'EG' => 'مصر', 'YE' => 'اليمن',
            'IQ' => 'العراق', 'JO' => 'الأردن', 'LB' => 'لبنان', 'SY' => 'سوريا',
            'PS' => 'فلسطين', 'KW' => 'الكويت', 'QA' => 'قطر', 'BH' => 'البحرين',
            'OM' => 'عُمان', 'MA' => 'المغرب', 'DZ' => 'الجزائر', 'TN' => 'تونس',
            'LY' => 'ليبيا', 'SD' => 'السودان', 'MR' => 'موريتانيا', 'SO' => 'الصومال',
            'DJ' => 'جيبوتي', 'KM' => 'جزر القمر', 'TR' => 'تركيا', 'US' => 'الولايات المتحدة',
            'GB' => 'بريطانيا', 'DE' => 'ألمانيا', 'NL' => 'هولندا', 'FR' => 'فرنسا',
            'IE' => 'أيرلندا', 'CA' => 'كندا', 'AU' => 'أستراليا', 'SG' => 'سنغافورة',
            'JP' => 'اليابان', 'KR' => 'كوريا الجنوبية', 'CN' => 'الصين', 'RU' => 'روسيا',
            'IN' => 'الهند', 'PK' => 'باكستان', 'BR' => 'البرازيل', 'ES' => 'إسبانيا',
            'IT' => 'إيطاليا', 'ZA' => 'جنوب أفريقيا', 'MX' => 'المكسيك', 'NG' => 'نيجيريا',
        ];
        return $names[$code] ?? '';
    }

    /** Human-friendly HTML label for an IP cell (IP + country badge). */
    public static function label(?string $ip): string
    {
        $ip = trim((string) ($ip ?? ''));
        if ($ip === '') {
            $ip = '127.0.0.1';
        }
        $dot = htmlspecialchars($ip, ENT_QUOTES, 'UTF-8');
        $badge = '<span class="badge bg-light text-muted border font-monospace" style="font-size:0.75rem" dir="ltr">' . $dot . '</span>';

        $code = self::lookup($ip);
        if (!$code) {
            return $badge;
        }
        $name = self::countryName($code);
        $flag = self::flag($code);
        return '<span dir="ltr" style="white-space:nowrap">'
            . $badge
            . ' <span class="badge bg-info-subtle text-info border ms-1" style="font-size:0.7rem" title="'
            . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">'
            . $flag . ' ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8')
            . '</span></span>';
    }
}

/** Convenience alias used by admin monitoring views. */
if (!function_exists('ip_country_label')) {
    function ip_country_label(?string $ip): string
    {
        return GeoIp::label($ip);
    }
}