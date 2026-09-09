<?php

class PollController extends Controller
{
    /**
     * Submit an AJAX vote
     */
    public function vote()
    {
        header('Content-Type: application/json; charset=utf-8');

        $pollId = (int) ($_POST['poll_id'] ?? 0);
        $optionId = (int) ($_POST['option_id'] ?? 0);

        if ($pollId <= 0 || $optionId <= 0) {
            echo json_encode(['success' => false, 'error' => 'بيانات التصويت غير مكتملة.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $userId = Auth::user()['id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ipHash = hash('sha256', $ip . $ua);

        $pollModel = new Poll();
        $result = $pollModel->vote($pollId, $optionId, $userId, $ipHash, $ua);

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get active poll state via JSON
     */
    public function active()
    {
        header('Content-Type: application/json; charset=utf-8');

        $userId = Auth::user()['id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ipHash = hash('sha256', $ip . $ua);

        $pollModel = new Poll();
        $poll = $pollModel->getActiveFeatured($userId, $ipHash);

        echo json_encode(['success' => true, 'poll' => $poll], JSON_UNESCAPED_UNICODE);
    }
}
