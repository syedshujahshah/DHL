<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DHL Clone - Database Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
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
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
        .test-result {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            background: #f8f9fa;
        }
        .nav-links {
            text-align: center;
            margin-top: 30px;
        }
        .nav-links a {
            display: inline-block;
            margin: 0 10px;
            padding: 10px 20px;
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .nav-links a:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255,204,0,0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚚 DHL Clone - Database Test</h1>
        
        <?php
        require_once 'db.php';
        
        try {
            $db = new Database();
            echo "<div class='test-result success'>✅ Database class loaded successfully</div>";
            
            $conn = $db->getConnection();
            if ($conn) {
                echo "<div class='test-result success'>✅ Database connection established</div>";
                echo "<div class='test-result info'>" . $db->testConnection() . "</div>";
                
                // Test each table
                $tables = ['services', 'shipments', 'tracking_history', 'news'];
                
                foreach ($tables as $table) {
                    try {
                        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM $table");
                        $stmt->execute();
                        $result = $stmt->fetch();
                        echo "<div class='test-result success'>📊 Table '$table': {$result['count']} records</div>";
                    } catch (Exception $e) {
                        echo "<div class='test-result error'>❌ Error with table '$table': " . $e->getMessage() . "</div>";
                    }
                }
                
                // Test database functions
                echo "<h3>Testing Database Functions:</h3>";
                
                $services = $db->getServices(null, 3);
                echo "<div class='test-result " . (count($services) > 0 ? 'success' : 'warning') . "'>🔧 Services function: " . count($services) . " services retrieved</div>";
                
                $news = $db->getNews(null, 3);
                echo "<div class='test-result " . (count($news) > 0 ? 'success' : 'warning') . "'>📰 News function: " . count($news) . " news articles retrieved</div>";
                
                $tracking = $db->trackShipment('DHL123456789');
                if ($tracking) {
                    echo "<div class='test-result success'>📦 Tracking function: Successfully retrieved tracking for DHL123456789</div>";
                } else {
                    echo "<div class='test-result warning'>⚠️ Tracking function: No data found for test tracking number</div>";
                }
                
                // Test search functions
                $searchResults = $db->searchServices('express');
                echo "<div class='test-result " . (count($searchResults) > 0 ? 'success' : 'warning') . "'>🔍 Search function: " . count($searchResults) . " results for 'express'</div>";
                
                echo "<div class='test-result success'><strong>✅ All database tests completed successfully!</strong></div>";
                
            } else {
                echo "<div class='test-result error'>❌ Database connection failed!</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='test-result error'>❌ Database error: " . $e->getMessage() . "</div>";
            echo "<div class='test-result info'>💡 Try running the database setup to configure your connection properly.</div>";
        }
        ?>
        
        <div class="nav-links">
            <a href="setup.php">🔧 Database Setup</a>
            <a href="index.php">🏠 Homepage</a>
            <a href="services.php">🚚 Services</a>
            <a href="tracking.php">📦 Tracking</a>
        </div>
    </div>
</body>
</html>
