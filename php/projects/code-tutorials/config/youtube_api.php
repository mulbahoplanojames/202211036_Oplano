<?php
/**
 * YouTube API Configuration
 * Curated Programming Tutorials Web Platform
 */

class YouTubeAPIConfig {
    private $api_key;
    private $base_url = 'https://www.googleapis.com/youtube/v3';
    
    public function __construct() {

        $this->api_key = 'AIzaSyAT2RQT186YwqUnyG5IgIOmD4Dfhnjoa_A';
    }
    
    public function getAPIKey() {   
        return $this->api_key;
    }
    
    public function getBaseURL() {
        return $this->base_url;
    }
    
    public function isValidAPIKey() {
        return !empty($this->api_key) && 
               $this->api_key !== 'YOUR_YOUTUBE_API_KEY_HERE' &&
               strlen($this->api_key) > 30; 
    }
}
?>
