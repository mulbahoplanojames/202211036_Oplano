<?php
/**
 * YouTube API Service
 * Curated Programming Tutorials Web Platform
 */

require_once __DIR__ . '/../config/youtube_api.php';

class YouTubeAPIService {
    private $config;
    private $db;
    
    public function __construct($database) {
        $this->config = new YouTubeAPIConfig();
        $this->db = $database;
    }
    
    /**
     * Search for YouTube videos based on course criteria
     * Returns top 6 videos with >1M views and educational relevance
     */
    public function searchVideosForCourse($course_title, $programming_language, $difficulty_level = 'beginner') {
        if (!$this->config->isValidAPIKey()) {
            throw new Exception("YouTube API key not configured. Please set up your API key in config/youtube_api.php");
        }
        
        // Build search query based on course information
        $search_query = $this->buildSearchQuery($course_title, $programming_language, $difficulty_level);
        
        // First, search for videos
        $search_url = $this->config->getBaseURL() . '/search?' . http_build_query([
            'part' => 'snippet',
            'q' => $search_query,
            'type' => 'video',
            'maxResults' => 20, // Get more to filter by view count
            'order' => 'relevance',
            'videoDuration' => 'medium', // Medium duration (4-20 minutes) for educational content
            'key' => $this->config->getAPIKey()
        ]);
        
        $search_response = $this->makeAPIRequest($search_url);
        
        if (!$search_response || !isset($search_response['items'])) {
            return [];
        }
        
        // Get video IDs for detailed information
        $video_ids = [];
        foreach ($search_response['items'] as $item) {
            $video_ids[] = $item['id']['videoId'];
        }
        
        // Get detailed video information including view counts
        $details_url = $this->config->getBaseURL() . '/videos?' . http_build_query([
            'part' => 'snippet,statistics,contentDetails',
            'id' => implode(',', $video_ids),
            'key' => $this->config->getAPIKey()
        ]);
        
        $details_response = $this->makeAPIRequest($details_url);
        
        if (!$details_response || !isset($details_response['items'])) {
            return [];
        }
        
        // Filter videos based on criteria
        $filtered_videos = [];
        foreach ($details_response['items'] as $video) {
            $views_count = intval($video['statistics']['viewCount'] ?? 0);
            
            // Apply filtering criteria
            if ($views_count >= 1000000 && // More than 1M views
                $this->isEducationalContent($video['snippet']['title'], $video['snippet']['description'])) {
                
                $filtered_videos[] = [
                    'youtube_video_id' => $video['id'],
                    'title' => $video['snippet']['title'],
                    'description' => $video['snippet']['description'],
                    'youtube_url' => 'https://www.youtube.com/watch?v=' . $video['id'],
                    'thumbnail_url' => $video['snippet']['thumbnails']['high']['url'] ?? $video['snippet']['thumbnails']['default']['url'],
                    'views_count' => $views_count,
                    'likes_count' => intval($video['statistics']['likeCount'] ?? 0),
                    'comments_count' => intval($video['statistics']['commentCount'] ?? 0),
                    'duration' => $this->formatDuration($video['contentDetails']['duration'] ?? ''),
                    'channel_name' => $video['snippet']['channelTitle']
                ];
            }
        }
        
        // Sort by view count (descending) and return top 6
        usort($filtered_videos, function($a, $b) {
            return $b['views_count'] - $a['views_count'];
        });
        
        return array_slice($filtered_videos, 0, 6);
    }
    
