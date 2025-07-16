<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple authentication (in production, use proper session management)
session_start();
$admin_logged_in = isset($_SESSION['admin_logged_in']) ? $_SESSION['admin_logged_in'] : false;

// Simple login check
if (isset($_POST['admin_login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Simple credentials (in production, use hashed passwords and database)
    if ($username === 'admin' && $password === 'dhl2024') {
        $_SESSION['admin_logged_in'] = true;
        $admin_logged_in = true;
    } else {
        $login_error = "Invalid credentials. Try username: admin, password: dhl2024";
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: addnews.php');
    exit;
}

// Initialize variables
$success_message = '';
$error_message = '';
$news_list = [];

// Handle form submission (only if admin is logged in)
if ($admin_logged_in && $_POST && !isset($_POST['admin_login'])) {
    try {
        // Sanitize input data
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $author = trim($_POST['author'] ?? 'DHL Team');
        $featured = isset($_POST['featured']) ? 1 : 0;
        $custom_category = trim($_POST['custom_category'] ?? '');
        
        // Use custom category if provided
        if ($custom_category) {
            $category = $custom_category;
        }
        
        // Basic validation
        $errors = [];
        
        if (empty($title)) {
            $errors[] = "Title is required";
        }
        
        if (empty($content)) {
            $errors[] = "Content is required";
        }
        
        if (empty($category)) {
            $errors[] = "Category is required";
        }
        
        if (empty($errors)) {
            // Try to save to database
            if (file_exists('db.php')) {
                require_once 'db.php';
                $db = new Database();
                $conn = $db->getConnection();
                
                if ($conn) {
                    $stmt = $conn->prepare("INSERT INTO news (title, content, category, author, featured, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                    $stmt->bind_param("ssssi", $title, $content, $category, $author, $featured);
                    
                    if ($stmt->execute()) {
                        $success_message = "News article added successfully!";
                        // Clear form data after successful submission
                        $title = $content = $category = $author = $custom_category = '';
                        $featured = 0;
                    } else {
                        $error_message = "Database error: " . $stmt->error;
                    }
                } else {
                    $error_message = "Database connection failed";
                }
            } else {
                $error_message = "Database configuration file not found";
            }
        } else {
            $error_message = implode('<br>', $errors);
        }
        
    } catch (Exception $e) {
        error_log("Add news error: " . $e->getMessage());
        $error_message = "Sorry, there was an error processing your request. Please try again.";
    }
}

// Fetch existing news (only if admin is logged in)
if ($admin_logged_in) {
    try {
        if (file_exists('db.php')) {
            require_once 'db.php';
            $db = new Database();
            $conn = $db->getConnection();
            
            if ($conn) {
                $result = $conn->query("SELECT * FROM news ORDER BY created_at DESC LIMIT 10");
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $news_list[] = $row;
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching news: " . $e->getMessage());
    }
}

// Handle delete news
if ($admin_logged_in && isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        if (file_exists('db.php')) {
            require_once 'db.php';
            $db = new Database();
            $conn = $db->getConnection();
            
            if ($conn) {
                $delete_id = (int)$_GET['delete'];
                $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
                $stmt->bind_param("i", $delete_id);
                
                if ($stmt->execute()) {
                    $success_message = "News article deleted successfully!";
                    // Refresh the news list
                    header('Location: addnews.php');
                    exit;
                } else {
                    $error_message = "Error deleting news article";
                }
            }
        }
    } catch (Exception $e) {
        error_log("Delete news error: " . $e->getMessage());
        $error_message = "Error deleting news article";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $admin_logged_in ? 'Add News - DHL Admin' : 'Admin Login - DHL'; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background: linear-gradient(135deg, #ffcc00 0%, #ff6600 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .logo {
            font-size: 2.5rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            cursor: pointer;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .admin-welcome {
            font-size: 1.1rem;
        }

        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 20px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        /* Login Form */
        .login-container {
            max-width: 400px;
            margin: 4rem auto;
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
        }

        .login-title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 2rem;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        /* Main Content */
        main {
            padding: 2rem 0;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-title {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 1rem;
        }

        .page-subtitle {
            font-size: 1.2rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Admin Layout */
        .admin-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        /* Add News Form */
        .add-news-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .form-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #ffcc00;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #555;
        }

        .required {
            color: #dc3545;
        }

        input[type="text"],
        select,
        textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #ffcc00;
            box-shadow: 0 0 10px rgba(255,204,0,0.3);
        }

        textarea {
            resize: vertical;
            min-height: 200px;
            font-family: inherit;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        .submit-btn {
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255,204,0,0.4);
        }

        /* News List Sidebar */
        .news-list-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: fit-content;
        }

        .news-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            margin-bottom: 1rem;
        }

        .news-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .news-item-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .news-item-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .news-category {
            background: #e8f4fd;
            color: #1a5490;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: bold;
        }

        .featured-badge {
            background: #ff6600;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: bold;
        }

        .news-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .btn-small {
            padding: 0.3rem 0.8rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-view {
            background: #28a745;
            color: white;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-small:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        /* Messages */
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border: 1px solid #f5c6cb;
        }

        .login-error {
            background: #f8d7da;
            color: #721c24;
            padding: 0.8rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            border: 1px solid #f5c6cb;
            font-size: 0.9rem;
        }

        /* Quick Stats */
        .stats-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .stat-card {
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Navigation Links */
        .nav-links {
            background: #e8f4fd;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
        }

        .nav-links a {
            display: inline-block;
            margin: 0 0.5rem;
            padding: 0.5rem 1rem;
            background: #1a5490;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .nav-links a:hover {
            background: #0f3d6b;
            transform: translateY(-2px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .admin-layout {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .admin-info {
                flex-direction: column;
                text-align: center;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #ffcc00;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <?php if (!$admin_logged_in): ?>
        <!-- Login Form -->
        <div class="login-container">
            <h1 class="login-title">🔐 Admin Login</h1>
            
            <?php if (isset($login_error)): ?>
                <div class="login-error">
                    <strong>❌ Error!</strong> <?php echo $login_error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" name="admin_login" class="submit-btn">Login</button>
            </form>
            
            <div style="margin-top: 2rem; padding: 1rem; background: #e8f4fd; border-radius: 8px; font-size: 0.9rem;">
                <strong>Demo Credentials:</strong><br>
                Username: <code>admin</code><br>
                Password: <code>dhl2024</code>
            </div>
            
            <div style="margin-top: 1rem;">
                <a href="index.php" style="color: #666; text-decoration: none;">← Back to Homepage</a>
            </div>
        </div>
    <?php else: ?>
        <!-- Admin Panel -->
        <header>
            <div class="container">
                <div class="header-content">
                    <div class="logo" onclick="window.location.href='index.php'">DHL Admin</div>
                    <div class="admin-info">
                        <span class="admin-welcome">👋 Welcome, Admin!</span>
                        <a href="?logout=1" class="logout-btn">Logout</a>
                    </div>
                </div>
            </div>
        </header>

        <main>
            <div class="container">
                <div class="page-header">
                    <h1 class="page-title">📰 News Management</h1>
                    <p class="page-subtitle">Add, edit, and manage news articles for the DHL website.</p>
                </div>

                <div class="nav-links">
                    <a href="index.php">🏠 Homepage</a>
                    <a href="news.php">📰 View News</a>
                    <a href="services.php">🚚 Services</a>
                    <a href="tracking.php">📦 Tracking</a>
                    <a href="test-db.php">🔧 Database Test</a>
                </div>

                <?php if ($success_message): ?>
                    <div class="success-message">
                        <strong>✅ Success!</strong> <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="error-message">
                        <strong>❌ Error!</strong> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <div class="stats-section">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count($news_list); ?></div>
                            <div class="stat-label">Recent Articles</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count(array_filter($news_list, function($n) { return $n['featured']; })); ?></div>
                            <div class="stat-label">Featured Articles</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo date('Y-m-d'); ?></div>
                            <div class="stat-label">Today's Date</div>
                        </div>
                    </div>
                </div>

                <div class="admin-layout">
                    <div class="add-news-section">
                        <h2 class="form-title">➕ Add New Article</h2>
                        <form method="POST" id="newsForm">
                            <div class="form-group">
                                <label for="title">Article Title <span class="required">*</span></label>
                                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title ?? ''); ?>" required placeholder="Enter article title...">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="category">Category <span class="required">*</span></label>
                                    <select id="category" name="category" required>
                                        <option value="">Select category</option>
                                        <option value="Services" <?php echo ($category ?? '') === 'Services' ? 'selected' : ''; ?>>Services</option>
                                        <option value="Updates" <?php echo ($category ?? '') === 'Updates' ? 'selected' : ''; ?>>Updates</option>
                                        <option value="Sustainability" <?php echo ($category ?? '') === 'Sustainability' ? 'selected' : ''; ?>>Sustainability</option>
                                        <option value="Technology" <?php echo ($category ?? '') === 'Technology' ? 'selected' : ''; ?>>Technology</option>
                                        <option value="Global" <?php echo ($category ?? '') === 'Global' ? 'selected' : ''; ?>>Global</option>
                                        <option value="custom">Custom Category</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="author">Author</label>
                                    <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($author ?? 'DHL Team'); ?>" placeholder="Author name...">
                                </div>
                            </div>

                            <div class="form-group" id="customCategoryGroup" style="display: none;">
                                <label for="custom_category">Custom Category Name</label>
                                <input type="text" id="custom_category" name="custom_category" value="<?php echo htmlspecialchars($custom_category ?? ''); ?>" placeholder="Enter custom category name...">
                            </div>

                            <div class="form-group">
                                <label for="content">Article Content <span class="required">*</span></label>
                                <textarea id="content" name="content" required placeholder="Write your article content here..."><?php echo htmlspecialchars($content ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <div class="checkbox-group">
                                    <input type="checkbox" id="featured" name="featured" value="1" <?php echo ($featured ?? 0) ? 'checked' : ''; ?>>
                                    <label for="featured">⭐ Mark as Featured Article</label>
                                </div>
                            </div>

                            <button type="submit" class="submit-btn" id="submitBtn">
                                <span id="submitText">📝 Publish Article</span>
                                <span id="submitLoading" class="loading hidden"></span>
                            </button>
                        </form>
                    </div>

                    <div class="news-list-section">
                        <h3 class="form-title">📋 Recent Articles</h3>
                        <?php if (empty($news_list)): ?>
                            <p style="color: #666; text-align: center; padding: 2rem;">No articles found. Add your first article!</p>
                        <?php else: ?>
                            <?php foreach ($news_list as $news): ?>
                            <div class="news-item">
                                <div class="news-item-title"><?php echo htmlspecialchars(substr($news['title'], 0, 50)) . (strlen($news['title']) > 50 ? '...' : ''); ?></div>
                                <div class="news-item-meta">
                                    <span class="news-category"><?php echo htmlspecialchars($news['category']); ?></span>
                                    <?php if ($news['featured']): ?>
                                        <span class="featured-badge">Featured</span>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 0.8rem; color: #888; margin-bottom: 0.5rem;">
                                    By <?php echo htmlspecialchars($news['author']); ?> • <?php echo date('M d, Y', strtotime($news['created_at'])); ?>
                                </div>
                                <div class="news-actions">
                                    <a href="news-detail.php?id=<?php echo $news['id']; ?>" class="btn-small btn-view" target="_blank">👁️ View</a>
                                    <a href="?delete=<?php echo $news['id']; ?>" class="btn-small btn-delete" onclick="return confirm('Are you sure you want to delete this article?')">🗑️ Delete</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <div style="text-align: center; margin-top: 1rem;">
                            <a href="news.php" style="color: #1a5490; text-decoration: none; font-weight: bold;">📰 View All News →</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    <?php endif; ?>

    <script>
        // Show/hide custom category field
        document.getElementById('category').addEventListener('change', function() {
            const customGroup = document.getElementById('customCategoryGroup');
            const customInput = document.getElementById('custom_category');
            
            if (this.value === 'custom') {
                customGroup.style.display = 'block';
                customInput.required = true;
            } else {
                customGroup.style.display = 'none';
                customInput.required = false;
                customInput.value = '';
            }
        });

        // Form submission with loading state
        document.getElementById('newsForm')?.addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitLoading = document.getElementById('submitLoading');
            
            // Show loading state
            submitBtn.disabled = true;
            submitText.classList.add('hidden');
            submitLoading.classList.remove('hidden');
        });

        // Auto-resize textarea
        document.getElementById('content')?.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });

        // Character counter for title
        document.getElementById('title')?.addEventListener('input', function() {
            const maxLength = 100;
            const currentLength = this.value.length;
            
            // Remove existing counter
            const existingCounter = this.parentNode.querySelector('.char-counter');
            if (existingCounter) {
                existingCounter.remove();
            }
            
            // Add counter
            const counter = document.createElement('div');
            counter.className = 'char-counter';
            counter.style.cssText = 'font-size: 0.8rem; color: #666; margin-top: 0.3rem;';
            counter.textContent = `${currentLength}/${maxLength} characters`;
            
            if (currentLength > maxLength) {
                counter.style.color = '#dc3545';
            }
            
            this.parentNode.appendChild(counter);
        });

        // Form validation
        function validateForm() {
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            const category = document.getElementById('category').value;
            const customCategory = document.getElementById('custom_category').value.trim();
            
            if (!title || !content) {
                alert('Please fill in all required fields.');
                return false;
            }
            
            if (category === 'custom' && !customCategory) {
                alert('Please enter a custom category name.');
                return false;
            }
            
            if (title.length > 100) {
                alert('Title must be 100 characters or less.');
                return false;
            }
            
            return true;
        }

        // Auto-save draft (localStorage)
        function saveDraft() {
            const formData = {
                title: document.getElementById('title')?.value || '',
                content: document.getElementById('content')?.value || '',
                category: document.getElementById('category')?.value || '',
                author: document.getElementById('author')?.value || '',
                custom_category: document.getElementById('custom_category')?.value || '',
                featured: document.getElementById('featured')?.checked || false
            };
            
            localStorage.setItem('dhl_news_draft', JSON.stringify(formData));
        }

        // Load draft
        function loadDraft() {
            const draft = localStorage.getItem('dhl_news_draft');
            if (draft) {
                const formData = JSON.parse(draft);
                
                if (document.getElementById('title')) document.getElementById('title').value = formData.title || '';
                if (document.getElementById('content')) document.getElementById('content').value = formData.content || '';
                if (document.getElementById('category')) document.getElementById('category').value = formData.category || '';
                if (document.getElementById('author')) document.getElementById('author').value = formData.author || '';
                if (document.getElementById('custom_category')) document.getElementById('custom_category').value = formData.custom_category || '';
                if (document.getElementById('featured')) document.getElementById('featured').checked = formData.featured || false;
                
                // Trigger category change event
                if (formData.category === 'custom') {
                    document.getElementById('category').dispatchEvent(new Event('change'));
                }
            }
        }

        // Auto-save every 30 seconds
        if (document.getElementById('newsForm')) {
            loadDraft();
            
            setInterval(saveDraft, 30000);
            
            // Save on form input
            document.getElementById('newsForm').addEventListener('input', saveDraft);
            
            // Clear draft on successful submission
            document.getElementById('newsForm').addEventListener('submit', function() {
                setTimeout(() => {
                    if (document.querySelector('.success-message')) {
                        localStorage.removeItem('dhl_news_draft');
                    }
                }, 1000);
            });
        }

        // Confirmation for delete
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this article? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
