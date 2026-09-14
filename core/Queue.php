<?php

/**
 * Queue — background jobs scaffold (SH-09, PART B — foundation only).
 *
 * Zero-dependency jobs table (`background_jobs`, see database/migrations_sh09_queue.sql)
 * so heavy work (RSS auto-sync, AI bulk translation) can be moved off the request
 * path in a later step. THIS RELEASE DOES NOT REWIRE any endpoint: AI/RSS flows
 * keep their current synchronous behaviour. The class only provides the building
 * block + a `craft queue:work` consumer.
 *
 * Storage:
 *   - "pdo"   → background_jobs table via Database::getInstance() (persistent).
 *   - "array" → in-memory store (used by tests / CLI without MySQL).
 *   Driver can be forced with Queue::setStore(); otherwise it auto-detects.
 */

class Queue
{
    protected static $store = null; // 'pdo' | 'array'

    /** @var array in-memory store for the "array" driver: id => row */
    protected static $memory = [];

    /** @var int auto-increment seq for the "array" driver */
    protected static $seq = 0;

    public static function table()
    {
        return 'background_jobs';
    }

    public static function setStore($store)
    {
        self::$store = ($store === 'pdo' || $store === 'array') ? $store : 'array';
    }

    public static function store()
    {
        if (self::$store !== null) {
            return self::$store;
        }
        try {
            Database::getInstance();
            self::$store = 'pdo';
        } catch (Throwable $e) {
            self::$store = 'array';
        }
        return self::$store;
    }

    /** Enqueue a job. Returns the new job id (int | false). */
    public static function push($handler, array $payload = [], $queue = 'default', $availableAt = null)
    {
        $availableAt = ($availableAt instanceof DateTimeInterface) ? $availableAt->format('Y-m-d H:i:s')
            : (($availableAt === null) ? date('Y-m-d H:i:s') : (string) $availableAt);

        if (self::store() === 'pdo') {
            $db = Database::getInstance();
            $db->query(
                'INSERT INTO `' . self::table() . '` (`queue`,`handler`,`payload`,`available_at`) VALUES (:q,:h,:p,:a)',
                [':q' => (string)$queue, ':h' => (string)$handler, ':p' => json_encode($payload, JSON_UNESCAPED_UNICODE), ':a' => $availableAt]
            );
            return (int) $db->lastInsertId();
        }

        self::$seq++;
        $id = self::$seq;
        self::$memory[$id] = [
            'id' => $id,
            'queue' => (string)$queue,
            'handler' => (string)$handler,
            'payload' => $payload,
            'status' => 'pending',
            'attempts' => 0,
            'max_attempts' => 3,
            'available_at' => $availableAt,
            'locked_at' => null,
            'error' => null,
            'result' => null,
        ];
        return $id;
    }

    /**
     * Claim the oldest available pending job of a queue.
     * Returns the job row (with decoded payload) or null when empty.
     */
    public static function claim($queue = 'default')
    {
        if (self::store() === 'pdo') {
            $db = Database::getInstance();
            // Claim best-effort: mark status='running' AND constrain the UPDATE on
            // status='pending' so two consumers can never both own the same row
            // (works on MySQL 5.7/8; production advice: MySQL 8 `SKIP LOCKED`).
            $row = $db->fetch(
                'SELECT * FROM `' . self::table() . '`
                 WHERE `queue` = :q AND `status` = \'pending\' AND `available_at` <= NOW()
                 ORDER BY `id` ASC LIMIT 1',
                [':q' => (string)$queue]
            );
            if (!$row) {
                return null;
            }
            $stmt = $db->query(
                'UPDATE `' . self::table() . '` SET `status` = \'running\', `attempts` = `attempts` + 1, `locked_at` = NOW() WHERE `id` = :id AND `status` = \'pending\'',
                [':id' => (int)$row['id']]
            );
            if ((int)$stmt->rowCount() !== 1) {
                return null; // another worker claimed it first
            }
            $row['status'] = 'running';
            $row['attempts'] = (int)$row['attempts'] + 1;
            $row['payload'] = json_decode((string)$row['payload'], true);
            return $row;
        }

        foreach (self::$memory as $id => $row) {
            if ($row['status'] === 'pending' && $row['queue'] === (string)$queue && strtotime($row['available_at']) <= time()) {
                self::$memory[$id]['status'] = 'running';
                self::$memory[$id]['attempts'] = (int)$row['attempts'] + 1;
                self::$memory[$id]['locked_at'] = date('Y-m-d H:i:s');
                return self::$memory[$id];
            }
        }
        return null;
    }