    /**
     * Build search query based on course information
     */
    private function buildSearchQuery($course_title, $programming_language, $difficulty_level) {
        $query_parts = [];
        
        // Add programming language
        $query_parts[] = $programming_language;
        
        // Add difficulty-specific terms
        switch ($difficulty_level) {
            case 'beginner':
                $query_parts[] = 'tutorial for beginners';
                break;
            case 'intermediate':
                $query_parts[] = 'intermediate tutorial';
                break;
            case 'advanced':
                $query_parts[] = 'advanced tutorial';
                break;
        }
        
        // Add course-specific keywords
        if (strpos(strtolower($course_title), 'python') !== false) {
            $query_parts[] = 'python programming';
        } elseif (strpos(strtolower($course_title), 'java') !== false) {
            $query_parts[] = 'java programming';
        } elseif (strpos(strtolower($course_title), 'javascript') !== false) {
            $query_parts[] = 'javascript tutorial';
        } elseif (strpos(strtolower($course_title), 'php') !== false) {
            $query_parts[] = 'php tutorial';
        } elseif (strpos(strtolower($course_title), 'c++') !== false) {
            $query_parts[] = 'c++ programming';
        }
        
        // Add educational keywords
        $query_parts[] = 'full course';
        $query_parts[] = 'programming';
        
        return implode(' ', $query_parts);
    }
    
    /**
     * Check if content appears to be educational
     */
    private function isEducationalContent($title, $description) {
        $educational_keywords = [
            'tutorial', 'course', 'learn', 'programming', 'code', 'development',
            'beginner', 'introduction', 'basics', 'fundamentals', 'complete',
            'full course', 'tutorial for beginners', 'how to', 'guide', 'lesson'
        ];
        
        $title_lower = strtolower($title);
        $description_lower = strtolower($description);
        
        foreach ($educational_keywords as $keyword) {
            if (strpos($title_lower, $keyword) !== false || 
                strpos($description_lower, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Format ISO 8601 duration to readable format
     */
    private function formatDuration($duration) {
        if (empty($duration)) return 'N/A';
        
        // Parse ISO 8601 duration (PT4M13S)
        $interval = new DateInterval($duration);
        $hours = $interval->h;
        $minutes = $interval->i;
        $seconds = $interval->s;
        
        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        } else {
            return sprintf('%02d:%02d', $minutes, $seconds);
        }
    }
    
    /**
     * Make API request to YouTube
     */
    private function makeAPIRequest($url) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'method' => 'GET'
            ]
        ]);
        
        $response = file_get_contents($url, false, $context);
        
        if ($response === false) {
            return null;
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        
        return $data;
    }
    
    /**
     * Save fetched videos to database
     */
    public function saveVideosToDatabase($videos, $course_id) {
        $saved_count = 0;
        
        foreach ($videos as $video) {
            // Check if video already exists
            $check_query = "SELECT id FROM videos WHERE youtube_video_id = :video_id";
            $check_stmt = $this->db->prepare($check_query);
            $check_stmt->bindParam(':video_id', $video['youtube_video_id']);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() === 0) {
                // Insert new video
                $insert_query = "INSERT INTO videos (
                    course_id, title, description, youtube_video_id, youtube_url, 
                    thumbnail_url, views_count, likes_count, comments_count, 
                    duration, channel_name, is_active
                ) VALUES (
                    :course_id, :title, :description, :youtube_video_id, :youtube_url,
                    :thumbnail_url, :views_count, :likes_count, :comments_count,
                    :duration, :channel_name, 1
                )";
                
                $stmt = $this->db->prepare($insert_query);
                
                $stmt->bindParam(':course_id', $course_id);
                $stmt->bindParam(':title', $video['title']);
                $stmt->bindParam(':description', $video['description']);
                $stmt->bindParam(':youtube_video_id', $video['youtube_video_id']);
                $stmt->bindParam(':youtube_url', $video['youtube_url']);
                $stmt->bindParam(':thumbnail_url', $video['thumbnail_url']);
                $stmt->bindParam(':views_count', $video['views_count']);
                $stmt->bindParam(':likes_count', $video['likes_count']);
                $stmt->bindParam(':comments_count', $video['comments_count']);
                $stmt->bindParam(':duration', $video['duration']);
                $stmt->bindParam(':channel_name', $video['channel_name']);
                
                if ($stmt->execute()) {
                    $saved_count++;
                }
            }
        }
        
        return $saved_count;
    }
}
?>
