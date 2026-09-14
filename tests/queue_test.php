<?php
/**
 * queue_test — Queue scaffold (SH-09) using the in-memory array store
 * (no MySQL required). Verifies push/claim/complete/release/attempts/exhaustion.
 */

function queue_test_ok_handler($payload)
{
    return 'processed:' . ($payload['n'] ?? '?');
}

function queue_test_bad_handler($payload)
{
    throw new RuntimeException('boom');
}

TestRunner::suite('Queue');

Queue::setStore('array');

// ── push → claim → complete ────────────────────────────────────────────
$id = Queue::push('queue_test_ok_handler', ['n' => 7], 'default');
TestRunner::isTrue($id > 0, 'push() returns a positive id');
TestRunner::is(1, Queue::countPending(), 'countPending() = 1 after push');

$job = Queue::claim();
TestRunner::isTrue($job !== null, 'claim() returns a waiting job');
TestRunner::is('queue_test_ok_handler', $job['handler'], 'claim() preserves the handler');
TestRunner::is(7, $job['payload']['n'] ?? null, 'claim() decodes payload');
TestRunner::is('running', $job['status'], 'claimed job is running');
TestRunner::is(1, $job['attempts'], 'first claim bumps attempts to 1');
TestRunner::is(0, Queue::countPending(), 'in-flight job no longer pending');

TestRunner::is(null, Queue::claim(), 'no double-claim while a job is running');

Queue::complete($id, ['ok' => true]);
TestRunner::is(0, Queue::countPending(), 'completed job leaves the pending set');

// ── work() executes a callable handler to completion ──────────────────
Queue::push('queue_test_ok_handler', ['n' => 9], 'default');
list($processed, $failed) = Queue::work('default', 5);
TestRunner::is(1, $processed, 'work() processed the callable job');
TestRunner::is(0, $failed, 'work() reported no failures');
TestRunner::is(0, Queue::countPending(), 'queue drained after work()');

// ── retry / backoff lifecycle ──────────────────────────────────────────
$id2 = Queue::push('queue_test_bad_handler', [], 'default');
$job2 = Queue::claim();                     // attempts = 1
Queue::fail($job2['id'], 'transient', 1);   // attempts(1) < 3 → retryable
TestRunner::is(1, Queue::countPending(), 'retryable failure returns to pending');
TestRunner::is(null, Queue::claim(), 'backoff: not claimable before available_at');

Queue::release($job2['id'], 0);             // make available now
$job2b = Queue::claim();
TestRunner::isTrue($job2b !== null, 'released job is claimable again');
TestRunner::is(2, $job2b['attempts'], 'reclaim bumps attempts to 2');
Queue::complete($job2b['id']);
TestRunner::is(0, Queue::countPending(), 'job completed after retry');

// ── attempts exhaustion → terminal failed ───────────────────────────────
$id3 = Queue::push('queue_test_bad_handler', [], 'default');
for ($i = 1; $i <= 3; $i++) {
    $j = Queue::claim();
    TestRunner::isTrue($j !== null, "attempt {$i}: claim returns the doomed job");
    TestRunner::is($i, $j['attempts'], "attempt {$i}: attempts counter correct");
    Queue::fail($j['id'], 'boom', $j['attempts']);
    if ($i < 3) {
        Queue::release($j['id'], 0); // test overrides the backoff delay
    }
}
TestRunner::is(0, Queue::countPending(), 'terminal after 3 failed attempts');
TestRunner::is(null, Queue::claim(), 'nothing left to claim');

// ── queue isolation ─────────────────────────────────────────────────────
Queue::push('queue_test_ok_handler', [], 'queueA');
Queue::push('queue_test_ok_handler', [], 'queueB');
TestRunner::is(1, Queue::countPending('queueA'), 'queueA pending count isolated');
TestRunner::is(1, Queue::countPending('queueB'), 'queueB pending count isolated');
$jA = Queue::claim('queueA');
TestRunner::isTrue($jA !== null, 'claim(queueA) picks the right queue');
TestRunner::is(1, Queue::countPending('queueB'), 'queueB untouched by queueA claim');

echo "\n";
return TestRunner::summary();