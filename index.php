<?php include 'header.php'; ?>
<?php
// Check if a success message is set in the session
if (isset($_SESSION['registration_success'])) {
    $message_type = $_SESSION['registration_success'];
    $alert_message = "";

    if ($message_type === "student") {
        $alert_message = "Congratulations! Student registration has been successful. Login now.";
    } elseif ($message_type === "admin") {
        $alert_message = "Congratulations! Admin registration has been successful. Login now.";
    } elseif ($message_type === "teacher") {
        $alert_message = "Congratulations! teacher registration has been successful. Login now.";
    }
    if (!empty($alert_message)) {
        echo '
        <div class="container mt-4">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> ' . htmlspecialchars($alert_message) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>';
    }
    // Unset the session variable so the alert doesn't show again on refresh
    unset($_SESSION['registration_success']);
}
?>
<!-- hero -->
<section id="hero" class="py-4" style="background-color:#F9FAFB; padding: 0px 0px 0px 0px;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start mb-4 mb-md-0">
                <h1 class="mb-3" style="color:#1E3A8A;">Learn Anytime, Anywhere!</h1>
                <p class="mb-4" style="color:#374151;">Your journey to smart learning starts here</p>
                <a href="#" class="btn btn-primary btn-lg" style="background-color:#60A5FA; border:none;">Explore Courses</a>
            </div>
            <div class="col-md-6 text-center">

                <!-- Image for screens up to 768px -->
                <picture>
                    <source srcset="images/her2.png" media="(max-width: 768px)">
                    <img src="images/her.png" alt="Learning Illustration" class="img-fluid" style="max-width: 70%; height: auto;">
                </picture>


            </div>
        </div>
    </div>
</section>
<!-- statistics -->
<section class="text-center recognised stats-section">
    <div class="d-flex justify-content-around flex-wrap text-white">
        <div class="stat-item">
            <h2 class="count" data-target="360">0</h2><span class="plus-sign">+</span>
            <p>Hours of Learning</p>
        </div>
        <div class="stat-item">
            <h2 class="count" data-target="1000">0</h2><span class="plus-sign">+</span>
            <p>Students Enrolled</p>
        </div>
        <div class="stat-item">
            <h2 class="count" data-target="150">0</h2><span class="plus-sign">+</span>
            <p>Teachers</p>
        </div>
        <div class="stat-item">
            <h2 class="count" data-target="98">0</h2><span class="plus-sign">+</span>
            <p>Satisfaction Rate</p>
        </div>
    </div>
