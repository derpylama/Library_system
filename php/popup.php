<?php
require_once __DIR__ . '/db.php';

function popupOutputer($delmedia = null, $message = null) {
    
    echo ifDeleteMedia($delmedia, $message);
    if ($message !== null && $delmedia === null) {
        echo '<div class="popup">';
        echo '<p>' . $message . '</p>';
        echo '<button class="popup-close-button" onclick="this.parentElement.classList.add(\'hidden\')">Close</button>';
        echo '</div>';
        // Clear the message after displaying it
        $message === null;
    }
}

function ifDeleteMedia($mediaId = null, $message = null) {
    if ($mediaId !== null && $message !== null) {
        // check if the media is linked to any active loans or has copies
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT COUNT(l.id)
            FROM loan l
            JOIN copy c ON l.copy_id = c.id
            WHERE c.media_id = ? AND l.status = ?;
        ");
        $stmt->execute([$mediaId, 'active']);
        $activeLoansCount = intval($stmt->fetchColumn());
        $stmt = $pdo->prepare("SELECT COUNT(id) FROM copy WHERE media_id = ?;");
        $stmt->execute([$mediaId]);
        $copiesCount = intval($stmt->fetchColumn());
        if ($activeLoansCount > 0 || $copiesCount > 0) {
            // full popup with a confirmation message and a confirm button and a cancel button
            echo '<div class="popup">';
            echo '<h2>Obs!!!</h2>';
            echo '<p>You are trying to delete a media that has copies(and has <strong>'. $activeLoansCount .'</strong> borrowed)</p>';
            echo '<p>if you do this, you will delete all (<strong>' . $copiesCount . '</strong>) copies for this media, also the ones loaned.</p>';
            echo '<p><strong>Be sure that all fysical books has been returned before continueing.</strong></p>';
            echo '<p>Are you sure you want to delete <strong>' . $message . '</strong>?</p>';
            echo '<div class="popup-buttons-container">';
            echo '<button class="close-button" onclick="this.parentElement.parentElement.classList.add(\'hidden\')">Close</button>';
            echo '<form method="POST">';
            echo '<input type="hidden" name="confirmed_delete_media" value="' . htmlspecialchars($mediaId) . '">';
            echo '<button type="submit" class="confirm-delete-button">Confirm Delete</button>';
            echo '</form>';
            echo '</div>';
            echo '</div>';

        } else {
            echo '<div class="popup">';
            echo '<h2>Obs!!!</h2>';
            echo '<p>Are you sure you want to delete <strong>' . $message . '</strong>?</p>';
            echo '<div class="popup-buttons-container">';
            echo '<button class="close-button" onclick="this.parentElement.parentElement.classList.add(\'hidden\')">Close</button>';
            echo '<form method="POST">';
            echo '<input type="hidden" name="confirmed_delete_media" value="' . htmlspecialchars($mediaId) . '">';
            echo '<button type="submit" class="confirm-delete-button">Confirm Delete</button>';
            echo '</form>';
            echo '</div>';
            echo '</div>';
            
        }
    } else return false;
}