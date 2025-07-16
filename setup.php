<?php
if ($_POST) {
    $host = $_POST['host'] ?? 'localhost';
    $username = $_POST['username'] ?? 'root';
    $password = $_POST['password'] ?? '';
    $database = $_POST['database'] ?? 'dhl_clone';
    
    try {
        // Test connection
        $dsn = "mysql:host=$host;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Update db.php file
        $dbContent = "<?php
class Database {
    private \$host = '$host';
    private \$db_name = '$database';
    private \$username = '$username';
    private \$password = '$password';
    private \$conn;

    public function getConnection() {
        if (\$this->conn) {
            return \$this->conn;
        }
        
        try {
            \$dsn = \"mysql:host=\" . \$this->host . \";dbname=\" . \$this->db_name . \";charset=utf8mb4\";
            \$options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => \"SET NAMES utf8mb4\"
            ];
            
            \$this->conn = new PDO(\$dsn, \$this->username, \$this->password, \$options);
            \$this->initializeTables();
            return \$this->conn;
            
        } catch(PDOException \$exception) {
            error_log(\"Database Connection Error: \" . \$exception->getMessage());
            die(\"Database connection failed. Please check your database configuration.\");
        }
    }
    
    // ... rest of the methods from the updated db.php file above
}
?>";
        
        file_put_contents('db.php', $dbContent);
        
        $success = "✅ Database connection configured successfully! <a href='index.php'>Go to Homepage</a>";
        
    } catch (Exception $e) {
        $error = "❌ Connection failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DHL Clone - Database Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f8f9fa;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #ffcc00;
        }
        button {
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255,204,0,0.4);
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚚 DHL Clone - Database Setup</h1>
        
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="info">
            <strong>Common Database Configurations:</strong><br>
            • XAMPP: Host=localhost, Username=root, Password=(empty)<br>
            • WAMP: Host=localhost, Username=root, Password=root<br>
            • MAMP: Host=localhost, Username=root, Password=root<br>
            • Live Server: Use your hosting provider's details
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="host">Database Host:</label>
                <input type="text" id="host" name="host" value="<?php echo $_POST['host'] ?? 'localhost'; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="username">Database Username:</label>
                <input type="text" id="username" name="username" value="<?php echo $_POST['username'] ?? 'root'; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Database Password:</label>
                <input type="password" id="password" name="password" value="<?php echo $_POST['password'] ?? ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="database">Database Name:</label>
                <input type="text" id="database" name="database" value="<?php echo $_POST['database'] ?? 'dhl_clone'; ?>" required>
            </div>
            
            <button type="submit">🔧 Configure Database</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="test-db.php" style="color: #666;">Test Current Configuration</a>
        </div>
    </div>
</body>
</html>
