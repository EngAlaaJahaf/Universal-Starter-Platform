<?php

class LiveBlogController extends Controller
{
    public function index()
    {
        $db = Database::getInstance();
        $stmt = $db->query("
            SELECT lb.*, a.slug as article_slug,
                   (SELECT COUNT(*) FROM live_blog_entries lbe WHERE lbe.live_blog_id = lb.id) as entries_count
            FROM live_blogs lb
            LEFT JOIN articles a ON lb.article_id = a.id
            ORDER BY (lb.status = 'active') DESC, lb.created_at DESC
        ");
        $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('live-blog/index', [
            'blogs' => $blogs
        ]);
    }

    public function show($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM live_blogs WHERE id = ?");
        $stmt->execute([(int) $id]);
        $blog = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$blog) {
            header("HTTP/1.0 404 Not Found");
            $this->view('errors/404');
            return;
        }

        // Fetch entries with replies count
        $stmt = $db->prepare("
            SELECT lbe.*, u.username as author_name, u.avatar as author_avatar,
                   (SELECT COUNT(*) FROM live_blog_chat c WHERE c.entry_id = lbe.id AND c.is_approved = 1) as replies_count
            FROM live_blog_entries lbe
            LEFT JOIN users u ON lbe.author_id = u.id
            WHERE lbe.live_blog_id = ?
            ORDER BY lbe.is_pinned DESC, lbe.created_at DESC
        ");
        $stmt->execute([(int) $id]);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch initial recent chat messages with reply snippets
        $chatStmt = $db->prepare("
            SELECT c.*, u.username as auth_username, u.avatar as auth_avatar,
                   SUBSTRING(lbe.content_ar, 1, 75) as reply_snippet
            FROM live_blog_chat c
            LEFT JOIN users u ON c.user_id = u.id
            LEFT JOIN live_blog_entries lbe ON c.entry_id = lbe.id
            WHERE c.live_blog_id = ? AND c.is_approved = 1
            ORDER BY c.id ASC
            LIMIT 60
        ");
        $chatStmt->execute([(int) $id]);
        $initialChats = $chatStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch reaction counts grouped by type & entry_id
        $rxStmt = $db->prepare("
            SELECT entry_id, reaction_type, COUNT(*) as total
            FROM live_blog_reactions
            WHERE live_blog_id = ?
            GROUP BY entry_id, reaction_type
        ");
        $rxStmt->execute([(int) $id]);
        $reactionsRaw = $rxStmt->fetchAll(PDO::FETCH_ASSOC);

        $reactions = [];
        foreach ($reactionsRaw as $r) {
            $key = ($r['entry_id'] !== null && $r['entry_id'] !== '') ? (int)$r['entry_id'] : 'global';
            $reactions[$key][$r['reaction_type']] = (int) $r['total'];
        }

        $this->view('live-blog/show', [
            'blog'         => $blog,
            'entries'      => $entries,
            'initialChats' => $initialChats,
            'reactions'    => $reactions
        ]);
    }

    // Send Live Chat message (with optional reply to entry)
    public function sendChat($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        CSRF::validate($_POST['_csrf'] ?? '');

        // Rate limit: 1 message every 2 seconds per IP
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $now = time();
        $lastChat = $_SESSION['last_live_chat_time'] ?? 0;

        if (($now - $lastChat) < 2) {
            echo json_encode([
                'success' => false,
                'message' => 'يرجى الانتظار لحظة قبل إرسال رسالة أخرى منعاً للتكرار.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $message = trim($_POST['message'] ?? '');
        $senderName = trim($_POST['sender_name'] ?? '');
        $entryId = isset($_POST['entry_id']) && $_POST['entry_id'] !== '' ? (int)$_POST['entry_id'] : null;

        $user = Auth::user();
        if ($user) {
            $senderName = $user['username'] ?? $user['name'] ?? 'مستخدم مسجل';
            $userId = $user['id'];
            $avatar = $user['avatar'] ?? null;
        } else {
            $userId = null;
            $avatar = null;
            if (empty($senderName)) {
                $senderName = 'متابع ' . rand(100, 999);
            } else {
                $senderName = mb_substr($senderName, 0, 40);
            }
        }

        if (empty($message)) {
            echo json_encode(['success' => false, 'message' => 'الرسالة لا يمكن أن تكون فارغة.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $message = mb_substr($message, 0, 400);

        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO live_blog_chat (live_blog_id, entry_id, user_id, sender_name, sender_avatar, message, ip_address, is_approved)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([(int) $id, $entryId, $userId, $senderName, $avatar, $message, $ip]);
        $newId = $db->lastInsertId();

        $_SESSION['last_live_chat_time'] = $now;

        $replySnippet = null;
        if ($entryId) {
            $eStmt = $db->prepare("SELECT SUBSTRING(content_ar, 1, 75) as snippet FROM live_blog_entries WHERE id = ?");
            $eStmt->execute([$entryId]);
            $replySnippet = $eStmt->fetchColumn() ?: null;
        }

        echo json_encode([
            'success' => true,
            'chat'    => [
                'id'            => (int) $newId,
                'entry_id'      => $entryId,
                'reply_snippet' => $replySnippet ? htmlspecialchars($replySnippet) : null,
                'sender_name'   => htmlspecialchars($senderName),
                'avatar'        => $avatar ? (str_starts_with($avatar, 'http') ? $avatar : app_url($avatar)) : null,
                'message'       => htmlspecialchars($message),
                'is_user'       => $userId ? 1 : 0,
                'created_at'    => fmt_time_site('now', 'H:i:s')
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Submit Live Reaction
    public function react($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        CSRF::validate($_POST['_csrf'] ?? '');

        $reactionType = $_POST['reaction_type'] ?? 'fire';
        $allowed = ['fire', 'clap', 'mindblown', 'lightbulb', 'heart'];
        if (!in_array($reactionType, $allowed)) {
            $reactionType = 'fire';
        }

        $entryId = isset($_POST['entry_id']) && $_POST['entry_id'] !== '' ? (int)$_POST['entry_id'] : null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO live_blog_reactions (live_blog_id, entry_id, reaction_type, ip_address)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([(int) $id, $entryId, $reactionType, $ip]);

        // Get updated count for this specific reaction
        if ($entryId) {
            $countStmt = $db->prepare("SELECT COUNT(*) FROM live_blog_reactions WHERE live_blog_id = ? AND entry_id = ? AND reaction_type = ?");
            $countStmt->execute([(int) $id, $entryId, $reactionType]);
        } else {
            $countStmt = $db->prepare("SELECT COUNT(*) FROM live_blog_reactions WHERE live_blog_id = ? AND entry_id IS NULL AND reaction_type = ?");
            $countStmt->execute([(int) $id, $reactionType]);
        }
        $total = (int) $countStmt->fetchColumn();

        echo json_encode([
            'success'       => true,
            'reaction_type' => $reactionType,
            'entry_id'      => $entryId,
            'count'         => $total
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Polling endpoint for real-time live updates, deletions, chat & reactions
    public function poll($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        $chatSince = isset($_GET['chat_since']) ? (int) $_GET['chat_since'] : 0;

        $db = Database::getInstance();
        
        // 1. Get blog status
        $bStmt = $db->prepare("SELECT id, status FROM live_blogs WHERE id = ?");
        $bStmt->execute([(int) $id]);
        $blog = $bStmt->fetch(PDO::FETCH_ASSOC);

        if (!$blog) {
            echo json_encode(['success' => false, 'message' => 'Not found']);
            exit;
        }

        // 2. Fetch all current entries with replies count
        $entriesStmt = $db->prepare("
            SELECT lbe.*, u.username as author_name, u.avatar as author_avatar,
                   (SELECT COUNT(*) FROM live_blog_chat c WHERE c.entry_id = lbe.id AND c.is_approved = 1) as replies_count
            FROM live_blog_entries lbe
            LEFT JOIN users u ON lbe.author_id = u.id
            WHERE lbe.live_blog_id = ?
            ORDER BY lbe.created_at DESC
        ");
        $entriesStmt->execute([(int) $id]);
        $entries = $entriesStmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Fetch new chat messages since chatSince with reply snippet
        $chatStmt = $db->prepare("
            SELECT c.*, u.username as auth_username, u.avatar as auth_avatar,
                   SUBSTRING(lbe.content_ar, 1, 75) as reply_snippet
            FROM live_blog_chat c
            LEFT JOIN users u ON c.user_id = u.id
            LEFT JOIN live_blog_entries lbe ON c.entry_id = lbe.id
            WHERE c.live_blog_id = ? AND c.is_approved = 1 AND c.id > ?
            ORDER BY c.id ASC
            LIMIT 40
        ");
        $chatStmt->execute([(int) $id, $chatSince]);
        $chats = $chatStmt->fetchAll(PDO::FETCH_ASSOC);

        // 4. Fetch reaction totals
        $rxStmt = $db->prepare("
            SELECT entry_id, reaction_type, COUNT(*) as total
            FROM live_blog_reactions
            WHERE live_blog_id = ?
            GROUP BY entry_id, reaction_type
        ");
        $rxStmt->execute([(int) $id]);
        $reactionsRaw = $rxStmt->fetchAll(PDO::FETCH_ASSOC);

        $reactions = [];
        foreach ($reactionsRaw as $r) {
            $key = ($r['entry_id'] !== null && $r['entry_id'] !== '') ? (int)$r['entry_id'] : 'global';
            $reactions[$key][$r['reaction_type']] = (int) $r['total'];
        }

        echo json_encode([
            'success'     => true,
            'status'      => $blog['status'],
            'total_count' => count($entries),
            'entries'     => $entries,
            'chats'       => $chats,
            'reactions'   => $reactions
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function sendChatMessage($id)
    {
        return $this->sendChat($id);
    }

    public function fetchChat($id)
    {
        return $this->poll($id);
    }

    public function fetchUpdates($id)
    {
        return $this->poll($id);
    }
}
