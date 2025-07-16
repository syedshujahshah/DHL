<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to catch any errors
ob_start();

try {
    // Try to include database, but don't fail if it doesn't work
    $db = null;
    $featuredServices = [];
    $featuredNews = [];
    
    if (file_exists('db.php')) {
        try {
            require_once 'db.php';
            $db = new Database();
            $conn = $db->getConnection();
            
            if ($conn) {
                $featuredServices = $db->getServices(null, 6);
                $featuredNews = $db->getNews(true, 3);
            }
        } catch (Exception $e) {
            // Database failed, use fallback data
            error_log("Database error: " . $e->getMessage());
        }
    }
    
    // Fallback data if database fails
    if (empty($featuredServices)) {
        $featuredServices = [
            ['id' => 1, 'title' => 'DHL Express Worldwide', 'description' => 'Fast international shipping with door-to-door delivery', 'category' => 'Express', 'price_range' => '$50-200', 'delivery_time' => '1-3 days'],
            ['id' => 2, 'title' => 'DHL Express 12:00', 'description' => 'Next business day delivery by 12:00', 'category' => 'Express', 'price_range' => '$80-300', 'delivery_time' => 'Next day by 12:00'],
            ['id' => 3, 'title' => 'DHL Freight', 'description' => 'Heavy cargo and freight solutions', 'category' => 'Freight', 'price_range' => '$200-2000', 'delivery_time' => '3-7 days'],
            ['id' => 4, 'title' => 'DHL E-commerce', 'description' => 'Specialized e-commerce delivery solutions', 'category' => 'E-commerce', 'price_range' => '$10-50', 'delivery_time' => '2-5 days'],
            ['id' => 5, 'title' => 'DHL Same Day', 'description' => 'Urgent same-day delivery service', 'category' => 'Express', 'price_range' => '$100-500', 'delivery_time' => 'Same day'],
            ['id' => 6, 'title' => 'DHL Supply Chain', 'description' => 'Complete supply chain management', 'category' => 'Logistics', 'price_range' => 'Custom quote', 'delivery_time' => 'Varies']
        ];
    }
    
    if (empty($featuredNews)) {
        $featuredNews = [
            ['id' => 1, 'title' => 'DHL Expands Electric Vehicle Fleet', 'content' => 'DHL announces major expansion of electric delivery vehicles across major cities...', 'category' => 'Sustainability', 'author' => 'DHL Press Team'],
            ['id' => 2, 'title' => 'New Express Service to Asia', 'content' => 'Introducing faster delivery options to Asian markets...', 'category' => 'Services', 'author' => 'Operations Team'],
            ['id' => 3, 'title' => 'Holiday Shipping Guidelines', 'content' => 'Important information about shipping during the holiday season...', 'category' => 'Updates', 'author' => 'Customer Service']
        ];
    }
    
} catch (Exception $e) {
    error_log("Critical error in index.php: " . $e->getMessage());
    // Continue with fallback data
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DHL - Excellence. Simply Delivered.</title>
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
            position: sticky;
            top: 0;
            z-index: 1000;
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

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), #ffcc00;
            background-size: cover;
            background-position: center;
            height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .hero-content p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            max-width: 600px;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            color: white;
            padding: 1rem 2rem;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255,204,0,0.4);
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255,204,0,0.6);
        }

        /* Tracking Section */
        .tracking-section {
            background: white;
            padding: 3rem 0;
            margin: 2rem 0;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .tracking-form {
            display: flex;
            gap: 1rem;
            max-width: 600px;
            margin: 0 auto;
            flex-wrap: wrap;
        }

        .tracking-input {
            flex: 1;
            padding: 1rem;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            min-width: 250px;
        }

        .tracking-input:focus {
            outline: none;
            border-color: #ffcc00;
            box-shadow: 0 0 10px rgba(255,204,0,0.3);
        }

        .track-button {
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .track-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255,204,0,0.4);
        }

        /* Services Section */
        .services-section {
            padding: 4rem 0;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: #333;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            border-radius: 2px;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
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
            font-size: 0.9rem;
            color: #888;
        }

        .price-range {
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-weight: bold;
        }

        /* News Section */
        .news-section {
            background: white;
            padding: 4rem 0;
            margin: 2rem 0;
            border-radius: 15px;
        }

        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .news-card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .news-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .news-image {
            width: 100%;
            height: 180px;
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }

        .news-content {
            padding: 1.5rem;
        }

        .news-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .news-excerpt {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
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

        /* Error Message */
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
            text-align: center;
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

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .tracking-form {
                flex-direction: column;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            .section-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">DHL</div>
                <nav>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="services.php">Services</a></li>
                        <li><a href="tracking.php">Track</a></li>
                        <li><a href="news.php">News</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="hero-content">
                <h1>Excellence. Simply Delivered.</h1>
                <p>Your trusted partner for fast, reliable shipping solutions worldwide. From express delivery to freight services, we connect your business to the world.</p>
                <a href="services.php" class="cta-button">Explore Services</a>
            </div>
        </section>

        <div class="container">
            <section class="tracking-section">
                <h2 class="section-title">Track Your Shipment</h2>
                <form class="tracking-form" action="tracking.php" method="GET">
                    <input type="text" class="tracking-input" name="track" placeholder="Enter tracking number (e.g., DHL123456789)" required>
                    <button type="submit" class="track-button">Track Package</button>
                </form>
            </section>

            <section class="services-section">
                <h2 class="section-title">Our Services</h2>
                <div class="services-grid">
                    <?php foreach ($featuredServices as $service): ?>
                    <div class="service-card" onclick="window.location.href='service-detail.php?id=<?php echo htmlspecialchars($service['id']); ?>'">
                        <div class="service-image">📦</div>
                        <div class="service-content">
                            <h3 class="service-title"><?php echo htmlspecialchars($service['title']); ?></h3>
                            <p class="service-description"><?php echo htmlspecialchars(substr($service['description'], 0, 100)) . '...'; ?></p>
                            <div class="service-meta">
                                <span><?php echo htmlspecialchars($service['delivery_time']); ?></span>
                                <span class="price-range"><?php echo htmlspecialchars($service['price_range']); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <?php if (!empty($featuredNews)): ?>
            <section class="news-section">
                <h2 class="section-title">Latest News & Updates</h2>
                <div class="news-grid">
                    <?php foreach ($featuredNews as $news): ?>
                    <div class="news-card" onclick="window.location.href='news-detail.php?id=<?php echo htmlspecialchars($news['id']); ?>'">
                        <div class="news-image">📰</div>
                        <div class="news-content">
                            <h3 class="news-title"><?php echo htmlspecialchars($news['title']); ?></h3>
                            <p class="news-excerpt"><?php echo htmlspecialchars(substr($news['content'], 0, 120)) . '...'; ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
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
</body>
</html>

<?php
// End output buffering and flush
ob_end_flush();
?>
