<?php include 'right_side_bar.php' ?>

<div class="custom-wave2">
    <svg viewBox="0 0 1440 320">
        <path fill="#1E3A8A" fill-opacity="0.3" d="M0,160L60,160C120,160,240,160,360,144C480,128,600,96,720,112C840,128,960,192,1080,186.7C1200,181,1320,107,1380,69.3L1440,32L1440,320L1380,320C1320,320,1200,320,1080,320C960,320,840,320,720,320C600,320,480,320,360,320C240,320,120,320,60,320L0,320Z"></path>
        <path style="display: block; width: 100%; height: auto; margin: 0; padding: 0;" fill="#1E3A8A" fill-opacity="0.5" d="M0,192L60,181.3C120,171,240,149,360,154.7C480,160,600,192,720,181.3C840,171,960,117,1080,117.3C1200,117,1320,171,1380,197.3L1440,224L1440,320L1380,320C1320,320,1200,320,1080,320C960,320,840,320,720,320C600,320,480,320,360,320C240,320,120,320,60,320L0,320Z"></path>
        <path fill="#1E3A8A" fill-opacity="1" d="M0,224L60,229.3C120,235,240,245,360,229.3C480,213,600,171,720,170.7C840,171,960,213,1080,218.7C1200,224,1320,192,1380,176L1440,160L1440,320L1440,320C1440,320,1320,320,1080,320C960,320,840,320,720,320C600,320,480,320,360,320C240,320,120,320,60,320L0,320Z"></path>
    </svg>
</div>

<footer class="footer text-white" style="background-color: #1E3A8A;padding-bottom: 20px;">
    <div class="container">
        <div class="row align-items-start">
            <div class="col-md-3 mb-4">
                <h5 class="text-uppercase mb-3">Tech Trekkers</h5>
                <p>Your digital companion in skill-based learning. Start your journey into tech today.</p>
            </div>
            <div class="col-md-3 mb-4">
                <h5 class="text-uppercase mb-3">Explore</h5>
                <ul class="list-unstyled">
                    <li><a href="#courses" class="footer-link">Courses</a></li>
                    <li><a href="#categories" class="footer-link">Categories</a></li>
                    <li><a href="#mentors" class="footer-link">Our Mentors</a></li>
                    <li><a href="#blogs" class="footer-link">Blog</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5 class="text-uppercase mb-3">Support</h5>
                <ul class="list-unstyled">
                    <li><a href="#faq" class="footer-link">FAQs</a></li>
                    <li><a href="#help" class="footer-link">Help Center</a></li>
                    <li><a href="#privacy" class="footer-link">Privacy Policy</a></li>
                    <li><a href="#terms" class="footer-link">Terms & Conditions</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5 class="text-uppercase mb-3">Stay Connected</h5>
                <form class="d-flex mb-3" onsubmit="event.preventDefault(); alert('Thank you for subscribing!');">
                    <input type="email" class="form-control me-2" placeholder="Your email" required>
                    <button type="submit" class="btn btn-light">Subscribe</button>
                </form>
                <div>
                    <a href="#" class="footer-icon"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="footer-icon"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="footer-icon"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="footer-icon"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>
        </div>
        <hr class="footer-divider" />
        <div class="text-center mt-4 small">&copy; 2025 TechTrekkers. Empowering Learning Everywhere.</div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
<script src="script.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log("DEBUG: DOMContentLoaded fired in footer.php.");

        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

        navLinks.forEach(link => {
            const linkHref = link.getAttribute('href');
            if (linkHref && currentPath.endsWith(linkHref)) {
                navLinks.forEach(l => l.classList.remove('active'));
                link.classList.add('active');
            }
        });

        const openModalFlag = document.getElementById('openLoginModal');
        if (openModalFlag && openModalFlag.value === 'true') {
            console.log("DEBUG: Hidden input 'openLoginModal' found with value 'true'. Attempting to open modal.");
            const loginModalElement = document.getElementById('loginModal');
            if (loginModalElement) {
                const loginModal = new bootstrap.Modal(loginModalElement);
                loginModal.show();
                console.log("DEBUG: Modal show() called successfully.");
            } else {
                console.error("ERROR: Modal element with ID 'loginModal' not found.");
            }
        } else {
            console.log("DEBUG: Hidden input 'openLoginModal' not found or value not 'true'.");
        }

        const userIcon = document.getElementById('userIcon');
        const rightSidebar = document.getElementById('rightSidebar');
        const closeSidebarBtn = document.getElementById('closeSidebar');

        if (userIcon) {
            userIcon.addEventListener('click', function() {
                rightSidebar.classList.add('open');
            });
        }

        if (closeSidebarBtn) {
            closeSidebarBtn.addEventListener('click', function() {
                rightSidebar.classList.remove('open');
            });
        }
    });
</script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57B+UoKzO3QdJgGgzaK9fG/I5aTq0QzB3I3R3S3T3" crossorigin="anonymous"></script>
</body>
</html>