// Owl Carousel: Initializes responsive carousel for "Why Choose Us" section.
$(document).ready(function () {
  $(".owl-carousel").owlCarousel({
    loop: true,
    margin: 20,
    nav: true,
    dots: true,
    autoplay: true,
    autoplayTimeout: 3000,
    responsive: {
      0: { items: 1 },
      600: { items: 2 },
      1000: { items: 3 },
    },
  });
});

// Swiper Testimonial Carousel: Sets up looping, autoplaying testimonial slider.
const swiper = new Swiper(".testimonial-swiper", {
  loop: true,
  autoplay: {
    delay: 3000,
    disableOnInteraction: false,
  },
  slidesPerView: 1,
  spaceBetween: 30,
});

// Count-up Animation: Animates numbers in the statistics section.
const counters = document.querySelectorAll(".count");

counters.forEach((counter) => {
  const updateCount = () => {
    const target = +counter.getAttribute("data-target");
    const count = +counter.innerText;

    const increment = target / 200;

    if (count < target) {
      counter.innerText = Math.ceil(count + increment);
      setTimeout(updateCount, 10);
    } else {
      counter.innerText = target;
    }
  };

  updateCount();
});


