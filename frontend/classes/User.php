<?php
class User {
    public static function getUsername($userId, Database $db) {
        $stmt = $db->query("SELECT username FROM users WHERE user_id = ?", [$userId], "i");
        $stmt->bind_result($username);
        $stmt->fetch();
        $stmt->close();
        return $username ?? '';
    }

    public static function getSearchHistory($userId, Database $db) {
        $stmt = $db->query(
            "SELECT history_id, search_query FROM search_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 10",
            [$userId], "i"
        );
        $result = $stmt->get_result();
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        $stmt->close();
        return $history;
    }
}
?>