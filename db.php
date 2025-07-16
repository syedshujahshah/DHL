<?php
// Simple database configuration - modify these values
$DB_HOST = 'localhost';
$DB_USER = 'dbc2jpr0ndfelg';
$DB_PASS = 'yolpwow1mwr2';
$DB_NAME = 'dbc2jpr0ndfelg';

class Database {
    private $host;
    private $username;
    private $password;
    private $database;
    private $conn;

    public function __construct() {
        global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;
        $this->host = $DB_HOST;
        $this->username = $DB_USER;
        $this->password = $DB_PASS;
        $this->database = $DB_NAME;
    }

    public function getConnection() {
        if ($this->conn) {
            return $this->conn;
        }

        try {
            // Create connection
            $this->conn = new mysqli($this->host, $this->username, $this->password);
            
            // Check connection
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            // Create database if it doesn't exist
            $sql = "CREATE DATABASE IF NOT EXISTS `$this->database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            if (!$this->conn->query($sql)) {
                throw new Exception("Error creating database: " . $this->conn->error);
            }
            
            // Select database
            if (!$this->conn->select_db($this->database)) {
                throw new Exception("Error selecting database: " . $this->conn->error);
            }
            
            // Set charset
            $this->conn->set_charset("utf8mb4");
            
            // Initialize tables
            $this->initializeTables();
            
            return $this->conn;
            
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            return null;
        }
    }

    private function initializeTables() {
        try {
            // Check if services table exists
            $result = $this->conn->query("SHOW TABLES LIKE 'services'");
            if ($result->num_rows == 0) {
                $this->createTables();
                $this->insertSampleData();
            }
        } catch (Exception $e) {
            error_log("Error initializing tables: " . $e->getMessage());
        }
    }

    private function createTables() {
        $tables = [
            "CREATE TABLE IF NOT EXISTS services (
                id INT PRIMARY KEY AUTO_INCREMENT,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                image_url VARCHAR(255),
                category VARCHAR(100),
                price_range VARCHAR(100),
                delivery_time VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            
            "CREATE TABLE IF NOT EXISTS shipments (
                id INT PRIMARY KEY AUTO_INCREMENT,
                tracking_number VARCHAR(50) UNIQUE NOT NULL,
                sender_name VARCHAR(255) NOT NULL,
                sender_address TEXT NOT NULL,
                receiver_name VARCHAR(255) NOT NULL,
                receiver_address TEXT NOT NULL,
                service_type VARCHAR(100),
                status VARCHAR(50) DEFAULT 'Processing',
                weight DECIMAL(10,2),
                dimensions VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            
            "CREATE TABLE IF NOT EXISTS tracking_history (
                id INT PRIMARY KEY AUTO_INCREMENT,
                tracking_number VARCHAR(50),
                status VARCHAR(100),
                location VARCHAR(255),
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                description TEXT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            
            "CREATE TABLE IF NOT EXISTS news (
                id INT PRIMARY KEY AUTO_INCREMENT,
                title VARCHAR(255) NOT NULL,
                content TEXT,
                category VARCHAR(100),
                author VARCHAR(100),
                image_url VARCHAR(255),
                featured BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        ];

        foreach ($tables as $sql) {
            if (!$this->conn->query($sql)) {
                throw new Exception("Error creating table: " . $this->conn->error);
            }
        }
    }

    private function insertSampleData() {
        // Insert services
        $services = [
            ['DHL Express Worldwide', 'Fast international shipping with door-to-door delivery', '', 'Express', '$50-200', '1-3 days'],
            ['DHL Express 12:00', 'Next business day delivery by 12:00', '', 'Express', '$80-300', 'Next day by 12:00'],
            ['DHL Freight', 'Heavy cargo and freight solutions', '', 'Freight', '$200-2000', '3-7 days'],
            ['DHL E-commerce', 'Specialized e-commerce delivery solutions', '', 'E-commerce', '$10-50', '2-5 days'],
            ['DHL Same Day', 'Urgent same-day delivery service', '', 'Express', '$100-500', 'Same day'],
            ['DHL Supply Chain', 'Complete supply chain management', '', 'Logistics', 'Custom quote', 'Varies']
        ];

        $stmt = $this->conn->prepare("INSERT INTO services (title, description, image_url, category, price_range, delivery_time) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($services as $service) {
            $stmt->bind_param("ssssss", $service[0], $service[1], $service[2], $service[3], $service[4], $service[5]);
            $stmt->execute();
        }

        // Insert sample shipments and tracking data
        $shipments = [
            ['DHL123456789', 'John Smith', '123 Main St, New York, NY', 'Jane Doe', '456 Oak Ave, Los Angeles, CA', 'Express', 'In Transit', 2.5],
            ['DHL987654321', 'Mike Johnson', '789 Pine St, Chicago, IL', 'Sarah Wilson', '321 Elm St, Miami, FL', 'Standard', 'Delivered', 1.8],
            ['DHL456789123', 'David Brown', '555 Cedar Rd, Seattle, WA', 'Lisa Garcia', '777 Maple Dr, Boston, MA', 'Express', 'Processing', 3.2]
        ];

        $stmt = $this->conn->prepare("INSERT INTO shipments (tracking_number, sender_name, sender_address, receiver_name, receiver_address, service_type, status, weight) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($shipments as $shipment) {
            $stmt->bind_param("sssssssd", $shipment[0], $shipment[1], $shipment[2], $shipment[3], $shipment[4], $shipment[5], $shipment[6], $shipment[7]);
            $stmt->execute();
        }

        // Insert news
        $news = [
            ['DHL Expands Electric Vehicle Fleet', 'DHL announces major expansion of electric delivery vehicles...', 'Sustainability', 'DHL Press Team', 1],
            ['New Express Service to Asia', 'Introducing faster delivery options to Asian markets...', 'Services', 'Operations Team', 1],
            ['Holiday Shipping Guidelines', 'Important information about shipping during the holiday season...', 'Updates', 'Customer Service', 0]
        ];

        $stmt = $this->conn->prepare("INSERT INTO news (title, content, category, author, featured) VALUES (?, ?, ?, ?, ?)");
        foreach ($news as $article) {
            $stmt->bind_param("ssssi", $article[0], $article[1], $article[2], $article[3], $article[4]);
            $stmt->execute();
        }
    }

    public function getServices($category = null, $limit = null) {
        try {
            $conn = $this->getConnection();
            if (!$conn) return [];

            $sql = "SELECT * FROM services";
            $params = [];
            $types = "";

            if ($category) {
                $sql .= " WHERE category = ?";
                $params[] = $category;
                $types .= "s";
            }

            $sql .= " ORDER BY created_at DESC";

            if ($limit) {
                $sql .= " LIMIT ?";
                $params[] = $limit;
                $types .= "i";
            }

            $stmt = $conn->prepare($sql);
            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            $services = [];
            while ($row = $result->fetch_assoc()) {
                $services[] = $row;
            }
            return $services;

        } catch (Exception $e) {
            error_log("Error fetching services: " . $e->getMessage());
            return [];
        }
    }

    public function getServiceById($id) {
        try {
            $conn = $this->getConnection();
            if (!$conn) return null;

            $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();

        } catch (Exception $e) {
            error_log("Error fetching service: " . $e->getMessage());
            return null;
        }
    }

    public function trackShipment($tracking_number) {
        try {
            $conn = $this->getConnection();
            if (!$conn) return null;

            $stmt = $conn->prepare("SELECT * FROM shipments WHERE tracking_number = ?");
            $stmt->bind_param("s", $tracking_number);
            $stmt->execute();
            $result = $stmt->get_result();
            $shipment = $result->fetch_assoc();

            if ($shipment) {
                // Get tracking history
                $stmt = $conn->prepare("SELECT * FROM tracking_history WHERE tracking_number = ? ORDER BY timestamp DESC");
                $stmt->bind_param("s", $tracking_number);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $history = [];
                while ($row = $result->fetch_assoc()) {
                    $history[] = $row['timestamp'] . '|' . $row['status'] . '|' . $row['location'] . '|' . $row['description'];
                }
                $shipment['tracking_history'] = implode(';;', $history);
            }

            return $shipment;

        } catch (Exception $e) {
            error_log("Error tracking shipment: " . $e->getMessage());
            return null;
        }
    }

    public function getNews($featured = null, $limit = null) {
        try {
            $conn = $this->getConnection();
            if (!$conn) return [];

            $sql = "SELECT * FROM news";
            $params = [];
            $types = "";

            if ($featured !== null) {
                $sql .= " WHERE featured = ?";
                $params[] = $featured ? 1 : 0;
                $types .= "i";
            }

            $sql .= " ORDER BY created_at DESC";

            if ($limit) {
                $sql .= " LIMIT ?";
                $params[] = $limit;
                $types .= "i";
            }

            $stmt = $conn->prepare($sql);
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
            $conn = $this->getConnection();
            if (!$conn) return null;

            $stmt = $conn->prepare("SELECT * FROM news WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();

        } catch (Exception $e) {
            error_log("Error fetching news: " . $e->getMessage());
            return null;
        }
    }

    public function searchServices($keyword) {
        try {
            $conn = $this->getConnection();
            if (!$conn) return [];

            $searchTerm = "%$keyword%";
            $stmt = $conn->prepare("SELECT * FROM services WHERE title LIKE ? OR description LIKE ? ORDER BY created_at DESC");
            $stmt->bind_param("ss", $searchTerm, $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $services = [];
            while ($row = $result->fetch_assoc()) {
                $services[] = $row;
            }
            return $services;

        } catch (Exception $e) {
            error_log("Error searching services: " . $e->getMessage());
            return [];
        }
    }

    public function searchNews($keyword) {
        try {
            $conn = $this->getConnection();
            if (!$conn) return [];

            $searchTerm = "%$keyword%";
            $stmt = $conn->prepare("SELECT * FROM news WHERE title LIKE ? OR content LIKE ? ORDER BY created_at DESC");
            $stmt->bind_param("ss", $searchTerm, $searchTerm);
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
}
?>
