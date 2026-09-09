<?php

class Poll extends Model
{
    /**
     * Get the active featured poll for display on the homepage
     */
    public function getActiveFeatured($userId = null, $ipHash = null)
    {
        $poll = $this->db->fetch("
            SELECT * FROM polls 
            WHERE status = 'active' AND is_featured = 1 
            ORDER BY id DESC LIMIT 1
        ");

        if (!$poll) {
            // Fallback to any active poll
            $poll = $this->db->fetch("
                SELECT * FROM polls 
                WHERE status = 'active' 
                ORDER BY id DESC LIMIT 1
            ");
        }

        if (!$poll) {
            return null;
        }

        $poll['options'] = $this->getOptionsWithStats($poll['id']);
        $poll['total_votes'] = array_sum(array_column($poll['options'], 'votes_count'));
        $poll['has_voted'] = $this->hasUserVoted($poll['id'], $userId, $ipHash);
        $poll['user_voted_option_id'] = $this->getUserVotedOptionId($poll['id'], $userId, $ipHash);

        return $poll;
    }

    /**
     * Get poll by ID with options
     */
    public function getById($id)
    {
        $poll = $this->db->fetch("SELECT * FROM polls WHERE id = :id", [':id' => (int) $id]);
        if (!$poll) return null;

        $poll['options'] = $this->getOptionsWithStats($poll['id']);
        $poll['total_votes'] = array_sum(array_column($poll['options'], 'votes_count'));

        return $poll;
    }

    /**
     * Get all polls for admin dashboard
     */
    public function getAll($limit = 50, $offset = 0)
    {
        $lim = (int) $limit;
        $off = (int) $offset;
        $polls = $this->db->fetchAll("
            SELECT p.*, 
                   COUNT(DISTINCT o.id) as options_count,
                   COALESCE(SUM(o.votes_count), 0) as total_votes
            FROM polls p
            LEFT JOIN poll_options o ON p.id = o.poll_id
            GROUP BY p.id
            ORDER BY p.is_featured DESC, p.created_at DESC
            LIMIT {$lim} OFFSET {$off}
        ");

        foreach ($polls as &$poll) {
            $poll['options'] = $this->getOptionsWithStats($poll['id']);
        }

        return $polls;
    }

    /**
     * Get options with computed percentage
     */
    public function getOptionsWithStats($pollId)
    {
        $options = $this->db->fetchAll("
            SELECT * FROM poll_options 
            WHERE poll_id = :pid 
            ORDER BY sort_order ASC, id ASC
        ", [':pid' => (int) $pollId]);

        $totalVotes = array_sum(array_column($options, 'votes_count'));

        foreach ($options as &$opt) {
            $opt['percent'] = $totalVotes > 0 ? (int) round(($opt['votes_count'] / $totalVotes) * 100) : 0;
        }

        return $options;
    }

    /**
     * Check if user/IP has voted
     */
    public function hasUserVoted($pollId, $userId = null, $ipHash = null)
    {
        if ($userId) {
            $vote = $this->db->fetch("SELECT id FROM poll_votes WHERE poll_id = :pid AND user_id = :uid LIMIT 1", [
                ':pid' => (int) $pollId,
                ':uid' => (int) $userId
            ]);
            if ($vote) return true;
        }

        if ($ipHash) {
            $vote = $this->db->fetch("SELECT id FROM poll_votes WHERE poll_id = :pid AND ip_hash = :ip LIMIT 1", [
                ':pid' => (int) $pollId,
                ':ip'  => (string) $ipHash
            ]);
            if ($vote) return true;
        }

        return false;
    }

    /**
     * Get option ID that user voted for
     */
    public function getUserVotedOptionId($pollId, $userId = null, $ipHash = null)
    {
        if ($userId) {
            $vote = $this->db->fetch("SELECT option_id FROM poll_votes WHERE poll_id = :pid AND user_id = :uid LIMIT 1", [
                ':pid' => (int) $pollId,
                ':uid' => (int) $userId
            ]);
            if ($vote) return (int) $vote['option_id'];
        }

        if ($ipHash) {
            $vote = $this->db->fetch("SELECT option_id FROM poll_votes WHERE poll_id = :pid AND ip_hash = :ip LIMIT 1", [
                ':pid' => (int) $pollId,
                ':ip'  => (string) $ipHash
            ]);
            if ($vote) return (int) $vote['option_id'];
        }

        return null;
    }

    /**
     * Submit a vote
     */
    public function vote($pollId, $optionId, $userId = null, $ipHash = '', $userAgent = '')
    {
        $poll = $this->getById($pollId);
        if (!$poll || $poll['status'] !== 'active') {
            return ['success' => false, 'error' => 'الاستطلاع غير متاح أو متوقف حالياً.'];
        }

        if ($this->hasUserVoted($pollId, $userId, $ipHash)) {
            return ['success' => false, 'error' => 'لقد قمت بالتصويت في هذا الاستطلاع مسبقاً.'];
        }

        // Verify option belongs to this poll
        $opt = $this->db->fetch("SELECT id FROM poll_options WHERE id = :oid AND poll_id = :pid", [
            ':oid' => (int) $optionId,
            ':pid' => (int) $pollId
        ]);
        if (!$opt) {
            return ['success' => false, 'error' => 'الخيار المحدد غير صالح.'];
        }

        // Record vote
        $this->db->query("
            INSERT INTO poll_votes (`poll_id`, `option_id`, `user_id`, `ip_hash`, `user_agent`) 
            VALUES (:pid, :oid, :uid, :ip, :ua)
        ", [
            ':pid' => (int) $pollId,
            ':oid' => (int) $optionId,
            ':uid' => $userId ? (int) $userId : null,
            ':ip'  => (string) $ipHash,
            ':ua'  => substr((string) $userAgent, 0, 255)
        ]);

        // Increment count
        $this->db->query("UPDATE poll_options SET votes_count = votes_count + 1 WHERE id = :oid", [
            ':oid' => (int) $optionId
        ]);

        $updatedOptions = $this->getOptionsWithStats($pollId);
        $totalVotes = array_sum(array_column($updatedOptions, 'votes_count'));

        return [
            'success'     => true,
            'poll_id'     => (int) $pollId,
            'voted_id'    => (int) $optionId,
            'total_votes' => $totalVotes,
            'options'     => $updatedOptions
        ];
    }

    /**
     * Create Poll
     */
    public function create($data, array $options = [])
    {
        $isFeatured = !empty($data['is_featured']) ? 1 : 0;
        if ($isFeatured) {
            $this->db->query("UPDATE polls SET is_featured = 0");
        }

        $this->db->query("
            INSERT INTO polls (`question`, `description`, `status`, `is_featured`, `expires_at`) 
            VALUES (:q, :d, :st, :feat, :exp)
        ", [
            ':q'    => trim((string) $data['question']),
            ':d'    => trim((string) ($data['description'] ?? '')),
            ':st'   => $data['status'] ?? 'active',
            ':feat' => $isFeatured,
            ':exp'  => !empty($data['expires_at']) ? $data['expires_at'] : null
        ]);

        $pollId = (int) $this->db->lastInsertId();

        $this->saveOptions($pollId, $options);

        return $pollId;
    }

    /**
     * Update Poll
     */
    public function update($id, $data, array $options = [])
    {
        $isFeatured = !empty($data['is_featured']) ? 1 : 0;
        if ($isFeatured) {
            $this->db->query("UPDATE polls SET is_featured = 0 WHERE id != :id", [':id' => (int) $id]);
        }

        $this->db->query("
            UPDATE polls SET 
                `question`    = :q,
                `description` = :d,
                `status`      = :st,
                `is_featured` = :feat,
                `expires_at`  = :exp
            WHERE `id` = :id
        ", [
            ':id'   => (int) $id,
            ':q'    => trim((string) $data['question']),
            ':d'    => trim((string) ($data['description'] ?? '')),
            ':st'   => $data['status'] ?? 'active',
            ':feat' => $isFeatured,
            ':exp'  => !empty($data['expires_at']) ? $data['expires_at'] : null
        ]);

        if (!empty($options)) {
            $this->saveOptions($id, $options);
        }

        return true;
    }

    /**
     * Delete Poll
     */
    public function delete($id)
    {
        return $this->db->query("DELETE FROM polls WHERE id = :id", [':id' => (int) $id]);
    }

    /**
     * Reset Votes
     */
    public function resetVotes($id)
    {
        $this->db->query("DELETE FROM poll_votes WHERE poll_id = :id", [':id' => (int) $id]);
        $this->db->query("UPDATE poll_options SET votes_count = 0 WHERE poll_id = :id", [':id' => (int) $id]);
        return true;
    }

    /**
     * Set as featured on homepage
     */
    public function setFeatured($id)
    {
        $this->db->query("UPDATE polls SET is_featured = 0");
        $this->db->query("UPDATE polls SET is_featured = 1, status = 'active' WHERE id = :id", [':id' => (int) $id]);
        return true;
    }

    /**
     * Save/Sync options for a poll
     */
    private function saveOptions($pollId, array $options)
    {
        // Get existing options to preserve votes for existing titles
        $existing = $this->db->fetchAll("SELECT id, title, votes_count FROM poll_options WHERE poll_id = :pid", [':pid' => (int) $pollId]);
        $existingMap = [];
        foreach ($existing as $ex) {
            $existingMap[(int)$ex['id']] = $ex;
        }

        $keepIds = [];
        $order = 1;

        foreach ($options as $opt) {
            $title = trim((string)($opt['title'] ?? ''));
            if (empty($title)) continue;

            $icon = trim((string)($opt['icon'] ?? 'sparkle'));
            if (empty($icon)) $icon = 'sparkle';

            $optId = !empty($opt['id']) ? (int)$opt['id'] : 0;

            if ($optId > 0 && isset($existingMap[$optId])) {
                $this->db->query("
                    UPDATE poll_options SET `title` = :t, `icon` = :ic, `sort_order` = :so WHERE `id` = :id AND `poll_id` = :pid
                ", [
                    ':id'  => $optId,
                    ':pid' => (int) $pollId,
                    ':t'   => $title,
                    ':ic'  => $icon,
                    ':so'  => $order
                ]);
                $keepIds[] = $optId;
            } else {
                $this->db->query("
                    INSERT INTO poll_options (`poll_id`, `title`, `icon`, `votes_count`, `sort_order`) 
                    VALUES (:pid, :t, :ic, 0, :so)
                ", [
                    ':pid' => (int) $pollId,
                    ':t'   => $title,
                    ':ic'  => $icon,
                    ':so'  => $order
                ]);
                $keepIds[] = (int) $this->db->lastInsertId();
            }
            $order++;
        }

        // Delete removed options
        if (!empty($keepIds)) {
            $placeholders = implode(',', array_fill(0, count($keepIds), '?'));
            $params = array_merge([(int) $pollId], $keepIds);
            $this->db->query("DELETE FROM poll_options WHERE poll_id = ? AND id NOT IN ($placeholders)", $params);
        }
    }
}
