const carousel = document.getElementById('carousel');
const container = document.getElementById('carousel-container');
const leftArrow = document.querySelector('.arrow-left');
const rightArrow = document.querySelector('.arrow-right');

let scrollSpeed = 0.5; // pixels per frame
let scrollPos = 0;
let animationFrame;

// Clone items to make seamless infinite scroll
function setupInfiniteScroll() {
    const cards = Array.from(carousel.children);
    if (cards.length === 0) return;

    let totalWidth = 0;
    cards.forEach(card => totalWidth += card.offsetWidth + 12);
    // duplicate items until total width > container width * 2 for smooth scroll
    while (totalWidth < container.offsetWidth * 2) {
        cards.forEach(card => {
            const clone = card.cloneNode(true);
            carousel.appendChild(clone);
            totalWidth += clone.offsetWidth + 12;
        });
    }
}
setupInfiniteScroll();

// seamless scroll function
function scrollCarousel() {
    scrollPos += scrollSpeed;
    const firstCardWidth = carousel.children[0].offsetWidth + 12;

    if (scrollPos >= firstCardWidth) {
        carousel.appendChild(carousel.children[0]);
        scrollPos -= firstCardWidth;
    }

    // ✅ Add 1 extra card width so there’s always a card on the left
    carousel.style.transform = `translateX(-${scrollPos + firstCardWidth}px)`;

    animationFrame = requestAnimationFrame(scrollCarousel);
}

// Start auto-scroll
animationFrame = requestAnimationFrame(scrollCarousel);

// Pause on hover anywhere
container.addEventListener('mouseenter', () => cancelAnimationFrame(animationFrame));
container.addEventListener('mouseleave', () => animationFrame = requestAnimationFrame(scrollCarousel));

// Arrow manual scroll
const cardWidth = carousel.children[0]?.offsetWidth + 12 || 192;

let isAnimating = false; // lock to prevent spamming
const firstCardWidth = carousel.children[0].offsetWidth + 12;
const animationDuration = 200; // milliseconds

function animateScroll(newScrollPos, callback) {
    isAnimating = true; // lock buttons
    carousel.style.transition = `transform ${animationDuration}ms ease`;
    carousel.style.transform = `translateX(-${newScrollPos + firstCardWidth}px)`;

    setTimeout(() => {
        carousel.style.transition = '';
        isAnimating = false; // unlock buttons
        if (callback) callback();
    }, animationDuration);
}

leftArrow.addEventListener('click', () => {
    if (isAnimating) return; // ignore clicks while animating

    scrollPos -= firstCardWidth;

    if (scrollPos < 0) {
        animateScroll(scrollPos, () => {
            carousel.insertBefore(carousel.lastElementChild, carousel.firstElementChild);
            scrollPos += firstCardWidth;
            carousel.style.transform = `translateX(-${scrollPos + firstCardWidth}px)`;
        });
    } else {
        animateScroll(scrollPos);
    }
});

rightArrow.addEventListener('click', () => {
    if (isAnimating) return; // ignore clicks while animating

    scrollPos += firstCardWidth;

    if (scrollPos >= firstCardWidth) {
        animateScroll(scrollPos, () => {
            carousel.appendChild(carousel.children[0]);
            scrollPos -= firstCardWidth;
            carousel.style.transform = `translateX(-${scrollPos + firstCardWidth}px)`;
        });
    } else {
        animateScroll(scrollPos);
    }
});

// Hide arrows if not needed
if (carousel.scrollWidth <= container.offsetWidth) {
    leftArrow.classList.add('hidden');
    rightArrow.classList.add('hidden');
}