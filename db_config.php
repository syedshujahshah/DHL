<?php
// Enhanced database configuration file
// Copy this to db.php or update your existing db.php

class Database {
    // Database configuration - UPDATE THESE VALUES
    private $host = 'localhost';        // Your database host
    private $username = 'root';         // Your database username  
    private $password = '';             // Your database password
    private $database = 'dhl_clone';    // Your database name
    private $conn;

    public function __construct() {
        // You can also set these from environment variables for security
        // $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        // $this->username = $_ENV['DB_USER'] ?? 'root';
        // $this->password = $_ENV['DB_PASS'] ?? '';
        // $this->database = $_ENV['DB_NAME'] ?? 'dhl_clone';
    }

    public function getConnection() {
        if ($this->conn) {
            return $this->conn;
        }

        try {
            // Create connection with error reporting
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            
            $this->conn = new mysqli($this->host, $this->username, $this->password);
            
            // Set charset
            $this->conn->set_charset("utf8mb4");
            
            // Create database if it doesn't exist
            $sql = "CREATE DATABASE IF NOT EXISTS `{$this->database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            if (!$this->conn->query($sql)) {
                throw new Exception("Error creating database: " . $this->conn->error);
            }
            
            // Select database
            if (!$this->conn->select_db($this->database)) {
                throw new Exception("Error selecting database: " . $this->conn->error);
            }
            
            // Initialize tables if needed
            $this->initializeTables();
            
            return $this->conn;
            
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    private function initializeTables() {
        try {
            // Check if news table exists
            $result = $this->conn->query("SHOW TABLES LIKE 'news'");
            if ($result->num_rows == 0) {
                $this->createNewsTable();
                $this->insertSampleNews();
            }
        } catch (Exception $e) {
            error_log("Error initializing tables: " . $e->getMessage());
            throw $e;
        }
    }

    private function createNewsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS news (
            id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            category VARCHAR(100) NOT NULL,
            author VARCHAR(100) DEFAULT 'DHL Team',
            image_url VARCHAR(255) DEFAULT NULL,
            featured BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_category (category),
            INDEX idx_featured (featured),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        if (!$this->conn->query($sql)) {
            throw new Exception("Error creating news table: " . $this->conn->error);
        }
    }

    private function insertSampleNews() {
        $news_data = [
            [
                'title' => 'DHL Expands Electric Vehicle Fleet',
                'content' => 'DHL announces a major expansion of its electric delivery vehicle fleet across major cities worldwide. This initiative is part of our commitment to sustainable logistics and reducing carbon emissions.',
                'category' => 'Sustainability',
                'author' => 'DHL Press Team',
                'featured' => 1
            ],
            [
                'title' => 'New Express Service to Asia',
                'content' => 'Introducing faster delivery options to Asian markets with improved transit times and enhanced tracking capabilities.',
                'category' => 'Services',
                'author' => 'Operations Team',
                'featured' => 1
            ],
            [
                'title' => 'Holiday Shipping Guidelines 2024',
                'content' => 'Important information about shipping during the upcoming holiday season. Plan ahead to ensure timely delivery.',
                'category' => 'Updates',
                'author' => 'Customer Service',
                'featured' => 0
            ]
        ];

        $stmt = $this->conn->prepare("INSERT INTO news (title, content, category, author, featured) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($news_data as $news) {
            $stmt->bind_param("ssssi", $news['title'], $news['content'], $news['category'], $news['author'], $news['featured']);
            $stmt->execute();
        }
    }

    // News-related methods
    public function addNews($title, $content, $category, $author = 'DHL Team', $featured = 0) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO news (title, content, category, author, featured, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssssi", $title, $content, $category, $author, $featured);
            
            if ($stmt->execute()) {
                return $this->conn->insert_id;
            } else {
                throw new Exception("Error adding news: " . $stmt->error);
            }
        } catch (Exception $e) {
            error_log("Error in addNews: " . $e->getMessage());
            throw $e;
        }
    }

    public function getNews($featured = null, $limit = null, $category = null) {
        try {
            $sql = "SELECT * FROM news WHERE 1=1";
            $params = [];
            $types = "";

            if ($featured !== null) {
                $sql .= " AND featured = ?";
                $params[] = $featured ? 1 : 0;
                $types .= "i";
            }

            if ($category) {
                $sql .= " AND category = ?";
                $params[] = $category;
                $types .= "s";
            }

            $sql .= " ORDER BY created_at DESC";

            if ($limit) {
                $sql .= " LIMIT ?";
                $params[] = $limit;
                $types .= "i";
            }

            $stmt = $this->conn->prepare($sql);
            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            $news = [];
            while ($row = $result->fetch_assoc()) {
                $news[] = $row;
            }
            return $news;

        } catch (Exception $e) {
            error_log("Error fetching news: " . $e->getMessage());
            return [];
        }
    }

    public function getNewsById($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM news WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error fetching news by ID: " . $e->getMessage());
            return null;
        }
    }

    public function deleteNews($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM news WHERE id = ?");
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error deleting news: " . $e->getMessage());
            return false;
        }
    }

    public function updateNews($id, $title, $content, $category, $author, $featured = 0) {
        try {
            $stmt = $this->conn->prepare("UPDATE news SET title = ?, content = ?, category = ?, author = ?, featured = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("sssiii", $title, $content, $category, $author, $featured, $id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating news: " . $e->getMessage());
            return false;
        }
    }

    public function searchNews($keyword) {
        try {
            $searchTerm = "%$keyword%";
            $stmt = $this->conn->prepare("SELECT * FROM news WHERE title LIKE ? OR content LIKE ? OR category LIKE ? ORDER BY created_at DESC");
            $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $news = [];
            while ($row = $result->fetch_assoc()) {
                $news[] = $row;
            }
            return $news;
        } catch (Exception $e) {
            error_log("Error searching news: " . $e->getMessage());
            return [];
        }
    }

    // Test connection method
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            if ($conn) {
                $result = $conn->query("SELECT 1");
                if ($result) {
                    return "✅ Database connection successful! Server version: " . $conn->server_info;
                }
            }
            return "❌ Database connection failed";
        } catch (Exception $e) {
            return "❌ Database error: " . $e->getMessage();
        }
    }

    // Get database statistics
    public function getStats() {
        try {
            $stats = [];
            
            // Count total news
            $result = $this->conn->query("SELECT COUNT(*) as total FROM news");
            $stats['total_news'] = $result->fetch_assoc()['total'];
            
            // Count featured news
            $result = $this->conn->query("SELECT COUNT(*) as featured FROM news WHERE featured = 1");
            $stats['featured_news'] = $result->fetch_assoc()['featured'];
            
            // Count by category
            $result = $this->conn->query("SELECT category, COUNT(*) as count FROM news GROUP BY category ORDER BY count DESC");
            $stats['categories'] = [];
            while ($row = $result->fetch_assoc()) {
                $stats['categories'][] = $row;
            }
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting stats: " . $e->getMessage());
            return [];
        }
    }

    // Close connection
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    // Destructor
    public function __destruct() {
        $this->close();
    }

    // Legacy methods for compatibility with existing code
    public function getServices($category = null, $limit = null) {
        // Your existing getServices method
        return [];
    }

    public function trackShipment($tracking_number) {
        // Your existing trackShipment method
        return null;
    }

    public function searchServices($keyword) {
        // Your existing searchServices method
        return [];
    }

    public function getServiceById($id) {
        // Your existing getServiceById method
        return null;
    }
}
?>
