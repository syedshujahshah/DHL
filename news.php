<?php
require_once 'db.php';
$db = new Database();
$conn = $db->getConnection();

$category = isset($_GET['category']) ? $_GET['category'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : null;

if ($search) {
    // For search, we'll use a simple LIKE query on news table
    $query = "SELECT * FROM news WHERE title LIKE :search OR content LIKE :search ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $searchTerm = "%$search%";
    $stmt->bindParam(':search', $searchTerm);
    $stmt->execute();
    $news = $stmt->fetchAll();
    $pageTitle = "Search Results for: " . htmlspecialchars($search);
} else {
    $news = $db->getNews($category ? ($category === 'featured' ? true : null) : null);
    $pageTitle = $category ? ucfirst($category) . " News" : "Latest News & Updates";
}

$categories = ['featured', 'services', 'updates', 'sustainability'];
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
            text-transform: capitalize;
        }

        .category-filter:hover,
        .category-filter.active {
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
        }

        /* News Grid */
        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .news-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .news-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }

        .news-image {
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

        .news-category {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: capitalize;
        }

        .featured-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: #ff6600;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .news-content {
            padding: 1.5rem;
        }

        .news-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
            line-height: 1.4;
        }

        .news-excerpt {
            color: #666;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .news-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            color: #888;
        }

        .news-author {
            font-weight: 500;
        }

        .news-date {
            color: #999;
        }

        .read-more {
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: bold;
            transition: all 0.3s ease;
            display: inline-block;
            margin-top: 1rem;
        }

        .read-more:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255,204,0,0.4);
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
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

            .news-grid {
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
                        <li><a href="#" onclick="navigateTo('services.php')">Services</a></li>
                        <li><a href="#" onclick="navigateTo('tracking.php')">Track</a></li>
                        <li><a href="#" onclick="navigateTo('news.php')" style="background: rgba(255,255,255,0.2);">News</a></li>
                        <li><a href="#" onclick="navigateTo('contact.php')">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="page-header">
                <h1 class="page-title"><?php echo $pageTitle; ?></h1>
                <p class="page-subtitle">Stay updated with the latest news, service updates, and industry insights from DHL.</p>
            </div>

            <div class="search-filter-section">
                <form class="search-form" method="GET">
                    <input type="text" class="search-input" name="search" placeholder="Search news..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                    <button type="submit" class="search-button">Search</button>
                </form>

                <div class="category-filters">
                    <a href="news.php" class="category-filter <?php echo !$category ? 'active' : ''; ?>">All News</a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="news.php?category=<?php echo $cat; ?>" 
                           class="category-filter <?php echo $category === $cat ? 'active' : ''; ?>">
                            <?php echo $cat; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (empty($news)): ?>
                <div class="no-results">
                    <h3>No news found</h3>
                    <p>We couldn't find any news articles matching your criteria. Please try a different search term or category.</p>
                    <a href="news.php" class="read-more">View All News</a>
                </div>
            <?php else: ?>
                <div class="news-grid">
                    <?php foreach ($news as $article): ?>
                    <div class="news-card" onclick="navigateTo('news-detail.php?id=<?php echo $article['id']; ?>')">
                        <div class="news-image">
                            <?php if ($article['featured']): ?>
                                <div class="featured-badge">Featured</div>
                            <?php endif; ?>
                            <?php if ($article['category']): ?>
                                <div class="news-category"><?php echo htmlspecialchars($article['category']); ?></div>
                            <?php endif; ?>
                            📰
                        </div>
                        <div class="news-content">
                            <h3 class="news-title"><?php echo htmlspecialchars($article['title']); ?></h3>
                            <p class="news-excerpt"><?php echo htmlspecialchars(substr($article['content'], 0, 150)) . '...'; ?></p>
                            <div class="news-meta">
                                <span class="news-author">By <?php echo htmlspecialchars($article['author'] ?? 'DHL Team'); ?></span>
                                <span class="news-date"><?php echo date('M d, Y', strtotime($article['created_at'])); ?></span>
                            </div>
                            <a href="#" class="read-more" onclick="event.stopPropagation(); navigateTo('news-detail.php?id=<?php echo $article['id']; ?>')">Read More</a>
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

        // Animate news cards
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.news-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
                observer.observe(card);
            });
        });
    </script>
</body>
</html>
