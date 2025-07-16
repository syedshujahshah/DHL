<!DOCTYPE html>
<html>
<head>
    <title>DHL Clone - Debug Information</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { background: #f0f0f0; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>DHL Clone - Debug Information</h1>
    
    <?php
    echo "<div class='info'><strong>PHP Version:</strong> " . phpversion() . "</div>";
    echo "<div class='info'><strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</div>";
    echo "<div class='info'><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</div>";
    echo "<div class='info'><strong>Current Directory:</strong> " . getcwd() . "</div>";
    
    // Check if files exist
    $files = ['index.php', 'db.php', 'services.php', 'tracking.php', 'news.php'];
    echo "<h3>File Check:</h3>";
    foreach ($files as $file) {
        if (file_exists($file)) {
            echo "<div class='success'>✅ $file exists</div>";
        } else {
            echo "<div class='error'>❌ $file missing</div>";
        }
    }
    
    // Check PHP extensions
    echo "<h3>PHP Extensions:</h3>";
    $extensions = ['mysqli', 'pdo', 'pdo_mysql'];
    foreach ($extensions as $ext) {
        if (extension_loaded($ext)) {
            echo "<div class='success'>✅ $ext loaded</div>";
        } else {
            echo "<div class='error'>❌ $ext not loaded</div>";
        }
    }
    
    // Test database connection
    echo "<h3>Database Test:</h3>";
    try {
        if (file_exists('db.php')) {
            require_once 'db.php';
            $db = new Database();
            $conn = $db->getConnection();
            if ($conn) {
                echo "<div class='success'>✅ Database connection successful</div>";
                
                // Test a simple query
                $services = $db->getServices(null, 1);
                if (!empty($services)) {
                    echo "<div class='success'>✅ Database query successful</div>";
                } else {
                    echo "<div class='warning'>⚠️ Database connected but no data found</div>";
                }
            } else {
                echo "<div class='error'>❌ Database connection failed</div>";
            }
        } else {
            echo "<div class='error'>❌ db.php file not found</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Database error: " . $e->getMessage() . "</div>";
    }
    
    // Show error log if accessible
    echo "<h3>Recent Errors:</h3>";
    if (function_exists('error_get_last')) {
        $error = error_get_last();
        if ($error) {
            echo "<div class='error'>Last Error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line'] . "</div>";
        } else {
            echo "<div class='success'>No recent errors</div>";
        }
    }
    ?>
    
    <h3>Quick Links:</h3>
    <a href="index.php">Homepage</a> | 
    <a href="services.php">Services</a> | 
    <a href="tracking.php">Tracking</a> | 
    <a href="news.php">News</a>
</body>
</html>
