<?php
/**
 * Add Course - Create new programming course
 */

require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $programming_language = sanitize($_POST['programming_language']);
    $difficulty_level = sanitize($_POST['difficulty_level']);
    $thumbnail_url = sanitize($_POST['thumbnail_url']);
    
    // CSRF validation
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = "Invalid request. Please try again.";
    } else {
        // Validate inputs
        if (empty($title) || empty($description) || empty($programming_language) || empty($difficulty_level)) {
            $error_message = "Please fill in all required fields.";
        } else {
            // Insert new course
            $query = "INSERT INTO courses (title, description, programming_language, difficulty_level, thumbnail_url) 
                     VALUES (:title, :description, :programming_language, :difficulty_level, :thumbnail_url)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':programming_language', $programming_language);
            $stmt->bindParam(':difficulty_level', $difficulty_level);
            $stmt->bindParam(':thumbnail_url', $thumbnail_url);
            
            if ($stmt->execute()) {
                $success_message = "Course created successfully!";
                // Clear form
                $_POST = array();
            } else {
                $error_message = "Failed to create course. Please try again.";
            }
        }
    }
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-nav {
            background: var(--primary-dark);
            padding: 1rem 0;
        }
        .admin-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }
        .admin-nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        .admin-nav-links a:hover,
        .admin-nav-links a.active {
            background: var(--primary-medium);
        }
        .page-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%);
            color: white;
            padding: 2rem 0;
        }
        .page-header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .page-title {
            font-size: 2rem;
            margin: 0;
            color: var(--white);
        }
        .form-container {
            max-width: 900px;
            margin: 2rem auto;
            background: white;
            padding: 2.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
        }
        .form-header {
            text-align: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--gray-100);
        }
        .form-header h2 {
            font-size: var(--text-2xl);
            font-weight: var(--font-semibold);
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }
        .form-header p {
            color: var(--gray-600);
            font-size: var(--text-base);
            margin: 0;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-weight: var(--font-semibold);
            color: var(--gray-700);
            margin-bottom: 0.5rem;
            font-size: var(--text-sm);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .required-indicator {
            color: var(--error-border);
            font-weight: var(--font-bold);
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: var(--text-base);
            font-family: var(--font-family-primary);
            transition: all var(--transition-fast);
            background-color: var(--white);
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-accent);
            box-shadow: 0 0 0 3px rgba(61, 68, 112, 0.1);
        }
        .form-group input:hover,
        .form-group select:hover,
        .form-group textarea:hover {
            border-color: var(--gray-300);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
            line-height: 1.5;
        }
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        .form-help-text {
            font-size: var(--text-xs);
            color: var(--gray-500);
            margin-top: 0.25rem;
            font-style: italic;
        }
        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            align-items: end;
        }
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            padding-top: 2rem;
            border-top: 2px solid var(--gray-100);
            margin-top: 2rem;
        }
        .preview-section {
            margin-top: 3rem;
            padding: 2rem;
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--primary-light) 100%);
            border-radius: var(--radius-lg);
            border: 2px solid var(--primary-light);
            box-shadow: var(--shadow-md);
        }
        .preview-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        .preview-icon {
            width: 32px;
            height: 32px;
            background: var(--primary-accent);
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: var(--text-sm);
        }
        .preview-title {
            font-size: var(--text-xl);
            font-weight: var(--font-semibold);
            color: var(--gray-900);
            margin: 0;
        }
        .preview-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
            transition: transform var(--transition-normal);
        }
        .preview-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }
        .preview-thumbnail {
            height: 180px;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 2.5rem;
            font-weight: var(--font-bold);
            position: relative;
            overflow: hidden;
        }
        .preview-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .preview-content {
            padding: 1.5rem;
        }
        .preview-meta {
            display: flex;
            gap: 1rem;
            font-size: var(--text-sm);
            color: var(--gray-600);
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-200);
        }
        .preview-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-full);
            font-size: var(--text-xs);
            font-weight: var(--font-semibold);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .preview-language-badge {
            background-color: var(--primary-light);
            color: var(--primary-dark);
        }
        .preview-difficulty-badge {
            background-color: var(--gray-100);
            color: var(--gray-700);
        }
    </style>
