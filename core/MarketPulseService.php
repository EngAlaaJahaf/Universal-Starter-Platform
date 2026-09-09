<?php

class MarketPulseService
{
    private static $cacheFile = __DIR__ . '/../storage/cache/market_pulse.json';
    private static $cacheTTL = 300; // 5 minutes cache

    public static function getPulseData($forceRefresh = false)
    {
        // 1. Check local cache
        if (!$forceRefresh && file_exists(self::$cacheFile)) {
            $raw = @file_get_contents(self::$cacheFile);
            if (!empty($raw)) {
                $cached = json_decode($raw, true);
                if (isset($cached['time']) && (time() - $cached['time'] < self::$cacheTTL) && !empty($cached['data'])) {
                    return $cached['data'];
                }
            }
        }

        // 2. Fetch fresh live data
        $liveData = self::fetchFreshMarketData();

        if (!empty($liveData)) {
            $payload = [
                'time' => time(),
                'updated_at' => date('Y-m-d H:i:s'),
                'data' => $liveData
            ];
            @file_put_contents(self::$cacheFile, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return $liveData;
        }

        // 3. Fallback to cached data if network failed, or robust baseline
        if (file_exists(self::$cacheFile)) {
            $raw = @file_get_contents(self::$cacheFile);
            $cached = json_decode($raw, true);
            if (!empty($cached['data'])) {
                return $cached['data'];
            }
        }

        return self::getDefaultBaseline();
    }

    private static function fetchFreshMarketData()
    {
        $results = [];

        // A) Fetch Cryptocurrencies (BTC, ETH, SOL) via Binance API
        try {
            $cryptoJson = self::curlGet('https://api.binance.com/api/v3/ticker/24hr?symbols=[%22BTCUSDT%22,%22ETHUSDT%22,%22SOLUSDT%22]', 4);
            if ($cryptoJson) {
                $cryptoList = json_decode($cryptoJson, true);
                if (is_array($cryptoList)) {
                    foreach ($cryptoList as $item) {
                        $sym = $item['symbol'] ?? '';
                        $price = (float) ($item['lastPrice'] ?? 0);
                        $pct = (float) ($item['priceChangePercent'] ?? 0);
                        $up = $pct >= 0;

                        if ($sym === 'BTCUSDT') {
                            $results[] = [
                                's'    => 'BTC',
                                'name' => 'بيتكوين',
                                'v'    => '$' . number_format($price, 0),
                                'c'    => ($up ? '+' : '') . number_format($pct, 2) . '%',
                                'up'   => $up
                            ];
                        } elseif ($sym === 'ETHUSDT') {
                            $results[] = [
                                's'    => 'ETH',
                                'name' => 'إيثيريوم',
                                'v'    => '$' . number_format($price, 2),
                                'c'    => ($up ? '+' : '') . number_format($pct, 2) . '%',
                                'up'   => $up
                            ];
                        } elseif ($sym === 'SOLUSDT') {
                            $results[] = [
                                's'    => 'SOL',
                                'name' => 'سولانا',
                                'v'    => '$' . number_format($price, 2),
                                'c'    => ($up ? '+' : '') . number_format($pct, 2) . '%',
                                'up'   => $up
                            ];
                        }
                    }
                }
            }
        } catch (Throwable $e) {}

        // B) Fetch Tech Stocks (NVDA, AAPL, MSFT, GOOGL, TSLA) via Yahoo Finance Chart v8
        $stocks = [
            'NVDA'  => 'إنفيديا',
            'AAPL'  => 'آبل',
            'MSFT'  => 'مايكروسوفت',
            'GOOGL' => 'جوجل',
            'TSLA'  => 'تسلا'
        ];

        foreach ($stocks as $symbol => $arabicName) {
            try {
                $stockJson = self::curlGet("https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}?interval=1d&range=1d", 3);
                if ($stockJson) {
                    $stockData = json_decode($stockJson, true);
                    $meta = $stockData['chart']['result'][0]['meta'] ?? [];
                    if (!empty($meta['regularMarketPrice'])) {
                        $price = (float) $meta['regularMarketPrice'];
                        $prevClose = (float) ($meta['chartPreviousClose'] ?? $meta['previousClose'] ?? $price);
                        $change = $price - $prevClose;
                        $pct = $prevClose > 0 ? ($change / $prevClose) * 100 : 0;
                        $up = $pct >= 0;

                        $results[] = [
                            's'    => $symbol,
                            'name' => $arabicName,
                            'v'    => '$' . number_format($price, 2),
                            'c'    => ($up ? '+' : '') . number_format($pct, 2) . '%',
                            'up'   => $up
                        ];
                    }
                }
            } catch (Throwable $e) {}
        }

        // C) Composite AI Index
        if (!empty($results)) {
            // Calculate weighted average of tech movers for AI Index
            $totalPct = 0;
            $count = 0;
            foreach ($results as $r) {
                if (in_array($r['s'], ['NVDA', 'MSFT', 'GOOGL', 'AAPL'])) {
                    $pctVal = (float) str_replace(['+', '%'], '', $r['c']);
                    $totalPct += $pctVal;
                    $count++;
                }
            }
            $aiAvgPct = $count > 0 ? ($totalPct / $count) : 1.25;
            $aiUp = $aiAvgPct >= 0;
            $results[] = [
                's'    => 'AI_INDEX',
                'name' => 'مؤشر الذكاء الاصطناعي',
                'v'    => number_format(3850 + ($aiAvgPct * 15), 1),
                'c'    => ($aiUp ? '+' : '') . number_format($aiAvgPct, 2) . '%',
                'up'   => $aiUp
            ];
        }

        return $results;
    }

    private static function curlGet($url, $timeout = 4)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($httpCode === 200) ? $response : false;
    }

    private static function getDefaultBaseline()
    {
        return [
            ['s' => 'NVDA',     'name' => 'إنفيديا',       'v' => '$225.16',  'c' => '+3.4%', 'up' => true],
            ['s' => 'AAPL',     'name' => 'آبل',           'v' => '$305.93',  'c' => '+1.1%', 'up' => true],
            ['s' => 'MSFT',     'name' => 'مايكروسوفت',     'v' => '$495.40',  'c' => '-0.3%', 'up' => false],
            ['s' => 'GOOGL',    'name' => 'جوجل',          'v' => '$345.90',  'c' => '+2.2%', 'up' => true],
            ['s' => 'BTC',      'name' => 'بيتكوين',       'v' => '$62,980',  'c' => '+4.8%', 'up' => true],
            ['s' => 'ETH',      'name' => 'إيثيريوم',      'v' => '$1,880',   'c' => '+2.9%', 'up' => true],
            ['s' => 'AI_INDEX', 'name' => 'مؤشر الذكاء الاصطناعي', 'v' => '3,842.1', 'c' => '+5.1%', 'up' => true]
        ];
    }
}
