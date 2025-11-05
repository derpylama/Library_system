<?php
require_once __DIR__ . '/../../php/db.php';


// Function to get book recommendations based on user's loan history maybe change to give of more than 1 catagory
function getRecommendations(PDO $pdo, int $userId, int $recommendationLimit = 20, int $favoriteLimit = 10): array
{
    // 1️⃣ Fetch recent sab_codes from user's latest loans
    $query = "
        SELECT m.sab_code
        FROM loan l
        JOIN copy c ON l.copy_id = c.id
        JOIN media m ON c.media_id = m.id
        WHERE l.user_id = :user_id
        ORDER BY l.loan_date DESC
        LIMIT $recommendationLimit
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $userId]);
    $sabCodes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($sabCodes)) {
        return []; // no loans → no recommendations
    }

    // 2️⃣ Extract main categories (first uppercase letter from sab_code)
    $mainCategories = array_map(function ($code) {
        if (preg_match('/[A-Z]/', $code, $matches)) {
            return $matches[0];
        }
        return null;
    }, $sabCodes);

    $mainCategories = array_filter($mainCategories); // remove nulls

    if (empty($mainCategories)) {
        return []; // no valid categories found
    }

    // 3️⃣ Find the most frequent main category
    $categoryCounts = array_count_values($mainCategories);
    arsort($categoryCounts);
    $mostFrequentCategory = key($categoryCounts);

    // 4️⃣ Fetch recommended books based on that category
    $query = "
        SELECT 
            m.id AS media_id
        FROM media m
        JOIN copy c ON c.media_id = m.id
        LEFT JOIN loan l ON l.copy_id = c.id
        WHERE m.sab_code LIKE :sab_pattern
          AND c.status = 'available'
          AND m.id NOT IN (
              SELECT DISTINCT c2.media_id
              FROM loan l2
              JOIN copy c2 ON l2.copy_id = c2.id
              WHERE l2.user_id = :user_id
          )
        GROUP BY m.id
        ORDER BY COUNT(l.id) DESC
        LIMIT $favoriteLimit
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'sab_pattern' => '%' . $mostFrequentCategory . '%',
        'user_id' => $userId
    ]);

    $mediaIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    return $mediaIds; // array of media IDs
}


// ✅ Example usage
$recommendations = getRecommendations($pdo, 2, 20, 10);

foreach ($recommendations as $id) {
    echo "Media ID: $id<br>";
}


?>

