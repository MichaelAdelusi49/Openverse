<?php

class OpenverseAPIClient {
    private $clientId;
    private $clientSecret;
    private $accessToken;
    private $baseUrl = 'https://api.openverse.org/v1/';
    
    // Constructor to initialize the client with API credentials
    public function __construct($clientId, $clientSecret) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    // Private method to fetch an access token
    private function fetchAccessToken() {
        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials'
        ];

        // Set the options for the request to fetch the token
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        
        $context = stream_context_create($options);
        $response = file_get_contents($this->baseUrl . 'auth_tokens/token/', false, $context);

        if ($response === false) {
            throw new Exception('Failed to get Openverse access token');
        }

        $tokenData = json_decode($response, true);
        $this->accessToken = $tokenData['access_token'] ?? '';
    }

    // Public method to get the access token, caching it to avoid redundant requests
    public function getToken() {
        if ($this->accessToken === null) {
            $this->fetchAccessToken(); // Fetch token if not already available
        }
        return $this->accessToken;
    }

    // Public method to fetch trending content of a specified type (e.g., 'images', 'audio')
    public function getTrendingContent($type) {
        try {
            $token = $this->getToken(); // Get the token

            // Construct URL for fetching trending content
            $url = $this->baseUrl . "$type/?q=nature&page_size=" . MAX_TRENDING_ITEMS;

            // Set options for the request with Authorization header
            $options = [
                'http' => [
                    'header' => "Authorization: Bearer $token\r\n"
                ]
            ];

            $context = stream_context_create($options);
            $response = file_get_contents($url, false, $context);

            if ($response === false) {
                return []; // Return an empty array if the request failed
            }

            $data = json_decode($response, true);
            return $data['results'] ?? []; // Return the results if available
        } catch (Exception $e) {
            error_log("Error fetching trending content: " . $e->getMessage());
            return []; // Return an empty array on error
        }
    }
}

?>
