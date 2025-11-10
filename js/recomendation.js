const carousel = document.getElementById('carousel');
const container = document.getElementById('carousel-container');
const leftArrow = document.querySelector('.arrow-left');
const rightArrow = document.querySelector('.arrow-right');

let scrollSpeed = 0.5; // pixels per frame
let scrollPos = 0;
let animationFrame;

let isRunning = false;     // handles hover pause/resume
let isCarouselOn = false;  // master on/off toggle

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

// ensure carousel starts visually aligned
function setInitialTransform() {
    const startFirstCardWidth = carousel.children[0]?.offsetWidth + 12 || 0;
    carousel.style.transform = `translateX(-${scrollPos + startFirstCardWidth}px)`;
}
setInitialTransform();

// seamless scroll function
function scrollCarousel() {
    if (!isRunning || !isCarouselOn) return; // respect both flags

    scrollPos += scrollSpeed;
    const firstCardWidth = carousel.children[0].offsetWidth + 12;

    if (scrollPos >= firstCardWidth) {
        carousel.appendChild(carousel.children[0]);
        scrollPos -= firstCardWidth;
    }

    carousel.style.transform = `translateX(-${scrollPos + firstCardWidth}px)`;

    animationFrame = requestAnimationFrame(scrollCarousel);
}

// Only start auto-scroll if carousel is enabled
if (isCarouselOn) {
    animationFrame = requestAnimationFrame(scrollCarousel);
}

// Hover pause/resume — only if carousel is enabled
container.addEventListener('mouseenter', () => {
    if (isCarouselOn) isRunning = false;
});

container.addEventListener('mouseleave', () => {
    if (isCarouselOn && !isRunning) {
        isRunning = true;
        animationFrame = requestAnimationFrame(scrollCarousel);
    }
});

// External control functions
function startCarousel() {
    if (isCarouselOn) {
        isRunning = true;
        animationFrame = requestAnimationFrame(scrollCarousel);
    }
}

function stopCarousel() {
    isRunning = false;
}

// Arrow manual scroll
const cardWidth = carousel.children[0]?.offsetWidth + 12 || 192;

let isAnimating = false;
const firstCardWidth = carousel.children[0].offsetWidth + 12;
const animationDuration = 200; // milliseconds

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
    if (isAnimating) return;

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

// Initialize transform and handle details-based carousel control
window.addEventListener('load', () => {
    const details = document.getElementById('recommendations-details');
    const mediaSection = document.getElementById('all-media-view');

    setInitialTransform(); // ensure proper position

    // Turn on/off carousel availability based on media section visibility
    if (mediaSection && mediaSection.classList.contains('hidden')) {
        // Section hidden -> stop carousel availability
        isCarouselOn = false;
    } else {
        // Section visible -> start carousel availability
        isCarouselOn = true;
    }

    // Add listener for details open/close to start/stop animation
    if (details) {
        // Set initial state based on details open attribute
        if (details.hasAttribute('open')) {
            startCarousel();
        } else {
            stopCarousel();
        }

        details.addEventListener('toggle', () => {
            if (details.open) {
                startCarousel();
            } else {
                stopCarousel();
            }
        });
    }


//test scroll to existing card with same title    doesent take mediatype into account so might break with same titled books/movies etc
function normalizeTitle(title) {
    if (!title) return '';
    // Replace non-breaking spaces with normal spaces, trim, and collapse multiple spaces
    return title.replace(/\u00A0/g, ' ').replace(/\s+/g, ' ').trim().toLowerCase();
}

const recommendationRow = document.querySelector('.recommendation-row');

recommendationRow.addEventListener('click', (event) => {
    const card = event.target.closest('.favorite-media-card');
    if (!card) return;

    // const title = normalizeTitle(card.querySelector('.title')?.textContent);
    // if (!title) return;

    const dataId = card.dataset.id;
    if (!dataId) return;

    // const matchingCard = Array.from(document.querySelectorAll('.card')).find(c => {
    //     const cardTitle = normalizeTitle(c.querySelector('.media-title-container h3')?.textContent);
    //     return cardTitle === title;
    // });

    // document.querySelectorAll(`.card[data-id="${dataId}"]`) // Find first match from query
    let matchingCard = document.querySelector(`.card[data-id="${dataId}"]`);
    
    // If not matchingCard match by title
    if (!matchingCard) {
        const title = normalizeTitle(card.querySelector('.title')?.textContent);
        if (!title) return;

        matchingCard = Array.from(document.querySelectorAll('.card')).find(c => {
            const cardTitle = normalizeTitle(c.querySelector('.media-title-container h3')?.textContent);
            return cardTitle === title;
        });
    }

    if (matchingCard) {
        matchingCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        matchingCard.style.transition = 'background-color 0.3s';
        matchingCard.style.backgroundColor = '#ffff99';
        setTimeout(() => {
            matchingCard.style.backgroundColor = '';
        }, 1000);
    } else {
        console.log(`No matching card found for "${title}"`);
    }
});

});


//MARK: bad solution to returning to switching views 

// Turn carousel on when "All Media" button is clicked
const allMediaBtn = document.getElementById('all-media-button');
if (allMediaBtn) {
    allMediaBtn.addEventListener('click', () => {
        isCarouselOn = true;
        startCarousel(); // optional: immediately start scrolling
    });
}

// Turn carousel off when "My Account" button is clicked
const myAccountBtn = document.getElementById('my-account-button');
if (myAccountBtn) {
    myAccountBtn.addEventListener('click', () => {
        isCarouselOn = false;
        stopCarousel(); // optional: immediately stop scrolling
    });
}