# YouTube API Setup Guide

This document explains how to set up the YouTube API for automatic video fetching functionality.

## Overview

The YouTube API integration allows you to automatically fetch relevant educational videos for your courses based on specific criteria:
- More than 1,000,000 views
- Educational content (tutorials, courses, guides)
- Relevant to the selected programming course
- Medium duration (4-20 minutes)

## Step 1: Get YouTube API Key

1. **Go to Google Cloud Console**
   - Visit: https://console.cloud.google.com/
   - Sign in with your Google account

2. **Create a New Project**
   - Click on the project dropdown at the top
   - Click "NEW PROJECT"
   - Enter a project name (e.g., "Programming Tutorials Platform")
   - Click "CREATE"

3. **Enable YouTube Data API**
   - In the navigation menu, go to "APIs & Services" → "Library"
   - Search for "YouTube Data API v3"
   - Click on it and then click "ENABLE"

4. **Create API Credentials**
   - Go to "APIs & Services" → "Credentials"
   - Click "+ CREATE CREDENTIALS"
   - Select "API key"
   - Copy the generated API key

5. **Restrict API Key (Recommended)**
   - Click on the API key you just created
   - Under "API restrictions", select "Restrict key"
   - Choose "YouTube Data API v3" from the dropdown
   - Under "Application restrictions", you can add your website domain if needed
   - Click "SAVE"

## Step 2: Configure API Key in Your Application

1. **Edit the configuration file**
   - Open: `config/youtube_api.php`
   - Replace `YOUR_YOUTUBE_API_KEY_HERE` with your actual API key:

```php
private $api_key = 'YOUR_ACTUAL_API_KEY_HERE';
```

## Step 3: Test the Integration

1. **Access the Fetch Videos Page**
   - Go to your admin panel
   - Click on "Fetch Videos" in the navigation
   - Select a course from the dropdown
   - Click "Fetch Videos"

2. **Expected Results**
   - The system will search YouTube for relevant videos
   - Filter videos based on the criteria (1M+ views, educational content)
   - Display a preview of fetched videos
   - Save them to your database automatically

## API Usage Limits

- **Free Tier**: 10,000 units per day
- **Video Search**: 100 units per request
- **Video Details**: 1 unit per request
- **Typical Usage**: ~201 units per course (1 search + 200 video details)

This means you can fetch videos for approximately 49 courses per day with the free tier.

## Troubleshooting

### Common Issues

1. **"YouTube API key not configured" Error**
   - Make sure you've replaced the placeholder API key in `config/youtube_api.php`

2. **"quotaExceeded" Error**
   - You've exceeded the daily API limit
   - Wait until the quota resets (daily at midnight Pacific Time)
   - Consider upgrading to a paid plan for higher limits

3. **No videos found**
   - Try different search terms or course titles
   - Some programming languages may have fewer high-view educational videos
   - Check if the API key has proper permissions

4. **"forbidden" Error**
   - Make sure the YouTube Data API v3 is enabled in your Google Cloud Console
   - Check if your API key has the correct restrictions

### Debug Mode

To debug API responses, you can temporarily add logging to the `makeAPIRequest` method in `includes/youtube_api_service.php`:

```php
private function makeAPIRequest($url) {
    $response = file_get_contents($url, false, $context);
    
    // Debug logging
    error_log("YouTube API Response: " . $response);
    
    return json_decode($response, true);
}
```

## Best Practices

1. **Cache Results**: Consider caching results to avoid repeated API calls
2. **Monitor Usage**: Keep track of your API usage to avoid hitting limits
3. **Error Handling**: The system includes proper error handling for API failures
4. **Rate Limiting**: Don't make too many requests in a short time period

## Security Notes

- Never expose your API key in client-side code
- Use HTTPS for all API requests
- Restrict your API key to specific domains if possible
- Regularly rotate your API keys for security

## Support

If you encounter issues:

1. Check the Google Cloud Console for API errors
2. Review the YouTube Data API documentation: https://developers.google.com/youtube/v3
3. Ensure your server can make external HTTP requests
4. Verify PHP extensions: `json`, `curl` or `file_get_contents` with HTTP streams

## Alternative Approach

If you prefer not to use the YouTube API, you can still:
- Manually add videos using the "Add New Video" button
- Import videos from CSV files
- Use the existing sample videos in the database
