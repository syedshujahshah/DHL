<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$success_message = '';
$error_message = '';
$service_id = isset($_GET['service']) ? (int)$_GET['service'] : null;
$selected_service = '';

// Get service details if service ID is provided
if ($service_id) {
    try {
        if (file_exists('db.php')) {
            require_once 'db.php';
            $db = new Database();
            $conn = $db->getConnection();
            
            if ($conn) {
                $service = $db->getServiceById($service_id);
                if ($service) {
                    $selected_service = $service['title'];
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching service: " . $e->getMessage());
    }
}

// Handle form submission
if ($_POST) {
    try {
        // Sanitize input data
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $service = trim($_POST['service'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $inquiry_type = trim($_POST['inquiry_type'] ?? '');
        
        // Basic validation
        $errors = [];
        
        if (empty($name)) {
            $errors[] = "Name is required";
        }
        
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address";
        }
        
        if (empty($subject)) {
            $errors[] = "Subject is required";
        }
        
        if (empty($message)) {
            $errors[] = "Message is required";
        }
        
        if (empty($errors)) {
            // Here you would typically save to database or send email
            // For now, we'll just show a success message
            
            // You can add email sending functionality here
            /*
            $to = "support@dhl.com";
            $email_subject = "Contact Form: " . $subject;
            $email_body = "Name: $name\nEmail: $email\nPhone: $phone\nService: $service\nInquiry Type: $inquiry_type\n\nMessage:\n$message";
            $headers = "From: $email\r\nReply-To: $email\r\n";
            
            if (mail($to, $email_subject, $email_body, $headers)) {
                $success_message = "Thank you for contacting us! We'll get back to you within 24 hours.";
            } else {
                $error_message = "Sorry, there was an error sending your message. Please try again.";
            }
            */
            
            // For demo purposes, always show success
            $success_message = "Thank you for contacting us, " . htmlspecialchars($name) . "! We'll get back to you within 24 hours.";
            
            // Clear form data after successful submission
            if ($success_message) {
                $name = $email = $phone = $service = $subject = $message = $inquiry_type = '';
            }
            
        } else {
            $error_message = implode('<br>', $errors);
        }
        
    } catch (Exception $e) {
        error_log("Contact form error: " . $e->getMessage());
        $error_message = "Sorry, there was an error processing your request. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - DHL</title>
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

        /* Contact Layout */
        .contact-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        /* Contact Form */
        .contact-form-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .form-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #ffcc00;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #555;
        }

        .required {
            color: #dc3545;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        select,
        textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #ffcc00;
            box-shadow: 0 0 10px rgba(255,204,0,0.3);
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        select {
            cursor: pointer;
        }

        .submit-btn {
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255,204,0,0.4);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Contact Info Sidebar */
        .contact-info-section {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .info-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .info-card h3 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: 0.8rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .contact-item:last-child {
            margin-bottom: 0;
        }

        .contact-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(45deg, #ffcc00, #ff6600);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .contact-details h4 {
            color: #333;
            margin-bottom: 0.3rem;
            font-size: 1rem;
        }

        .contact-details p {
            color: #666;
            font-size: 0.9rem;
        }

        /* Office Hours */
        .hours-list {
            list-style: none;
        }

        .hours-list li {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .hours-list li:last-child {
            border-bottom: none;
        }

        .day {
            font-weight: bold;
            color: #333;
        }

        .time {
            color: #666;
        }

        /* Quick Links */
        .quick-links {
            background: linear-gradient(135deg, #ffcc00 0%, #ff6600 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
        }

        .quick-links h3 {
            margin-bottom: 1rem;
        }

        .quick-links ul {
            list-style: none;
        }

        .quick-links li {
            margin-bottom: 0.8rem;
        }

        .quick-links a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .quick-links a:hover {
            background: rgba(255,255,255,0.2);
            transform: translateX(5px);
        }

        /* Messages */
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border: 1px solid #f5c6cb;
        }

        /* FAQ Section */
        .faq-section {
            background: white;
            padding: 3rem 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 3rem;
        }

        .faq-title {
            text-align: center;
            font-size: 2rem;
            color: #333;
            margin-bottom: 2rem;
        }

        .faq-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .faq-item {
            padding: 1.5rem;
            border: 1px solid #eee;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .faq-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .faq-question {
            font-weight: bold;
            color: #333;
            margin-bottom: 0.8rem;
            font-size: 1.1rem;
        }

        .faq-answer {
            color: #666;
            line-height: 1.6;
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

            .contact-layout {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .faq-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #ffcc00;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .hidden {
            display: none;
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
                        <li><a href="services.php">Services</a></li>
                        <li><a href="tracking.php">Track</a></li>
                        <li><a href="news.php">News</a></li>
                        <li><a href="contact.php" style="background: rgba(255,255,255,0.2);">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Contact Us</h1>
                <p class="page-subtitle">Get in touch with our team for any questions, support, or service inquiries. We're here to help you with all your shipping needs.</p>
            </div>

            <?php if ($success_message): ?>
                <div class="success-message">
                    <strong>✅ Success!</strong> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="error-message">
                    <strong>❌ Error!</strong> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="contact-layout">
                <div class="contact-form-section">
                    <h2 class="form-title">Send us a Message</h2>
                    <form method="POST" id="contactForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name <span class="required">*</span></label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address <span class="required">*</span></label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="inquiry_type">Inquiry Type</label>
                                <select id="inquiry_type" name="inquiry_type">
                                    <option value="">Select inquiry type</option>
                                    <option value="general" <?php echo ($inquiry_type ?? '') === 'general' ? 'selected' : ''; ?>>General Inquiry</option>
                                    <option value="quote" <?php echo ($inquiry_type ?? '') === 'quote' ? 'selected' : ''; ?>>Request Quote</option>
                                    <option value="support" <?php echo ($inquiry_type ?? '') === 'support' ? 'selected' : ''; ?>>Customer Support</option>
                                    <option value="complaint" <?php echo ($inquiry_type ?? '') === 'complaint' ? 'selected' : ''; ?>>Complaint</option>
                                    <option value="partnership" <?php echo ($inquiry_type ?? '') === 'partnership' ? 'selected' : ''; ?>>Partnership</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="service">Service Interest</label>
                            <select id="service" name="service">
                                <option value="">Select a service (optional)</option>
                                <option value="DHL Express Worldwide" <?php echo ($service ?? '') === 'DHL Express Worldwide' || $selected_service === 'DHL Express Worldwide' ? 'selected' : ''; ?>>DHL Express Worldwide</option>
                                <option value="DHL Express 12:00" <?php echo ($service ?? '') === 'DHL Express 12:00' || $selected_service === 'DHL Express 12:00' ? 'selected' : ''; ?>>DHL Express 12:00</option>
                                <option value="DHL Freight" <?php echo ($service ?? '') === 'DHL Freight' || $selected_service === 'DHL Freight' ? 'selected' : ''; ?>>DHL Freight</option>
                                <option value="DHL E-commerce" <?php echo ($service ?? '') === 'DHL E-commerce' || $selected_service === 'DHL E-commerce' ? 'selected' : ''; ?>>DHL E-commerce</option>
                                <option value="DHL Same Day" <?php echo ($service ?? '') === 'DHL Same Day' || $selected_service === 'DHL Same Day' ? 'selected' : ''; ?>>DHL Same Day</option>
                                <option value="DHL Supply Chain" <?php echo ($service ?? '') === 'DHL Supply Chain' || $selected_service === 'DHL Supply Chain' ? 'selected' : ''; ?>>DHL Supply Chain</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="subject">Subject <span class="required">*</span></label>
                            <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($subject ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="message">Message <span class="required">*</span></label>
                            <textarea id="message" name="message" placeholder="Please provide details about your inquiry..." required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="submit-btn" id="submitBtn">
                            <span id="submitText">Send Message</span>
                            <span id="submitLoading" class="loading hidden"></span>
                        </button>
                    </form>
                </div>

                <div class="contact-info-section">
                    <div class="info-card">
                        <h3>Contact Information</h3>
                        <div class="contact-item">
                            <div class="contact-icon">📞</div>
                            <div class="contact-details">
                                <h4>Phone</h4>
                                <p>+1 (800) DHL-SHIP<br>+1 (800) 345-7447</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">✉️</div>
                            <div class="contact-details">
                                <h4>Email</h4>
                                <p>support@dhl.com<br>info@dhl.com</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">📍</div>
                            <div class="contact-details">
                                <h4>Address</h4>
                                <p>DHL Express USA<br>1200 South Pine Island Rd<br>Plantation, FL 33324</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">💬</div>
                            <div class="contact-details">
                                <h4>Live Chat</h4>
                                <p>Available 24/7<br>Click to start chat</p>
                            </div>
                        </div>
                    </div>

                    <div class="info-card">
                        <h3>Office Hours</h3>
                        <ul class="hours-list">
                            <li><span class="day">Monday - Friday</span><span class="time">8:00 AM - 8:00 PM</span></li>
                            <li><span class="day">Saturday</span><span class="time">9:00 AM - 5:00 PM</span></li>
                            <li><span class="day">Sunday</span><span class="time">10:00 AM - 4:00 PM</span></li>
                        </ul>
                        <p style="margin-top: 1rem; color: #666; font-size: 0.9rem;">
                            <strong>Emergency Support:</strong> Available 24/7 for urgent shipment issues.
                        </p>
                    </div>

                    <div class="quick-links">
                        <h3>Quick Links</h3>
                        <ul>
                            <li><a href="tracking.php">📦 Track Package</a></li>
                            <li><a href="services.php">🚚 Our Services</a></li>
                            <li><a href="services.php?search=quote">💰 Get Quote</a></li>
                            <li><a href="news.php">📰 Latest News</a></li>
                            <li><a href="#">❓ FAQ</a></li>
                            <li><a href="#">📋 Shipping Guide</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="faq-section">
                <h2 class="faq-title">Frequently Asked Questions</h2>
                <div class="faq-grid">
                    <div class="faq-item">
                        <div class="faq-question">How can I track my package?</div>
                        <div class="faq-answer">You can track your package using our tracking tool on the homepage or tracking page. Just enter your tracking number to get real-time updates.</div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">What are your delivery times?</div>
                        <div class="faq-answer">Delivery times vary by service type. Express services deliver in 1-3 days, while standard services take 3-7 days depending on destination.</div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">How do I get a shipping quote?</div>
                        <div class="faq-answer">You can get a quote by contacting us through this form, calling our customer service, or visiting our services page for pricing information.</div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">Do you offer international shipping?</div>
                        <div class="faq-answer">Yes, we offer international shipping to over 220 countries and territories worldwide with our DHL Express Worldwide service.</div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">What if my package is damaged?</div>
                        <div class="faq-answer">If your package arrives damaged, please contact us immediately. We offer insurance coverage and will work to resolve the issue quickly.</div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">Can I schedule a pickup?</div>
                        <div class="faq-answer">Yes, you can schedule a pickup through our website, mobile app, or by calling customer service. We offer flexible pickup times to suit your needs.</div>
                    </div>
                </div>
            </div>
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
        // Form submission with loading state
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitLoading = document.getElementById('submitLoading');
            
            // Show loading state
            submitBtn.disabled = true;
            submitText.classList.add('hidden');
            submitLoading.classList.remove('hidden');
            
            // Note: In a real implementation, you might want to prevent the default
            // form submission and handle it with AJAX for a better user experience
        });

        // Auto-fill service if coming from service page
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const serviceParam = urlParams.get('service');
            
            if (serviceParam) {
                const serviceSelect = document.getElementById('service');
                // Try to match the service ID with the select options
                for (let option of serviceSelect.options) {
                    if (option.value.includes('Express') && serviceParam.includes('1')) {
                        option.selected = true;
                        break;
                    } else if (option.value.includes('Freight') && serviceParam.includes('3')) {
                        option.selected = true;
                        break;
                    }
                    // Add more matching logic as needed
                }
            }
        });

        // Form validation
        function validateForm() {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const subject = document.getElementById('subject').value.trim();
            const message = document.getElementById('message').value.trim();
            
            if (!name || !email || !subject || !message) {
                alert('Please fill in all required fields.');
                return false;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address.');
                return false;
            }
            
            return true;
        }

        // Add real-time validation
        document.querySelectorAll('input[required], textarea[required]').forEach(field => {
            field.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    this.style.borderColor = '#dc3545';
                } else {
                    this.style.borderColor = '#28a745';
                }
            });
            
            field.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.style.borderColor = '#ddd';
                }
            });
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
