<?php
require_once 'db.php';
$db = new Database();
$conn = $db->getConnection();

$newsId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$article = null;

if ($newsId) {
    $article = $db->getNewsById($newsId);
}

if (!$article) {
    header('Location: news.php');
    exit;
}

// Get related articles
$relatedQuery = "SELECT * FROM news WHERE id != :id AND category = :category ORDER BY created_at DESC LIMIT 3";
$stmt = $conn->prepare($relatedQuery);
$stmt->bindParam(':id', $newsId);
$stmt->bindParam(':category', $article['category']);
$stmt->execute();
$relatedArticles = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - DHL News</title>
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

        /* Article Layout */
        .article-layout {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 3rem;
            margin: 2rem 0;
        }

        .article-main {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .article-sidebar {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        /* Article Header */
        .article-header {
            padding: 2rem;
            border-bottom: 1px solid #eee;
        }

        .article-category {
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            color: white;
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 1rem;
            text-transform: capitalize;
        }

        .article-title {
            font-size: 2.2rem;
            color: #333;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .article-meta {
            display: flex;
            gap: 2rem;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .article-excerpt {
            font-size: 1.1rem;
            color: #666;
            font-style: italic;
            line-height: 1.6;
        }

        /* Article Image */
        .article-image {
            width: 100%;
            height: 300px;
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 4rem;
        }

        /* Article Content */
        .article-content {
            padding: 2rem;
        }

        .article-content h2 {
            color: #333;
            margin: 2rem 0 1rem;
            font-size: 1.5rem;
        }

        .article-content h3 {
            color: #333;
            margin: 1.5rem 0 0.8rem;
            font-size: 1.3rem;
        }

        .article-content p {
            margin-bottom: 1.5rem;
            line-height: 1.8;
            color: #555;
        }

        .article-content ul,
        .article-content ol {
            margin: 1rem 0 1.5rem 2rem;
        }

        .article-content li {
            margin-bottom: 0.5rem;
            line-height: 1.6;
        }

        .article-content blockquote {
            background: #f8f9fa;
            border-left: 4px solid #ffcc00;
            padding: 1rem 1.5rem;
            margin: 1.5rem 0;
            font-style: italic;
            color: #666;
        }

        /* Sidebar Components */
        .sidebar-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }

        .sidebar-title {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #ffcc00;
        }

        /* Share Buttons */
        .share-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .share-btn {
            padding: 0.8rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
            flex: 1;
            text-align: center;
            min-width: 60px;
        }

        .share-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .share-facebook { background: #3b5998; }
        .share-twitter { background: #1da1f2; }
        .share-linkedin { background: #0077b5; }
        .share-email { background: #666; }

        /* Related Articles */
        .related-article {
            display: flex;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .related-article:hover {
            background: #f8f9fa;
            transform: translateX(5px);
        }

        .related-article:last-child {
            border-bottom: none;
        }

        .related-image {
            width: 80px;
            height: 60px;
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .related-content h4 {
            font-size: 0.9rem;
            color: #333;
            margin-bottom: 0.3rem;
            line-height: 1.3;
        }

        .related-date {
            font-size: 0.8rem;
            color: #999;
        }

        /* Newsletter Signup */
        .newsletter-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .newsletter-input {
            padding: 0.8rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .newsletter-input:focus {
            outline: none;
            border-color: #ffcc00;
            box-shadow: 0 0 10px rgba(255,204,0,0.3);
        }

        .newsletter-btn {
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            color: white;
            border: none;
            padding: 0.8rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .newsletter-btn:hover {
            transform: translateY(-2px);
            box-
