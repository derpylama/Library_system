<?php
require_once('db.php');
require_once('get_recomendations.php');

// Fetch recommendations
$userid=2;
$recommendations = getRecommendations($pdo, $userid, 50, 10, true, 0.7);


// Fetch media details
$mediaList = [];
if (!empty($recommendations)) {
    $inQuery = implode(',', array_fill(0, count($recommendations), '?'));
    $stmt = $pdo->prepare("SELECT * FROM media WHERE id IN ($inQuery) ORDER BY FIELD(id, $inQuery)");
    $stmt->execute(array_merge($recommendations, $recommendations));
    $mediaList = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Recommendations</title>
<style>
body {
    background-color: #1e1e1e;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #d4d4d4;
    padding: 20px;
}
h2 {
    color: #569cd6;
    font-weight: 500;
    margin-bottom: 15px;
}

.carousel-container {
    position: relative;
    overflow: hidden;
    width: 100%;
    height: 350px; /* set the carousel height */
    background-color:rgb(116, 65, 65);
    border-radius: 2px;
}

.recommendation-row {
    display: flex;
    gap: 12px;
    height: 100%;
    align-items: center; /* center the cards vertically */
}

.favorite-media-card:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.8);
}

.favorite-media-card {
    width: 180px;
    min-width: 180px;
    flex: 0 0 auto;
    background-color: #252526;
    border: 1px solid #3c3c3c;
    border-radius: 6px;
    overflow: hidden;
    text-align: center;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.6);
    display: flex;
    flex-direction: column;
    height: 85%;
    margin: 0 auto;

}

.favorite-media-card img {
    width: 100%;
    height: 85%; /* fixed proportion of card height */
    object-fit: cover; /* crop rather than stretching */
    border-bottom: 1px solid #3c3c3c;
}

.favorite-media-card .title {
    padding: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #dcdcaa;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 100%;
    box-sizing: border-box;
    flex-shrink: 0;
    background-color: #252526;
    height: 15%; /* title takes remaining height */
    display: block;
    align-items: center; /* vertically center title */

}


/* Arrows */
.arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 60px;
    background-color: rgba(0,0,0,0.5);
    color: #fff;
    font-size: 30px;
    text-align: center;
    line-height: 60px;
    cursor: pointer;
    z-index: 10;
    border-radius: 4px;
    user-select: none;
}
.arrow-left { left: 0; }
.arrow-right { right: 0; }
.arrow.hidden { display: none; }
</style>
</head>
<body>

<h2>Recommended for You</h2>
<div class="carousel-container" id="carousel-container">
    <div class="arrow arrow-left">&#8249;</div>
    <div class="arrow arrow-right">&#8250;</div>
    <div class="recommendation-row" id="carousel">
        <?php foreach ($mediaList as $media): ?>
            <div class="favorite-media-card">
                <img src="<?= htmlspecialchars($media['image_url']) ?>" alt="<?= htmlspecialchars($media['title']) ?>">
                <div class="title"><?= htmlspecialchars($media['title']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
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
</script>

</body>
</html>
