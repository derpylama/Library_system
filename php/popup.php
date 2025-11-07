<?php

function popupOutputer() {
    if (isset($_SESSION['popup_message'])) {
        echo '<div class="popup-message">';
        echo '<p>' . $_SESSION['popup_message'] . '</p>';
        echo '<button onclick="this.parentElement.style.display=\'none\';">Close</button>';
        echo '</div>';
        // Clear the message after displaying it
        unset($_SESSION['popup_message']);
    }
}