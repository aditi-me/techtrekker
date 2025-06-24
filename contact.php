<?php
include 'header.php'; // Includes your header
require 'db.php';     // Connects to your database

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_message'])) {
    $name = mysqli_real_escape_string($connection, $_POST['name']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $message = mysqli_real_escape_string($connection, $_POST['message']);

    // Insert data into 'messages' table
    $sql = "INSERT INTO enquires (name, email, message) VALUES ('$name', '$email', '$message')";

    if (mysqli_query($connection, $sql)) {
        echo "<script>alert('Your message has been sent successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($connection) . "');</script>"; // Corrected $conn to $connection
    }
}
?>

<head>
    <title>Contact Us - TechTrekker</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* General Body and Container Styles */
        body {
            background-color: #f9fafb;
            margin: 0; /* Ensure no default body margin */
            padding: 0; /* Ensure no default body padding */
        }

        .all-together {
            width: 100%;
            margin: 0 auto;
        }

        /* 1. Banner Section */
        .banner-section {
            background-color: #1e3a8a;
            color: white;
            padding: 100px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-sizing: border-box; /* Include padding in element's total width and height */
        }

        .banner-section h1 {
            font-size: 3em;
            margin-bottom: 15px;
        }

        .banner-section p {
            width: 400px;
            margin: 0 auto;
            max-width: 90%; /* Ensure paragraph doesn't overflow on smaller screens */
        }

        /* 2. Absolute Image */
        .banner-image {
            position: absolute;
            left: 50px;
            top: 80px;
            width: 450px;
            height: auto;
            z-index: 1;
        }

        .banner-image img {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 10px;
        }

        /* 3. Contact Information Section */
        .contact-details {
            background-color: #f8f8f8;
            padding: 30px;
            width: 100%;
            margin: 30px auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-sizing: border-box;
        }

        .contact-details h2 {
            color: #1e3a8a;
            font-size: 2em;
            margin-bottom: 15px;
        }

        .contact-details p {
            margin-bottom: 10px;
            font-size: 1.1em;
            display: flex;
            align-items: center; /* Align icon and text vertically */
            justify-content: center; /* Center content horizontally */
            text-align: center; /* Fallback for text alignment */
        }

        .contact-details p i {
            margin-right: 10px;
            color: #1e3a8a;
            font-size: 1.2em;
        }

        /* 4. Our Story and Get in Touch Section */
        .story-and-form-section {
            width: 90%;
            margin: 100px auto 0px auto;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            padding: 50px 20px;
            gap: 40px;
            box-sizing: border-box;
        }

        .our-story-social {
            flex: 2;
            min-width: 300px;
        }

        .our-story-social h2,
        .our-story-social h3 {
            color: #1e3a8a;
            margin-bottom: 15px;
        }

        .our-story-social p {
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .instagram-feed-placeholder {
            width: 100%; /* Ensure it takes full width of its container */
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
            background-color: #e6e6e6;
            padding: 20px;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .insta-post {
            text-align: center;
            background-color: white;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .insta-post img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .insta-post p {
            font-size: 0.9em;
            color: #555;
            margin: 0;
        }

        .social-media-icons {
            margin-top: 20px;
            text-align: left; /* Center icons on smaller screens */
        }

        .social-media-icons a {
            color: #1e3a8a;
            font-size: 2.5em;
            margin-right: 20px;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .social-media-icons a:last-child {
            margin-right: 0; /* Remove margin from last icon */
        }

        .social-media-icons a:hover {
            color: #a78bfa;
        }

        .get-in-touch-card {
            flex: 1;
            min-width: 400px;
            background-color: #a78bfa;
            color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            box-sizing: border-box;
        }

        .get-in-touch-card h2 {
            font-size: 2em;
            margin-bottom: 15px;
            text-align: center;
        }

        .get-in-touch-card p {
            line-height: 1.6;
            margin-bottom: 25px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group textarea {
            width: 100%; /* Make inputs take full width */
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            background-color: rgba(255, 255, 255, 0.9);
            color: #333;
            box-sizing: border-box; /* Include padding in input's total width */
        }

        .form-group textarea {
            resize: vertical;
        }

        .get-in-touch-card button {
            background-color: #1e3a8a;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: block;
            width: 100%;
        }

        .get-in-touch-card button:hover {
            background-color: #152d6a;
        }

        /* 5. Newsletter Section */
        .newsletter-section {
            padding: 100px 20px 0px 20px;
            text-align: center;
            border-radius: 10px;
            box-sizing: border-box;
        }

        .newsletter-section h2 {
            color: #1e3a8a;
            font-size: 2.2em;
            margin-bottom: 15px;
        }

        .newsletter-section p {
            font-size: 1.1em;
            margin-bottom: 30px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .newsletter-form {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .newsletter-form input[type="email"] {
            padding: 12px 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            flex: 1;
            max-width: 400px;
            box-sizing: border-box;
        }

        .newsletter-form button {
            background-color: #1e3a8a;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .newsletter-form button:hover {
            background-color: #152d6a;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .banner-section {
                padding: 60px 20px; /* Add horizontal padding for smaller screens */
            }

            .banner-section h1 {
                font-size: 2.5em;
            }

            .banner-section p {
                font-size: 1em;
                width: auto;
                padding: 0 10px; /* Adjust padding for better readability */
            }

            .banner-image {
                position: static; /* Make image flow naturally */
                width: 80%; /* Adjust width for smaller screens */
                margin: 20px auto; /* Center the image */
                left: auto; /* Remove absolute positioning */
                top: auto; /* Remove absolute positioning */
            }

            .contact-details {
                padding: 30px 20px;
            }

            .contact-details p {
                flex-direction: column; /* Stack icon and text vertically */
                align-items: center; /* Center items when stacked */
                text-align: center;
            }

            .contact-details p i {
                margin-right: 0;
                margin-bottom: 5px; /* Add some space below the icon */
            }

            .story-and-form-section {
                flex-direction: column;
                align-items: center;
                padding: 30px 20px;
                gap: 30px;
            }

            .our-story-social,
            .get-in-touch-card {
                min-width: unset;
                width: 100%;
            }

            .instagram-feed-placeholder {
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                padding: 15px;
            }

            .insta-post img {
                height: 120px;
            }

            .social-media-icons a {
                font-size: 2em;
                margin: 0 10px;
            }

            .newsletter-form {
                flex-direction: column;
                align-items: center;
            }

            .newsletter-form input[type="email"] {
                max-width: 100%;
                width: calc(100% - 40px); /* Account for padding */
            }
        }

        @media (max-width: 480px) {
            .banner-section h1 {
                font-size: 2em;
            }

            .banner-image {
                width: 90%;
            }

            .instagram-feed-placeholder {
                grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); /* Even smaller grid for tiny screens */
            }
            .insta-post img {
                height: 80px; /* Adjust image height for very small screens */
            }
        }
    </style>
</head>

<body>
    <section class="all-together">
        <div class="banner-section">
            <h1>Contact Us</h1>
            <p>We'd love to hear from you! Reach out to us for any inquiries, support, or collaboration opportunities.</p>
        </div>

        <div class="banner-image">
            <img src="images/her6.png" alt="TechTrekker Image">
        </div>

        <div class="contact-details">
            <h2>Contact Information</h2>
            <p>Our team is ready to assist you. Find our official contact details below:</p>
            <p><i class="fa-solid fa-phone"></i> Official Phone: +91 12345 67890</p>
            <p><i class="fas fa-envelope"></i> Official Email: info@techtrekker.com</p>
        </div>
    </section>

    <section class="story-and-form-section">
        <div class="our-story-social">
            <h2>Our Story</h2>
            <p>TechTrekker is dedicated to providing innovative online learning solutions. We believe in empowering individuals with the knowledge and skills they need to thrive in the digital age. Our platform offers a diverse range of courses designed by experts, focusing on practical application and real-world relevance. Join us on this journey of learning and discovery!</p>

            <h3>Our Instagram Feed</h3>
            <div class="instagram-feed-placeholder">
                <div class="insta-post">
                    <img src="images/IP1.png" alt="Instagram Post 1">
                    <p>Engaging lessons for every student!</p>
                </div>
                <div class="insta-post">
                    <img src="images/IP2.png" alt="Instagram Post 2">
                    <p>New course alert: Web Development!</p>
                </div>
                <div class="insta-post">
                    <img src="images/IP3.png" alt="Instagram Post 3">
                    <p>Interactive sessions for better understanding.</p>
                </div>
                <div class="insta-post">
                    <img src="images/IP4.png" alt="Instagram Post 4">
                    <p>Your journey to success starts here.</p>
                </div>
            </div>

            <h3>Our Social Media Links</h3>
            <div class="social-media-icons">
                <a href="#" class="fab fa-facebook-f"></a>
                <a href="#" class="fab fa-twitter"></a>
                <a href="#" class="fab fa-instagram"></a>
                <a href="#" class="fab fa-linkedin-in"></a>
                <a href="#" class="fab fa-youtube"></a>
            </div>
        </div>

        <div class="get-in-touch-card">
            <h2>Get in Touch</h2>
            <p>Have a question or want to share your thoughts? Fill out the form below and we'll get back to you as soon as possible.</p>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="form-group">
                    <label for="name">Your Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Your Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="message">Your Message</label>
                    <textarea id="message" name="message" rows="6" required></textarea>
                </div>
                <button type="submit" name="submit_message">Send Message</button>
            </form>
        </div>
    </section>

    <section class="newsletter-section">
        <div class="newsletter-content">
            <h2>Our Newsletter</h2>
            <p>Stay updated with our latest courses, news, and special offers. Subscribe to our newsletter!</p>
        </div>
        <div class="newsletter-form">
            <input type="email" placeholder="Enter your email" required>
            <button type="submit">Subscribe</button>
        </div>
    </section>

    <?php include 'footer.php'; // Includes your footer 
    ?>
</body>
</html>