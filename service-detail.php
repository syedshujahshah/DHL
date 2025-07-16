<?php
require_once 'db.php';
$db = new Database();
$conn = $db->getConnection();

$serviceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$service = null;

if ($serviceId) {
    $service = $db->getServiceById($serviceId);
}

if (!$service) {
    header('Location: services.php');
    exit;
}

// Get related services
$relatedServices = $db->getServices($service['category'], 3);
$relatedServices = array_filter($relatedServices, function($s) use ($serviceId) {
    return $s['id'] != $serviceId;
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($service['title']); ?> - DHL</title>
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

        /* Breadcrumb */
        .breadcrumb {
            background: white;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .breadcrumb-list {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            list-style: none;
        }

        .breadcrumb-item {
            color: #666;
        }

        .breadcrumb-item a {
            color: #666;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumb-item a:hover {
            color: #ffcc00;
        }

        .breadcrumb-separator {
            color: #ccc;
        }

        /* Service Hero */
        .service-hero {
            background: white;
            padding: 3rem 0;
            margin-bottom: 3rem;
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 3rem;
            align-items: center;
        }

        .hero-text h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 1rem;
        }

        .hero-text .service-category {
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            color: white;
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .hero-text p {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.8;
        }

        .hero-meta {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .meta-item {
            text-align: center;
        }

        .meta-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #ffcc00;
            display: block;
        }

        .meta-label {
            font-size: 0.9rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .hero-image {
            width: 100%;
            height: 300px;
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 4rem;
            box-shadow: 0 10px 30px rgba(255,204,0,0.3);
        }

        /* Service Details */
        .service-details {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .details-content {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .details-sidebar {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: fit-content;
        }

        .section-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #ffcc00;
        }

        .feature-list {
            list-style: none;
            margin-bottom: 2rem;
        }

        .feature-list li {
            padding: 0.8rem 0;
            border-bottom: 1px solid #eee;
            position: relative;
            padding-left: 2rem;
        }

        .feature-list li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #28a745;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .feature-list li:last-child {
            border-bottom: none;
        }

        /* Pricing Card */
        .pricing-card {
            background: linear-gradient(135deg, #ffcc00 0%, #ff6600 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 2rem;
        }

        .price-range {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .price-note {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 1.5rem;
        }

        .cta-button {
            background: white;
            color: #ff6600;
            padding: 1rem 2rem;
            border: none;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            width: 100%;
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        /* Contact Info */
        .contact-info {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .contact-info h4 {
            color: #333;
            margin-bottom: 1rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.8rem;
            color: #666;
        }

        .contact-item:last-child {
            margin-bottom: 0;
        }

        /* Related Services */
        .related-services {
            background: white;
            padding: 3rem 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .related-card {
            border: 1px solid #eee;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .related-image {
            width: 100%;
            height: 150px;
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }

        .related-content {
            padding: 1.5rem;
        }

        .related-title {
            font-size: 1.1rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .related-description {
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

            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .service-details {
                grid-template-columns: 1fr;
            }

            .hero-meta {
                justify-content: center;
            }

            .related-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo" onclick="navigateTo('index.php')">DHL</div>
                <nav>
                    <ul>
                        <li><a href="#" onclick="navigateTo('index.php')">Home</a></li>
                        <li><a href="#" onclick="navigateTo('services.php')" style="background: rgba(255,255,255,0.2);">Services</a></li>
                        <li><a href="#" onclick="navigateTo('tracking.php')">Track</a></li>
                        <li><a href="#" onclick="navigateTo('news.php')">News</a></li>
                        <li><a href="#" onclick="navigateTo('contact.php')">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <div class="breadcrumb">
        <div class="container">
            <ul class="breadcrumb-list">
                <li class="breadcrumb-item"><a href="#" onclick="navigateTo('index.php')">Home</a></li>
                <li class="breadcrumb-separator">›</li>
                <li class="breadcrumb-item"><a href="#" onclick="navigateTo('services.php')">Services</a></li>
                <li class="breadcrumb-separator">›</li>
                <li class="breadcrumb-item"><a href="#" onclick="navigateTo('services.php?category=<?php echo urlencode($service['category']); ?>')"><?php echo htmlspecialchars($service['category']); ?></a></li>
                <li class="breadcrumb-separator">›</li>
                <li class="breadcrumb-item"><?php echo htmlspecialchars($service['title']); ?></li>
            </ul>
        </div>
    </div>

    <main>
        <div class="container">
            <section class="service-hero">
                <div class="hero-content">
                    <div class="hero-text">
                        <div class="service-category"><?php echo htmlspecialchars($service['category']); ?></div>
                        <h1><?php echo htmlspecialchars($service['title']); ?></h1>
                        <p><?php echo htmlspecialchars($service['description']); ?></p>
                        <div class="hero-meta">
                            <div class="meta-item">
                                <span class="meta-value"><?php echo htmlspecialchars($service['delivery_time']); ?></span>
                                <span class="meta-label">Delivery Time</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-value"><?php echo htmlspecialchars($service['price_range']); ?></span>
                                <span class="meta-label">Price Range</span>
                            </div>
                        </div>
                    </div>
                    <div class="hero-image">📦</div>
                </div>
            </section>

            <section class="service-details">
                <div class="details-content">
                    <h2 class="section-title">Service Features</h2>
                    <ul class="feature-list">
                        <li>Door-to-door delivery service</li>
                        <li>Real-time tracking and updates</li>
                        <li>Insurance coverage included</li>
                        <li>Professional handling and care</li>
                        <li>Flexible pickup and delivery options</li>
                        <li>24/7 customer support</li>
                        <li>Customs clearance assistance</li>
                        <li>Proof of delivery confirmation</li>
                    </ul>

                    <h3 class="section-title">How It Works</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
                        <div style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 10px;">
                            <div style="font-size: 2rem; margin-bottom: 1rem;">📋</div>
                            <h4 style="margin-bottom: 0.5rem;">1. Book Online</h4>
                            <p style="color: #666; font-size: 0.9rem;">Schedule your pickup and provide shipment details</p>
                        </div>
                        <div style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 10px;">
                            <div style="font-size: 2rem; margin-bottom: 1rem;">📦</div>
                            <h4 style="margin-bottom: 0.5rem;">2. We Collect</h4>
                            <p style="color: #666; font-size: 0.9rem;">Our team picks up your package from your location</p>
                        </div>
                        <div style="text-align: center; padding: 1.5rem; background: #f8f9fa; border-radius: 10px;">
                            <div style="font-size: 2rem; margin-bottom: 1rem;">🚚</div>
                            <h4 style="margin-bottom: 0.5rem;">3. We Deliver</h4>
                            <p style="color: #666; font-size: 0.9rem;">Fast and secure delivery to the destination</p>
                        </div>
                    </div>
                </div>

                <div class="details-sidebar">
                    <div class="pricing-card">
                        <div class="price-range"><?php echo htmlspecialchars($service['price_range']); ?></div>
                        <div class="price-note">Starting price - final cost depends on weight, dimensions, and destination</div>
                        <a href="#" class="cta-button" onclick="navigateTo('contact.php?service=<?php echo $service['id']; ?>')">Get Quote</a>
                    </div>

                    <div class="contact-info">
                        <h4>Need Help?</h4>
                        <div class="contact-item">
                            <span>📞</span>
                            <span>+1 (800) DHL-SHIP</span>
                        </div>
                        <div class="contact-item">
                            <span>✉️</span>
                            <span>support@dhl.com</span>
                        </div>
                        <div class="contact-item">
                            <span>💬</span>
                            <span>Live Chat Available</span>
                        </div>
                        <div class="contact-item">
                            <span>🕒</span>
                            <span>24/7 Customer Support</span>
                        </div>
                    </div>

                    <div style="background: #e8f4fd; padding: 1.5rem; border-radius: 10px; text-align: center;">
                        <h4 style="color: #1a5490; margin-bottom: 1rem;">Track Your Package</h4>
                        <p style="color: #666; font-size: 0.9rem; margin-bottom: 1rem;">Already shipped with us? Track your package here.</p>
                        <a href="#" class="cta-button" onclick="navigateTo('tracking.php')" style="background: #1a5490; color: white;">Track Now</a>
                    </div>
                </div>
            </section>

            <?php if (!empty($relatedServices)): ?>
            <section class="related-services">
                <h2 class="section-title">Related Services</h2>
                <div class="related-grid">
                    <?php foreach ($relatedServices as $related): ?>
                    <div class="related-card" onclick="navigateTo('service-detail.php?id=<?php echo $related['id']; ?>')">
                        <div class="related-image">📦</div>
                        <div class="related-content">
                            <h3 class="related-title"><?php echo htmlspecialchars($related['title']); ?></h3>
                            <p class="related-description"><?php echo htmlspecialchars(substr($related['description'], 0, 100)) . '...'; ?></p>
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
                        <li><a href="#" onclick="navigateTo('services.php?category=Express')">Express Delivery</a></li>
                        <li><a href="#" onclick="navigateTo('services.php?category=Freight')">Freight Services</a></li>
                        <li><a href="#" onclick="navigateTo('services.php?category=E-commerce')">E-commerce Solutions</a></li>
                        <li><a href="#" onclick="navigateTo('services.php?category=Logistics')">Supply Chain</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Support</h3>
                    <ul>
                        <li><a href="#" onclick="navigateTo('tracking.php')">Track Package</a></li>
                        <li><a href="#" onclick="navigateTo('contact.php')">Customer Service</a></li>
                        <li><a href="#">Shipping Guide</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Company</h3>
                    <ul>
                        <li><a href="#" onclick="navigateTo('news.php')">News & Media</a></li>
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
        function navigateTo(url) {
            window.location.href = url;
        }

        // Add animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Animate elements on scroll
        document.addEventListener('DOMContentLoaded', () => {
            const animatedElements = document.querySelectorAll('.related-card, .feature-list li');
            animatedElements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                element.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
                observer.observe(element);
            });
        });
    </script>
</body>
</html>