</head>
<body>
    <!-- Admin Navigation -->
    <nav class="admin-nav">
        <div class="container">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <a href="../index.php" class="logo" style="color: white;">📚 CodeTutorials</a>
                <span style="color: #bdc3c7;">|</span>
                <span style="color: white;">Admin Panel</span>
            </div>
            <ul class="admin-nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="courses.php" class="active">Courses</a></li>
                <li><a href="videos.php">Videos</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 class="page-title">Add New Course</h1>
            <a href="courses.php" class="btn btn-outline-white">Back to Courses</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <?php if (isset($success_message)): ?>
            <?= displayAlert('success', $success_message); ?>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <?= displayAlert('error', $error_message); ?>
        <?php endif; ?>

        <div class="form-container">
            <div class="form-header">
                <h2>Create New Course</h2>
                <p>Fill in the details below to add a new programming course to the platform</p>
            </div>
            
            <form method="POST" action="add_course.php" id="courseForm">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="title">
                            Course Title
                            <span class="required-indicator">*</span>
                        </label>
                        <input type="text" id="title" name="title" required 
                               value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                               placeholder="e.g., Python Programming for Beginners">
                        <div class="form-help-text">Choose a clear, descriptive title for your course</div>
                    </div>

                    <div class="form-group">
                        <label for="programming_language">
                            Programming Language
                            <span class="required-indicator">*</span>
                        </label>
                        <select id="programming_language" name="programming_language" required>
                            <option value="">Select Language</option>
                            <option value="Python" <?= (isset($_POST['programming_language']) && $_POST['programming_language'] === 'Python') ? 'selected' : ''; ?>>Python</option>
                            <option value="JavaScript" <?= (isset($_POST['programming_language']) && $_POST['programming_language'] === 'JavaScript') ? 'selected' : ''; ?>>JavaScript</option>
                            <option value="Java" <?= (isset($_POST['programming_language']) && $_POST['programming_language'] === 'Java') ? 'selected' : ''; ?>>Java</option>
                            <option value="PHP" <?= (isset($_POST['programming_language']) && $_POST['programming_language'] === 'PHP') ? 'selected' : ''; ?>>PHP</option>
                            <option value="C++" <?= (isset($_POST['programming_language']) && $_POST['programming_language'] === 'C++') ? 'selected' : ''; ?>>C++</option>
                            <option value="C#" <?= (isset($_POST['programming_language']) && $_POST['programming_language'] === 'C#') ? 'selected' : ''; ?>>C#</option>
                            <option value="Ruby" <?= (isset($_POST['programming_language']) && $_POST['programming_language'] === 'Ruby') ? 'selected' : ''; ?>>Ruby</option>
                            <option value="Go" <?= (isset($_POST['programming_language']) && $_POST['programming_language'] === 'Go') ? 'selected' : ''; ?>>Go</option>
                            <option value="Rust" <?= (isset($_POST['programming_language']) && $_POST['programming_language'] === 'Rust') ? 'selected' : ''; ?>>Rust</option>
                            <option value="Swift" <?= (isset($_POST['programming_language']) && $_POST['programming_language'] === 'Swift') ? 'selected' : ''; ?>>Swift</option>
                        </select>
                        <div class="form-help-text">Select the primary programming language</div>
                    </div>

                    <div class="form-group">
                        <label for="difficulty_level">
                            Difficulty Level
                            <span class="required-indicator">*</span>
                        </label>
                        <select id="difficulty_level" name="difficulty_level" required>
                            <option value="">Select Level</option>
                            <option value="beginner" <?= (isset($_POST['difficulty_level']) && $_POST['difficulty_level'] === 'beginner') ? 'selected' : ''; ?>>Beginner</option>
                            <option value="intermediate" <?= (isset($_POST['difficulty_level']) && $_POST['difficulty_level'] === 'intermediate') ? 'selected' : ''; ?>>Intermediate</option>
                            <option value="advanced" <?= (isset($_POST['difficulty_level']) && $_POST['difficulty_level'] === 'advanced') ? 'selected' : ''; ?>>Advanced</option>
                        </select>
                        <div class="form-help-text">Choose the appropriate difficulty level</div>
                    </div>

                    <div class="form-group">
                        <label for="thumbnail_url">Thumbnail URL</label>
                        <input type="url" id="thumbnail_url" name="thumbnail_url" 
                               value="<?= isset($_POST['thumbnail_url']) ? htmlspecialchars($_POST['thumbnail_url']) : ''; ?>"
                               placeholder="https://example.com/thumbnail.jpg">
                        <div class="form-help-text">Optional: Add a custom thumbnail image URL</div>
                    </div>

                    <div class="form-group full-width">
                        <label for="description">
                            Course Description
                            <span class="required-indicator">*</span>
                        </label>
                        <textarea id="description" name="description" required rows="5"
                                  placeholder="Describe what students will learn in this course, including key topics, prerequisites, and learning outcomes..."><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        <div class="form-help-text">Provide a comprehensive description (minimum 10 characters)</div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="courses.php" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <span>📚</span>
                        Create Course
                    </button>
                </div>
            </form>

            <!-- Live Preview -->
            <div class="preview-section" id="previewSection" style="display: none;">
                <div class="preview-header">
                    <div class="preview-icon">👁️</div>
                    <h3 class="preview-title">Live Preview</h3>
                </div>
                <div class="preview-card">
                    <div class="preview-thumbnail" id="previewThumbnail">
                        <span id="previewLanguageIcon">--</span>
                    </div>
                    <div class="preview-content">
                        <h4 id="previewTitle">Course Title</h4>
                        <p id="previewDescription">Course description will appear here...</p>
                        <div class="preview-meta">
                            <span id="previewLanguage" class="preview-badge preview-language-badge">Language</span>
                            <span id="previewDifficulty" class="preview-badge preview-difficulty-badge">Difficulty</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Live preview functionality
        const form = document.getElementById('courseForm');
        const previewSection = document.getElementById('previewSection');
        const titleInput = document.getElementById('title');
        const descriptionInput = document.getElementById('description');
        const languageSelect = document.getElementById('programming_language');
        const difficultySelect = document.getElementById('difficulty_level');
        const thumbnailInput = document.getElementById('thumbnail_url');

        // Preview elements
        const previewTitle = document.getElementById('previewTitle');
        const previewDescription = document.getElementById('previewDescription');
        const previewLanguage = document.getElementById('previewLanguage');
        const previewDifficulty = document.getElementById('previewDifficulty');
        const previewLanguageIcon = document.getElementById('previewLanguageIcon');
        const previewThumbnail = document.getElementById('previewThumbnail');

        function updatePreview() {
            const hasContent = titleInput.value || descriptionInput.value || languageSelect.value || difficultySelect.value;
            
            if (hasContent) {
                previewSection.style.display = 'block';
                previewTitle.textContent = titleInput.value || 'Course Title';
                previewDescription.textContent = descriptionInput.value || 'Course description will appear here...';
                previewLanguage.textContent = languageSelect.value || 'Language';
                previewDifficulty.textContent = difficultySelect.value || 'Difficulty';
                
                if (languageSelect.value) {
                    previewLanguageIcon.textContent = languageSelect.value.substring(0, 2).toUpperCase();
                } else {
                    previewLanguageIcon.textContent = '--';
                }
                
                // Update thumbnail if URL provided
                if (thumbnailInput.value) {
                    previewThumbnail.innerHTML = `<img src="${thumbnailInput.value}" style="width: 100%; height: 100%; object-fit: cover;">`;
                } else {
                    previewThumbnail.innerHTML = `<span id="previewLanguageIcon">${languageSelect.value ? languageSelect.value.substring(0, 2).toUpperCase() : '--'}</span>`;
                }
                
                // Update difficulty badge color
                const difficultyColors = {
                    'beginner': '#d4edda',
                    'intermediate': '#fff3cd',
                    'advanced': '#f8d7da'
                };
                
                if (difficultySelect.value) {
                    previewDifficulty.style.backgroundColor = difficultyColors[difficultySelect.value];
                    previewDifficulty.style.padding = '0.25rem 0.5rem';
                    previewDifficulty.style.borderRadius = '15px';
                    previewDifficulty.style.fontSize = '0.75rem';
                    previewDifficulty.style.fontWeight = 'bold';
                    previewDifficulty.style.textTransform = 'uppercase';
                }
            } else {
                previewSection.style.display = 'none';
            }
        }

        // Add event listeners for live preview
        titleInput.addEventListener('input', updatePreview);
        descriptionInput.addEventListener('input', updatePreview);
        languageSelect.addEventListener('change', updatePreview);
        difficultySelect.addEventListener('change', updatePreview);
        thumbnailInput.addEventListener('input', updatePreview);

        // Form validation
        form.addEventListener('submit', function(e) {
            const title = titleInput.value.trim();
            const description = descriptionInput.value.trim();
            const language = languageSelect.value;
            const difficulty = difficultySelect.value;
            
            if (!title || !description || !language || !difficulty) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
            
            if (title.length < 3) {
                e.preventDefault();
                alert('Course title must be at least 3 characters long.');
                return false;
            }
            
            if (description.length < 10) {
                e.preventDefault();
                alert('Course description must be at least 10 characters long.');
                return false;
            }
        });

        // Character counter for description
        descriptionInput.addEventListener('input', function() {
            const charCount = this.value.length;
            const charCounter = document.getElementById('charCounter');
            
            if (!charCounter) {
                const counter = document.createElement('small');
                counter.id = 'charCounter';
                counter.style.color = '#666';
                counter.style.fontSize = '0.85rem';
                counter.style.marginTop = '0.25rem';
                counter.style.display = 'block';
                this.parentElement.appendChild(counter);
            }
            
            document.getElementById('charCounter').textContent = `${charCount} characters`;
        });

        // Auto-focus on title field
        titleInput.focus();
    </script>
</body>
</html>
