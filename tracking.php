<?php
require_once 'db.php';
$db = new Database();
$conn = $db->getConnection();

$trackingNumber = isset($_GET['track']) ? $_GET['track'] : '';
$shipmentData = null;
$trackingHistory = [];

if ($trackingNumber) {
    $shipmentData = $db->trackShipment($trackingNumber);
    if ($shipmentData && $shipmentData['tracking_history']) {
        $historyItems = explode(';;', $shipmentData['tracking_history']);
        foreach ($historyItems as $item) {
            if ($item) {
                $parts = explode('|', $item);
                if (count($parts) >= 4) {
                    $trackingHistory[] = [
                        'timestamp' => $parts[0],
                        'status' => $parts[1],
                        'location' => $parts[2],
                        'description' => $parts[3]
                    ];
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Shipment - DHL</title>
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

        /* Tracking Form */
        .tracking-section {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 3rem;
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

        /* Shipment Details */
        .shipment-details {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 3rem;
        }

        .shipment-header {
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .tracking-number {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .shipment-status {
            font-size: 1.2rem;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.2);
            border-radius: 25px;
            display: inline-block;
        }

        .shipment-info {
            padding: 2rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .info-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 4px solid #ffcc00;
        }

        .info-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-content {
            color: #666;
            font-size: 1rem;
        }

        /* Tracking Timeline */
        .tracking-timeline {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 2rem;
        }

        .timeline-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 2rem;
            text-align: center;
        }

        .timeline {
            position: relative;
            padding-left: 2rem;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 1rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #ddd;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-left: 1rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -2.5rem;
            top: 1.5rem;
            width: 1rem;
            height: 1rem;
            background: #ffcc00;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 0 3px #ffcc00;
        }

        .timeline-item.current::before {
            background: #ff6600;
            box-shadow: 0 0 0 3px #ff6600;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 3px #ff6600;
            }
            50% {
                box-shadow: 0 0 0 8px rgba(255,102,0,0.3);
            }
            100% {
                box-shadow: 0 0 0 3px #ff6600;
            }
        }

        .timeline-status {
            font-weight: bold;
            color: #333;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .timeline-location {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .timeline-description {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .timeline-date {
            color: #999;
            font-size: 0.8rem;
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

        /* Sample Tracking Numbers */
        .sample-numbers {
            background: #e8f4fd;
            border: 1px solid #b3d9f7;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .sample-numbers h4 {
            color: #1a5490;
            margin-bottom: 1rem;
        }

        .sample-list {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .sample-number {
            background: white;
            color: #1a5490;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid #b3d9f7;
        }

        .sample-number:hover {
            background: #1a5490;
            color: white;
            transform: translateY(-2px);
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

            .tracking-form {
                flex-direction: column;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .timeline {
                padding-left: 1rem;
            }

            .timeline-item {
                margin-left: 0.5rem;
            }

            .timeline-item::before {
                left: -1.5rem;
            }

            .sample-list {
                flex-direction: column;
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
                        <li><a href="#" onclick="navigateTo('services.php')">Services</a></li>
                        <li><a href="#" onclick="navigateTo('tracking.php')" style="background: rgba(255,255,255,0.2);">Track</a></li>
                        <li>  style="background: rgba(255,255,255,0.2);">Track</a></li>
                        <li><a href="#" onclick="navigateTo('news.php')">News</a></li>
                        <li><a href="#" onclick="navigateTo('contact.php')">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Track Your Shipment</h1>
                <p class="page-subtitle">Enter your tracking number to get real-time updates on your package delivery status.</p>
            </div>

            <div class="tracking-section">
                <form class="tracking-form" method="GET">
                    <input type="text" class="tracking-input" name="track" placeholder="Enter tracking number (e.g., DHL123456789)" value="<?php echo htmlspecialchars($trackingNumber); ?>" required>
                    <button type="submit" class="track-button">Track Package</button>
                </form>

                <?php if (!$trackingNumber): ?>
                <div class="sample-numbers">
                    <h4>Try these sample tracking numbers:</h4>
                    <div class="sample-list">
                        <span class="sample-number" onclick="trackSample('DHL123456789')">DHL123456789</span>
                        <span class="sample-number" onclick="trackSample('DHL987654321')">DHL987654321</span>
                        <span class="sample-number" onclick="trackSample('DHL456789123')">DHL456789123</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($trackingNumber && !$shipmentData): ?>
                <div class="no-results">
                    <div class="no-results-icon">📦❌</div>
                    <h3>Tracking number not found</h3>
                    <p>We couldn't find any shipment with tracking number: <strong><?php echo htmlspecialchars($trackingNumber); ?></strong></p>
                    <p>Please check your tracking number and try again, or contact customer service for assistance.</p>
                </div>
            <?php elseif ($shipmentData): ?>
                <div class="shipment-details">
                    <div class="shipment-header">
                        <div class="tracking-number">Tracking #<?php echo htmlspecialchars($shipmentData['tracking_number']); ?></div>
                        <div class="shipment-status"><?php echo htmlspecialchars($shipmentData['status']); ?></div>
                    </div>
                    <div class="shipment-info">
                        <div class="info-grid">
                            <div class="info-card">
                                <div class="info-title">Service Type</div>
                                <div class="info-content"><?php echo htmlspecialchars($shipmentData['service_type']); ?></div>
                            </div>
                            <div class="info-card">
                                <div class="info-title">Weight</div>
                                <div class="info-content"><?php echo htmlspecialchars($shipmentData['weight']); ?> kg</div>
                            </div>
                            <div class="info-card">
                                <div class="info-title">From</div>
                                <div class="info-content">
                                    <?php echo htmlspecialchars($shipmentData['sender_name']); ?><br>
                                    <?php echo htmlspecialchars($shipmentData['sender_address']); ?>
                                </div>
                            </div>
                            <div class="info-card">
                                <div class="info-title">To</div>
                                <div class="info-content">
                                    <?php echo htmlspecialchars($shipmentData['receiver_name']); ?><br>
                                    <?php echo htmlspecialchars($shipmentData['receiver_address']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($trackingHistory)): ?>
                <div class="tracking-timeline">
                    <h2 class="timeline-title">Tracking History</h2>
                    <div class="timeline">
                        <?php foreach ($trackingHistory as $index => $history): ?>
                        <div class="timeline-item <?php echo $index === 0 ? 'current' : ''; ?>">
                            <div class="timeline-status"><?php echo htmlspecialchars($history['status']); ?></div>
                            <div class="timeline-location">📍 <?php echo htmlspecialchars($history['location']); ?></div>
                            <div class="timeline-description"><?php echo htmlspecialchars($history['description']); ?></div>
                            <div class="timeline-date"><?php echo date('M d, Y - H:i', strtotime($history['timestamp'])); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
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

        function trackSample(trackingNumber) {
            window.location.href = `tracking.php?track=${trackingNumber}`;
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

        // Animate timeline items
        document.addEventListener('DOMContentLoaded', () => {
            const timelineItems = document.querySelectorAll('.timeline-item');
            timelineItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateX(-20px)';
                item.style.transition = `opacity 0.6s ease ${index * 0.2}s, transform 0.6s ease ${index * 0.2}s`;
                observer.observe(item);
            });
        });
    </script>
</body>
</html>
