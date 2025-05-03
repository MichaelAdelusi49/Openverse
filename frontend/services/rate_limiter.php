<?php
require_once '../config/config.php';

class RateLimiter {
    private $user_id;
    private $redis;

    public function __construct($user_id = null) {
        $this->user_id = $user_id ?? session_id();
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }

    public function checkLimit() {
        $key = "rate_limit:{$this->user_id}";
        $current = $this->redis->get($key);

        if ($current >= API_RATE_LIMIT) {
            header('Retry-After: 60');
            throw new Exception('API rate limit exceeded', 429);
        }

        $this->redis->multi()
            ->incr($key)
            ->expire($key, 60)
            ->exec();
    }

    public function getRemaining() {
        $key = "rate_limit:{$this->user_id}";
        return API_RATE_LIMIT - ($this->redis->get($key) ?: 0);
    }
}

// Usage in other files:
// $limiter = new RateLimiter($_SESSION['user_id'] ?? null);
// $limiter->checkLimit();
?>