<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Try to include database
    $db = null;
    $services = [];
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    $search = isset($_GET['search']) ? trim($_GET['search']) : null;
    
    if (file_exists('db.php')) {
        try {
            require_once 'db.php';
            $db = new Database();
            $conn = $db->getConnection();
            
            if ($conn) {
                if ($search) {
                    $services = $db->searchServices($search);
                    $pageTitle = "Search Results for: " . htmlspecialchars($search);
                } else {
                    $services = $db->getServices($category);
                    $pageTitle = $category ? ucfirst($category) . " Services" : "All Services";
                }
            }
        } catch (Exception $e) {
            error_log("Database error: " . $e->getMessage());
        }
    }
    
    // Fallback data if database fails or no results
    if (empty($services)) {
        $fallbackServices = [
            ['id' => 1, 'title' => 'DHL Express Worldwide', 'description' => 'Fast international shipping with door-to-door delivery service. Perfect for urgent documents and packages.', 'category' => 'Express', 'price_range' => '$50-200', 'delivery_time' => '1-3 days'],
            ['id' => 2, 'title' => 'DHL Express 12:00', 'description' => 'Next business day delivery by 12:00 noon. Guaranteed time-definite delivery for urgent shipments.', 'category' => 'Express', 'price_range' => '$80-300', 'delivery_time' => 'Next day by 12:00'],
            ['id' => 3, 'title' => 'DHL Freight', 'description' => 'Heavy cargo and freight solutions for businesses. Comprehensive logistics services for large shipments.', 'category' => 'Freight', 'price_range' => '$200-2000', 'delivery_time' => '3-7 days'],
            ['id' => 4, 'title' => 'DHL E-commerce', 'description' => 'Specialized e-commerce delivery solutions designed for online retailers. Seamless integration available.', 'category' => 'E-commerce', 'price_range' => '$10-50', 'delivery_time' => '2-5 days'],
            ['id' => 5, 'title' => 'DHL Same Day', 'description' => 'Urgent same-day delivery service for critical shipments. Available in major cities worldwide.', 'category' => 'Express', 'price_range' => '$100-500', 'delivery_time' => 'Same day'],
            ['id' => 6, 'title' => 'DHL Supply Chain', 'description' => 'Complete supply chain management solutions. End-to-end logistics services for businesses.', 'category' => 'Logistics', 'price_range' => 'Custom quote', 'delivery_time' => 'Varies']
        ];
        
        // Filter fallback data based on search or category
        if ($search) {
            $services = array_filter($fallbackServices, function($service) use ($search) {
                return stripos($service['title'], $search) !== false || 
                       stripos($service['description'], $search) !== false ||
                       stripos($service['category'], $search) !== false;
            });
            $pageTitle = "Search Results for: " . htmlspecialchars($search);
        } elseif ($category) {
            $services = array_filter($fallbackServices, function($service) use ($category) {
                return strcasecmp($service['category'], $category) === 0;
            });
            $pageTitle = ucfirst($category) . " Services";
        } else {
            $services = $fallbackServices;
            $pageTitle = "All Services";
        }
    }
    
    $categories = ['Express', 'Freight', 'E-commerce', 'Logistics'];
    
} catch (Exception $e) {
    error_log("Critical error in services.php: " . $e->getMessage());
    $services = [];
    $pageTitle = "Services";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - DHL</title>
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

        nav ul {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        nav a:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
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

        /* Search and Filter Section */
        .search-filter-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 3rem;
        }

        .search-form {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            padding: 1rem;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            min-width: 250px;
        }

        .search-input:focus {
            outline: none;
            border-color: #ffcc00;
            box-shadow: 0 0 10px rgba(255,204,0,0.3);
        }

        .search-button {
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .search-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255,204,0,0.4);
        }

        .category-filters {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .category-filter {
            padding: 0.8rem 1.5rem;
            border: 2px solid #ddd;
            background: white;
            color: #333;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .category-filter:hover,
        .category-filter.active {
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
        }

        /* Search Results Info */
        .search-info {
            background: #e8f4fd;
            border: 1px solid #b3d9f7;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .search-info h3 {
            color: #1a5490;
            margin-bottom: 0.5rem;
        }

        .search-info p {
            color: #666;
        }

        .clear-search {
            background: #1a5490;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.9rem;
            margin-left: 1rem;
        }

        .clear-search:hover {
            background: #0f3d6b;
        }

        /* Services Grid */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .service-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }

        .service-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            position: relative;
        }

        .service-category {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .service-content {
            padding: 1.5rem;
        }

        .service-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .service-description {
            color: #666;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .service-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .delivery-time {
            background: #e8f5e8;
            color: #2d5a2d;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .price-range {
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-weight: bold;
        }

        .service-actions {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            flex: 1;
        }

        .btn-primary {
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            color: white;
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #333;
            border: 2px solid #ddd;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .no-results-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .no-results h3 {
            font-size: 1.5rem;
            color: #666;
            margin-bottom: 1rem;
        }

        .no-results p {
            color: #888;
            margin-bottom: 2rem;
        }

        /* Footer */
        footer {
            background: #333;
            color: white;
            padding: 3rem 0 1rem;
            margin-top: 4rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            margin-bottom: 1rem;
            color: #ffcc00;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 0.5rem;
        }

        .footer-section ul li a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section ul li a:hover {
            color: #ffcc00;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #555;
            color: #ccc;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            nav ul {
                flex-wrap: wrap;
                justify-content: center;
            }

            .search-form {
                flex-direction: column;
            }

            .category-filters {
                justify-content: flex-start;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            .service-actions {
                flex-direction: column;
            }
        }

        /* Highlight search terms */
        .highlight {
            background-color: #ffff99;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo" onclick="window.location.href='index.php'">DHL</div>
                <nav>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="services.php" style="background: rgba(255,255,255,0.2);">Services</a></li>
                        <li><a href="tracking.php">Track</a></li>
                        <li><a href="news.php">News</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="page-header">
                <h1 class="page-title"><?php echo $pageTitle; ?></h1>
                <p class="page-subtitle">Discover our comprehensive range of shipping and logistics solutions designed to meet your business needs.</p>
            </div>

            <div class="search-filter-section">
                <form class="search-form" method="GET" action="services.php">
                    <input type="text" class="search-input" name="search" placeholder="Search services (e.g., express, freight, same day)..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                    <button type="submit" class="search-button">🔍 Search</button>
                </form>

                <div class="category-filters">
                    <a href="services.php" class="category-filter <?php echo !$category && !$search ? 'active' : ''; ?>">All Services</a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="services.php?category=<?php echo urlencode($cat); ?>" 
                           class="category-filter <?php echo $category === $cat ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ($search): ?>
            <div class="search-info">
                <h3>Search Results</h3>
                <p>
                    Found <?php echo count($services); ?> service(s) for "<?php echo htmlspecialchars($search); ?>"
                    <a href="services.php" class="clear-search">Clear Search</a>
                </p>
            </div>
            <?php endif; ?>

            <?php if (empty($services)): ?>
                <div class="no-results">
                    <div class="no-results-icon">🔍❌</div>
                    <h3>No services found</h3>
                    <?php if ($search): ?>
                        <p>We couldn't find any services matching "<strong><?php echo htmlspecialchars($search); ?></strong>".</p>
                        <p>Try searching for terms like: express, freight, same day, e-commerce, or logistics.</p>
                    <?php else: ?>
                        <p>We couldn't find any services in this category. Please try a different category.</p>
                    <?php endif; ?>
                    <a href="services.php" class="btn btn-primary">View All Services</a>
                </div>
            <?php else: ?>
                <div class="services-grid">
                    <?php foreach ($services as $service): ?>
                    <div class="service-card" onclick="window.location.href='service-detail.php?id=<?php echo htmlspecialchars($service['id']); ?>'">
                        <div class="service-image">
                            <span class="service-category"><?php echo htmlspecialchars($service['category']); ?></span>
                            📦
                        </div>
                        <div class="service-content">
                            <h3 class="service-title">
                                <?php 
                                $title = htmlspecialchars($service['title']);
                                if ($search) {
                                    $title = preg_replace('/(' . preg_quote($search, '/') . ')/i', '<span class="highlight">$1</span>', $title);
                                }
                                echo $title;
                                ?>
                            </h3>
                            <p class="service-description">
                                <?php 
                                $description = htmlspecialchars($service['description']);
                                if ($search) {
                                    $description = preg_replace('/(' . preg_quote($search, '/') . ')/i', '<span class="highlight">$1</span>', $description);
                                }
                                echo $description;
                                ?>
                            </p>
                            <div class="service-meta">
                                <span class="delivery-time">⏱️ <?php echo htmlspecialchars($service['delivery_time']); ?></span>
                                <span class="price-range"><?php echo htmlspecialchars($service['price_range']); ?></span>
                            </div>
                            <div class="service-actions">
                                <a href="service-detail.php?id=<?php echo htmlspecialchars($service['id']); ?>" class="btn btn-primary" onclick="event.stopPropagation();">Learn More</a>
                                <a href="contact.php?service=<?php echo htmlspecialchars($service['id']); ?>" class="btn btn-secondary" onclick="event.stopPropagation();">Get Quote</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Services</h3>
                    <ul>
                        <li><a href="services.php?category=Express">Express Delivery</a></li>
                        <li><a href="services.php?category=Freight">Freight Services</a></li>
                        <li><a href="services.php?category=E-commerce">E-commerce Solutions</a></li>
                        <li><a href="services.php?category=Logistics">Supply Chain</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Support</h3>
                    <ul>
                        <li><a href="tracking.php">Track Package</a></li>
                        <li><a href="contact.php">Customer Service</a></li>
                        <li><a href="#">Shipping Guide</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Company</h3>
                    <ul>
                        <li><a href="news.php">News & Media</a></li>
                        <li><a href="#">About DHL</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Sustainability</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Connect</h3>
                    <ul>
                        <li><a href="#">Facebook</a></li>
                        <li><a href="#">Twitter</a></li>
                        <li><a href="#">LinkedIn</a></li>
                        <li><a href="#">Instagram</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 DHL Clone. All rights reserved. | Excellence. Simply Delivered.</p>
            </div>
        </div>
    </footer>

    <script>
        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Focus search input if there's a search parameter
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('search')) {
                const searchInput = document.querySelector('.search-input');
                if (searchInput) {
                    searchInput.focus();
                }
            }
            
            // Add animation to service cards
            const cards = document.querySelectorAll('.service-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
        
        // Search suggestions
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                // You can add search suggestions here if needed
            });
        }
    </script>
</body>
</html>
