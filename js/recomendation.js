const carousel = document.getElementById('carousel');
const container = document.getElementById('carousel-container');
const leftArrow = document.querySelector('.arrow-left');
const rightArrow = document.querySelector('.arrow-right');

let scrollSpeed = 0.5; // pixels per frame
let scrollPos = 0;
let animationFrame;
let isCarouselRunning = true; // ✅ toggle this variable to start/stop

// Clone items to make seamless infinite scroll
function setupInfiniteScroll() {
    const cards = Array.from(carousel.children);
    if (cards.length === 0) return;

    let totalWidth = 0;
    cards.forEach(card => totalWidth += card.offsetWidth + 12);
    while (totalWidth < container.offsetWidth * 2) {
        cards.forEach(card => {
            const clone = card.cloneNode(true);
            carousel.appendChild(clone);
            totalWidth += clone.offsetWidth + 12;
        });
    }
}
setupInfiniteScroll();

// Seamless scroll function
function scrollCarousel() {
    if (!isCarouselRunning) return; // ✅ stop if paused

    scrollPos += scrollSpeed;
    const firstCardWidth = carousel.children[0].offsetWidth + 12;

    if (scrollPos >= firstCardWidth) {
        carousel.appendChild(carousel.children[0]);
        scrollPos -= firstCardWidth;
    }

    carousel.style.transform = `translateX(-${scrollPos + firstCardWidth}px)`;
    animationFrame = requestAnimationFrame(scrollCarousel);
}

// Start auto-scroll
animationFrame = requestAnimationFrame(scrollCarousel);

// ✅ Helper functions to control it manually
function startCarousel() {
    if (!isCarouselRunning) {
        isCarouselRunning = true;
        animationFrame = requestAnimationFrame(scrollCarousel);
    }
}

function stopCarousel() {
    isCarouselRunning = false;
    cancelAnimationFrame(animationFrame);
}

// Pause on hover
container.addEventListener('mouseenter', stopCarousel);
container.addEventListener('mouseleave', startCarousel);

// Arrow manual scroll
const firstCardWidth = carousel.children[0].offsetWidth + 12;
let isAnimating = false;
const animationDuration = 200;

function animateScroll(newScrollPos, callback) {
    isAnimating = true;
    carousel.style.transition = `transform ${animationDuration}ms ease`;
    carousel.style.transform = `translateX(-${newScrollPos + firstCardWidth}px)`;

    setTimeout(() => {
        carousel.style.transition = '';
        isAnimating = false;
        if (callback) callback();
    }, animationDuration);
}

leftArrow.addEventListener('click', () => {
    if (isAnimating) return;
    stopCarousel(); // ✅ optional: pause while manually scrolling

    scrollPos -= firstCardWidth;

    if (scrollPos < 0) {
        animateScroll(scrollPos, () => {
            carousel.insertBefore(carousel.lastElementChild, carousel.firstElementChild);
            scrollPos += firstCardWidth;
            carousel.style.transform = `translateX(-${scrollPos + firstCardWidth}px)`;
            startCarousel(); // ✅ resume if desired
        });
    } else {
        animateScroll(scrollPos, startCarousel);
    }
});

rightArrow.addEventListener('click', () => {
    if (isAnimating) return;
    stopCarousel();

    scrollPos += firstCardWidth;

    if (scrollPos >= firstCardWidth) {
        animateScroll(scrollPos, () => {
            carousel.appendChild(carousel.children[0]);
            scrollPos -= firstCardWidth;
            carousel.style.transform = `translateX(-${scrollPos + firstCardWidth}px)`;
            startCarousel();
        });
    } else {
        animateScroll(scrollPos, startCarousel);
    }
});

// Hide arrows if not needed
if (carousel.scrollWidth <= container.offsetWidth) {
    leftArrow.classList.add('hidden');
    rightArrow.classList.add('hidden');
}