</section>
<!-- why choose us? -->
<section class="container mb-5" style=" background-color:#F9FAFB;">
    <h2 class="text-center mb-4" style="color:#1E3A8A;">Why Choose Us?</h2>
    <div class="owl-carousel owl-theme">
        <div class="item scroll-card p-3 rounded" style="background-color:#FFFFFF; border:1px solid #E5E7EB;">
            <div class="image-placeholder mb-2" style="height:300px; background:#E5E7EB; border-radius:8px;">
                <img src="images/industry.png" alt="industry.png" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">
            </div>
            <h5 style="color:#1E3A8A;">Industry-Based Learning</h5>
            <p style="color:#374151;">Skills aligned with real-world job needs</p>
        </div>
        <div class="item scroll-card p-3 rounded" style="background-color:#FFFFFF; border:1px solid #E5E7EB;">
            <div class="image-placeholder mb-2" style="height:300px; background:#E5E7EB; border-radius:8px; overflow:hidden;">
                <img src="images/experts.png" alt="experts.png" style="width:100%; height:100%; object-fit:cover; object-position: center 30%; border-radius:8px;">
            </div>
            <h5 style="color:#1E3A8A;">Learn from Experts</h5>
            <p style="color:#374151;">Experienced mentors from top domains</p>
        </div>
        <div class="item scroll-card p-3 rounded" style="background-color:#FFFFFF; border:1px solid #E5E7EB;">
            <div class="image-placeholder mb-2" style="height:300px; background:#E5E7EB; border-radius:8px;">
                <img src="images/AI.png" alt="AI.png" style="width:100%; height:100%; object-fit:cover; object-position: center 33%; border-radius:8px;">
            </div>
            <h5 style="color:#1E3A8A;">AI Support 24/7</h5>
            <p style="color:#374151;">Powered by ChatGPT/Gemini for instant help</p>
        </div>
        <div class="item scroll-card p-3 rounded" style="background-color:#FFFFFF; border:1px solid #E5E7EB;">
            <div class="image-placeholder mb-2" style="height:300px; background:#E5E7EB; border-radius:8px;">
                <img src="images/project.png" alt="project.png" style="width:100%; height:100%; object-fit:cover; object-position: center 50%; border-radius:8px;">
            </div>
            <h5 style="color:#1E3A8A;">Hands-on Projects</h5>
            <p style="color:#374151;">Build real solutions to boost your portfolio</p>
        </div>
        <div class="item scroll-card p-3 rounded" style="background-color:#FFFFFF; border:1px solid #E5E7EB;">
            <div class="image-placeholder mb-2" style="height:300px; background:#E5E7EB; border-radius:8px;">
                <img src="images/affordable.png" alt="affordable.png" style="width:100%; height:100%; object-fit:cover; object-position: center 50%; border-radius:8px;">
            </div>
            <h5 style="color:#374151;">Affordable Pricing</h5>
            <p class="text-muted">Quality learning at pocket-friendly rates.</p>
        </div>
        <div class="item scroll-card p-3 rounded" style="background-color:#FFFFFF; border:1px solid #E5E7EB;">
            <div class="image-placeholder mb-2" style="height:300px; background:#E5E7EB; border-radius:8px;">
                <img src="images/advanced.png" alt="advanced.png" style="width:100%; height:100%; object-fit:cover; object-position: center 15%; border-radius:8px;">
            </div>
            <h5 style="color:#374151;">Beginner to Advanced</h5>
            <p class="text-muted">Courses for all levels of learners.</p>
        </div>
        <div class="item scroll-card p-3 rounded" style="background-color:#FFFFFF; border:1px solid #E5E7EB;">
            <div class="image-placeholder mb-2" style="height:300px; background:#E5E7EB; border-radius:8px;">
                <img src="images/certification.png" alt="certification.png" style="width:100%; height:100%; object-fit:cover; object-position: center 40%; border-radius:8px;">
            </div>
            <h5 style="color:#374151;">Certification</h5>
            <p class="text-muted">Build real-world skills and earn a certificate.</p>
        </div>
    </div>
    <div id="dots" class="dot-container mt-4 text-center"></div>
</section>
<!-- how it works -->
<section class="py-5 text-center" style="color:#1e3a8a; margin-bottom: 100px;">
    <h2 class="mb-5">How It Works</h2>
    <div class="d-flex justify-content-center align-items-center gap-4 flex-wrap">
        <div class="d-flex flex-column align-items-center justify-content-center text-center">
            <i class="fa-solid fa-user-plus step-icon mb-2 d-block mx-auto"></i>
            <div class="step-text">
                <h6>Step 1</h6>
                <p>Enroll in your desired course</p>
            </div>
        </div>
        <div class="vertical-bar"></div>
        <div class="d-flex flex-column align-items-center justify-content-center text-center">
            <i class="fa-solid fa-credit-card step-icon mb-2 d-block mx-auto"></i>
            <div class="step-text">
                <h6>Step 2</h6>
                <p>Pay securely via UPI, Card or Wallets</p>
            </div>
        </div>
        <div class="vertical-bar"></div>
        <div class="d-flex flex-column align-items-center justify-content-center text-center">
            <i class="fa-regular fa-clock step-icon mb-2 d-block mx-auto"></i>
            <div class="step-text">
                <h6>Step 3</h6>
                <p>Get batch & timing within 24 hrs</p>
            </div>
        </div>
        <div class="vertical-bar"></div>
        <div class="d-flex flex-column align-items-center justify-content-center text-center">
            <i class="fa-solid fa-award step-icon mb-2 d-block mx-auto"></i>
            <div class="step-text">
                <h6>Step 4</h6>
                <p>Start learning & earn a certificate</p>
            </div>
        </div>
    </div>