    public static function complete($id, $result = null)
    {
        self::finish($id, 'completed', null, $result);
    }

    /**
     * Mark a job failed. When attempts remain it is released back to 'pending'
     * with an exponential backoff; otherwise it becomes terminal 'failed'.
     */
    public static function fail($id, $error, $attemptsUsed = 0)
    {
        $maxAttempts = self::maxAttemptsFor($id);
        if ($attemptsUsed >= $maxAttempts) {
            self::finish($id, 'failed', (string)$error, null);
            return;
        }
        $delay = min(600, (int) (30 * max(1, $attemptsUsed)));
        self::release($id, $delay);
        self::finish($id, 'pending', (string)$error, null);
    }

    public static function maxAttemptsFor($id)
    {
        if (self::store() === 'pdo') {
            $r = Database::getInstance()->fetch(
                'SELECT `max_attempts` FROM `' . self::table() . '` WHERE `id` = :id LIMIT 1',
                [':id' => (int)$id]
            );
            return (int) ($r['max_attempts'] ?? 3);
        }
        return (int) (self::$memory[$id]['max_attempts'] ?? 3);
    }

    /** Re-queue a job for later (failed attempt, attempts remain) or on manual retry. */
    public static function release($id, $delaySeconds = 2)
    {
        if (self::store() === 'pdo') {
            $db = Database::getInstance();
            $db->query(
                'UPDATE `' . self::table() . '` SET `status` = \'pending\', `available_at` = DATE_ADD(NOW(), INTERVAL :d SECOND), `locked_at` = NULL WHERE `id` = :id',
                [':d' => max(0, (int)$delaySeconds), ':id' => (int)$id]
            );
            return true;
        }
        if (isset(self::$memory[$id])) {
            self::$memory[$id]['status'] = 'pending';
            self::$memory[$id]['locked_at'] = null;
            self::$memory[$id]['available_at'] = date('Y-m-d H:i:s', time() + max(0, (int)$delaySeconds));
        }
        return true;
    }

    protected static function finish($id, $status, $error, $result)
    {
        if (self::store() === 'pdo') {
            $db = Database::getInstance();
            $db->query(
                'UPDATE `' . self::table() . '` SET `status` = :s, `error` = :e, `result` = :r, `locked_at` = NULL WHERE `id` = :id',
                [':s' => $status, ':e' => $error, ':r' => ($result === null ? null : json_encode($result, JSON_UNESCAPED_UNICODE)), ':id' => (int)$id]
            );
            return true;
        }
        if (isset(self::$memory[$id])) {
            self::$memory[$id]['status'] = $status;
            self::$memory[$id]['error'] = $error;
            self::$memory[$id]['result'] = $result;
            self::$memory[$id]['locked_at'] = null;
        }
        return true;
    }

    public static function countPending($queue = 'default')
    {
        if (self::store() === 'pdo') {
            $r = Database::getInstance()->fetch(
                'SELECT COUNT(*) AS c FROM `' . self::table() . '` WHERE `queue` = :q AND `status` = \'pending\'',
                [':q' => (string)$queue]
            );
            return (int) ($r['c'] ?? 0);
        }
        $c = 0;
        foreach (self::$memory as $row) {
            if ($row['status'] === 'pending' && $row['queue'] === (string)$queue) $c++;
        }
        return $c;
    }

    /**
     * Process pending jobs synchronously (the eventual `craft queue:work` loop).
     * $handler is a callable string (e.g. "Jobs\\SyncFeed::run") or closure.
     * Returns [processed, failed].
     */
    public static function work($queue = 'default', $maxJobs = 10)
    {
        $processed = 0;
        $failed = 0;
        for ($i = 0; $i < (int)$maxJobs; $i++) {
            $job = self::claim($queue);
            if (!$job) {
                break;
            }
            try {
                if (is_callable($job['handler'])) {
                    $result = call_user_func($job['handler'], $job['payload'], $job);
                } else {
                    throw new RuntimeException('No callable handler: ' . (string)$job['handler']);
                }
                self::complete($job['id'], $result);
                $processed++;
            } catch (Throwable $e) {
                $attemptsUsed = (int)$job['attempts'];
                self::fail($job['id'], $e->getMessage(), $attemptsUsed);
                $failed++;
            }
        }
        return [$processed, $failed];
    }
}