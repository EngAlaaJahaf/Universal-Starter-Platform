<?php

/**
 * Builds storage/data/geoip_db.bin from an IP2Location-Lite-style CSV:
 *   <start_ip>,<end_ip>,<CC>
 *
 * Output is a compact binary file for fast binary-search lookups:
 *   header  : 'GIP1' (4) + uint16 countryCount
 *   country : countryCount * 2 bytes (ASCII CC)
 *   records : start(uint32 BE) + end(uint32 BE) + ccIndex(uint8)
 */

$usage = "Usage: php tools/build_geoip_db.php <source.csv.gz|source.csv> [output]\n";

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "must run from CLI\n");
    exit(1);
}

$src = $argv[1] ?? '';
$out = $argv[2] ?? (__DIR__ . '/../storage/data/geoip_db.bin');
if ($src === '') {
    fwrite(STDERR, $usage);
    exit(1);
}

/* Countries worth keeping: the Arab world + the biggest tech/cloud/crawler
 * origins. Any other IP is left unmapped (returns null). */
$keep = array_flip([
    'SA','AE','EG','YE','IQ','JO','LB','SY','PS','KW','QA','BH','OM',
    'MA','DZ','TN','LY','SD','MR','SO','DJ','KM',
    'US','GB','DE','NL','FR','IE','CA','AU','SG','JP','KR','CN','RU','IN',
    'TR','PK','BR','ES','IT','ZA','MX','NG',
]);

/* Expand gz if needed. */
if (preg_match('/\.gz$/i', $src)) {
    $fh = @gzopen($src, 'rb');
    if (!$fh) {
        fwrite(STDERR, "cannot open gz source: $src\n");
        exit(1);
    }
    $lines = [];
    while (!gzeof($fh)) {
        $line = trim(gzgets($fh, 8192));
        if ($line !== '') {
            $lines[] = $line;
        }
    }
    gzclose($fh);
} else {
    $lines = @file($src, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        fwrite(STDERR, "cannot open source: $src\n");
        exit(1);
    }
}

$records = [];
$ccIndex = [];   // CC => index
$byIx = [];      // index => CC
$skipped = 0;

foreach ($lines as $line) {
    $parts = explode(',', $line);
    if (count($parts) < 3) {
        $skipped++;
        continue;
    }
    $cc = strtoupper(trim($parts[2]));
    if (!isset($keep[$cc])) {
        continue;
    }
    $start = ip2long(trim($parts[0]));
    $end = ip2long(trim($parts[1]));
    if ($start === false || $end === false || $start > $end || $start < 0 || $end < 0) {
        $skipped++;
        continue;
    }
    if (!isset($ccIndex[$cc])) {
        $ccIndex[$cc] = count($byIx);
        $byIx[] = $cc;
    }
    $records[] = pack('NN', (int)$start, (int)$end) . chr($ccIndex[$cc]); // 9 bytes
}

if (empty($records)) {
    fwrite(STDERR, "no usable records\n");
    exit(1);
}

/* Sort ascending by start (binary search needs it). */
usort($records, function ($a, $b) {
    return unpack('Ns', $a)['s'] <=> unpack('Ns', $b)['s'];
});

$ccCount = count($byIx);
if ($ccCount > 256) {
    fwrite(STDERR, "too many countries (max 256)\n");
    exit(1);
}

$ccTable = '';
foreach ($byIx as $cc) {
    $ccTable .= substr($cc, 0, 2);
}

$header = 'GIP1' . pack('n', $ccCount) . $ccTable;
$bin = $header . implode('', $records);

$dir = dirname($out);
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}
$tmp = $out . '.tmp';
if (file_put_contents($tmp, $bin) === false) {
    fwrite(STDERR, "cannot write $tmp\n");
    exit(1);
}
rename($tmp, $out);

$mb = round(strlen($bin) / 1048576, 2);
echo "OK  records=" . count($records) . " countries=$ccCount size={$mb}MB -> $out\n";