<?php
class OpenverseAPIClient {
    private static $token = null;

    public static function getToken() {
        if (self::$token === null) {
            $data = [
                'client_id' => OPENVERSE_CLIENT_ID,
                'client_secret' => OPENVERSE_CLIENT_SECRET,
                'grant_type' => 'client_credentials'
            ];
            $options = [
                'http' => [
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data)
                ]
            ];
            $context = stream_context_create($options);
            $response = file_get_contents('https://api.openverse.org/v1/auth_tokens/token/', false, $context);
            if ($response === false) {
                throw new Exception('Failed to get Openverse access token');
            }
            $token_data = json_decode($response, true);
            self::$token = $token_data['access_token'] ?? '';
        }
        return self::$token;
    }

    public static function getTrendingContent($type) {
        try {
            $token = self::getToken();
            $url = "https://api.openverse.org/v1/$type/?q=nature&page_size=" . MAX_TRENDING_ITEMS;
            $options = [
                'http' => [
                    'header' => "Authorization: Bearer $token\r\n"
                ]
            ];
            $context = stream_context_create($options);
            $response = file_get_contents($url, false, $context);
            if ($response === false) {
                return [];
            }
            $data = json_decode($response, true);
            return $data['results'] ?? [];
        } catch (Exception $e) {
            error_log("Error fetching trending content: " . $e->getMessage());
            return [];
        }
    }
}
?>