</section>
<!-- testimonials -->
<section class="testimonial-slider" style="position: relative; overflow: hidden;">
    <div class="bg-image"></div>
    <div class="overlay"></div>
    <div class="swiper testimonial-swiper">
        <div class="swiper-wrapper">
            <div class="swiper-slide">
                <h4 class="name">Riya Sharma</h4>
                <div class="stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="quote">“The courses at TechTrekker are incredibly well structured, combining theory with practical applications perfectly. The AI support system went beyond my expectations — it helped me clarify doubts instantly and kept me motivated throughout. I genuinely feel more confident in my skills now, and I can see a clear path ahead in my career. This learning experience has been truly transformational!”</p>
                <p class="course">Completed: Bioinformatics & Genomics</p>
            </div>
            <div class="swiper-slide">
                <h4 class="name">Aditya Singh</h4>
                <div class="stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i>
                </div>
                <p class="quote">“I learned an incredible amount in a very short period. The course material was detailed and engaging, and the instructors were always available to help. The hands-on projects helped me apply what I learned to real-world problems. I highly recommend TechTrekker to anyone wanting to advance their knowledge in cutting-edge technologies with practical insights.”</p>
                <p class="course">Completed: Machine Learning for Civil Engineers</p>
            </div>
            <div class="swiper-slide">
                <h4 class="name">Priya Das</h4>
                <div class="stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i>
                </div>
                <p class="quote">“The support throughout the course was phenomenal! The interactive UI made the entire learning journey enjoyable and easy to navigate. I appreciated the timely responses to questions and the encouragement from the community. It’s rare to find an online course that makes learning so fun and effective at the same time.”</p>
                <p class="course">Completed: AI in Environmental Engineering</p>
            </div>
            <div class="swiper-slide">
                <h4 class="name">Kunal Mehta</h4>
                <div class="stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i>
                </div>
                <p class="quote">“As someone new to biotech, I found the courses at TechTrekker to be extremely beginner-friendly yet comprehensive. Complex molecular biology concepts were broken down into simple, understandable lessons without sacrificing depth. This course made me feel confident to pursue further studies and career opportunities in biotechnology.”</p>
                <p class="course">Completed: Molecular Biology for Beginners</p>
            </div>
            <div class="swiper-slide">
                <h4 class="name">Sneha Roy</h4>
                <div class="stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i>
                </div>
                <p class="quote">“Before joining TechTrekker, coding felt intimidating and overwhelming. But the Python for Bioinformatics course changed all that. Step by step, I learned to code with clear explanations, real-life examples, and lots of practice. Now, coding feels like a superpower, and I’m excited to use it in my career!”</p>
                <p class="course">Completed: Python for Bioinformatics</p>
            </div>
            <div class="swiper-slide">
                <h4 class="name">Aman Tripathi</h4>
                <div class="stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="quote">“The practical labs included in the AI in Structural Monitoring course were outstanding! They gave me hands-on experience with real data, tools, and techniques that I wouldn’t have gotten elsewhere. The instructors were supportive and always willing to help troubleshoot. This course was a game-changer for my skills and confidence.”</p>
                <p class="course">Completed: AI in Structural Monitoring</p>
            </div>
        </div>
    </div>
</section>
<!-- CTA -->
<section class="cta-section-wrapper">
    <div class="cta-card">
        <h2>Start Trekking Your Journey with Tech Trekkers!</h2>
        <a href="#" class="cta-button">Get Started</a>
    </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Your existing stats counter script
    document.addEventListener('DOMContentLoaded', () => {
        const counters = document.querySelectorAll('.count');
        const speed = 200; // The lower the speed, the faster the count

        counters.forEach(counter => {
            const updateCount = () => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;
                const increment = target / speed;

                if (count < target) {
                    counter.innerText = Math.ceil(count + increment);
                    setTimeout(updateCount, 1);
                } else {
                    counter.innerText = target;
                }
            };
            updateCount();
        });
    });
    // Add this new JavaScript block
    document.addEventListener('DOMContentLoaded', function() {
        <?php
        // Check for a successful signup message
        if (isset($_SESSION['signup_success_message'])) {
            echo '
            // Display Bootstrap Success Alert (Green)
            const alertHtml = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> ' . htmlspecialchars($_SESSION['signup_success_message']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            `;
            document.getElementById("alertContainer").innerHTML = alertHtml;

            // Show the login modal
            const loginModal = new bootstrap.Modal(document.getElementById("loginModal"));
            loginModal.show();
            ';
            // Unset the session variable so the alert doesn\'t show again on refresh
            unset($_SESSION['signup_success_message']);
        }
        ?>
    });
</script>
</body>
<?php include 'footer.php'; ?>