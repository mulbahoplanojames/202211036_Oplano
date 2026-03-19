# Course Favorites Feature - Installation Guide

## Database Migration

To enable the course favorites feature, you need to run the database migration. Follow these steps:

### Option 1: Using phpMyAdmin
1. Open phpMyAdmin in your web browser
2. Select the `programming_tutorials` database
3. Click on the "SQL" tab
4. Copy and paste the contents of `database/course_favorites_migration.sql`
5. Click "Go" to execute the migration

### Option 2: Using MySQL Command Line
```bash
mysql -u [username] -p programming_tutorials < database/course_favorites_migration.sql
```

### Option 3: Using the Database Import in Your Control Panel
1. Access your hosting control panel (cPanel, Plesk, etc.)
2. Find the "phpMyAdmin" or "Database Management" tool
3. Select the `programming_tutorials` database
4. Click "Import"
5. Choose the `database/course_favorites_migration.sql` file
6. Click "Go" or "Import"

## What This Migration Does

The migration creates a new table called `course_favorites` with the following structure:
- `id` - Primary key
- `user_id` - Links to the users table
- `course_id` - Links to the courses table  
- `created_at` - Timestamp when the course was favorited
- Unique constraint to prevent duplicate favorites
- Foreign key constraints for data integrity

## Feature Summary

After installing the migration, users will be able to:
1. **Add courses to favorites** - Click the heart icon (🤍) on any course
2. **Remove courses from favorites** - Click the red heart icon (❤️) to unfavorite
3. **View all favorite courses** - Visit "My Favorites" page to see saved courses
4. **Separate tabs** - Switch between favorite courses and favorite videos
5. **Persistent favorites** - Favorites are saved per user and persist across sessions

## Files Modified/Created

### New Files:
- `database/course_favorites_migration.sql` - Database migration script

### Modified Files:
- `includes/functions.php` - Added course favorites functions
- `course.php` - Added favorite button and toggle functionality
- `student/favorites.php` - Added courses tab alongside videos
- `courses.php` - Added favorite buttons to course listing

## Testing the Feature

1. Log in as a student user
2. Go to any course page (e.g., `course.php?id=1`)
3. Click the "Add to Favorites" button (🤍)
4. Verify the button changes to "Favorited" (❤️)
5. Navigate to "My Favorites" page
6. Switch to the "Courses" tab
7. Verify the course appears in favorites
8. Click the favorite button again to remove it
9. Verify it's removed from favorites

## Notes

- The feature is completely separate from video favorites
- Only logged-in users can favorite courses
- The existing video favorites functionality remains unchanged
- All favorite data is preserved even if courses are deactivated
