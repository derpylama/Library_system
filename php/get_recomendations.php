<?php


function getRecommendations(
    PDO $pdo,
    ?int $userId = null, // negativ if you no user id or empty
    int $loanHistoryLimit = 50,     // how many latest loans to analyze
    int $recommendationLimit = 20,  // how many recommendations to return
    bool $diversityMode = true,     // cap dominance of a single category
    float $maxCategoryShare = 0.7   // max share per category if diversity mode on
): array
{
    // ✅ If no userId provided → return 10 most loaned media
    if (empty($userId) || $userId <= 0) {
        $stmt = $pdo->query("
            SELECT m.id AS media_id
            FROM media m
            JOIN copy c ON c.media_id = m.id
            JOIN loan l ON l.copy_id = c.id
            WHERE c.status = 'available'
            GROUP BY m.id
            ORDER BY COUNT(l.id) DESC
            LIMIT 10
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // 1️ Fetch user's most recent sab_codes from loans
    $query = "
        SELECT m.sab_code
        FROM loan l
        JOIN copy c ON l.copy_id = c.id
        JOIN media m ON c.media_id = m.id
        WHERE l.user_id = :user_id
        ORDER BY l.loan_date DESC
        LIMIT $loanHistoryLimit
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $userId]);
    $sabCodes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($sabCodes)) {
        return []; // No history = no recommendations
    }

    // 2️ Extract main categories (first uppercase letter)
    $mainCategories = array_filter(array_map(function ($code) {
        return preg_match('/[A-Z]/', $code, $matches) ? $matches[0] : null;
    }, $sabCodes));

    if (empty($mainCategories)) {
        return [];
    }

    // 3 Count and find top 3 categories
    $categoryCounts = array_count_values($mainCategories);
    arsort($categoryCounts);
    $topCategories = array_slice($categoryCounts, 0, 3, true);

    // 4️ Calculate proportional limits
    $total = array_sum($topCategories);
    $categoryLimits = [];
    $accumulated = 0;

    foreach ($topCategories as $cat => $count) {
        $portion = ($count / $total) * $recommendationLimit;

        // Apply diversity cap if enabled
        if ($diversityMode) {
            $maxForCategory = $recommendationLimit * $maxCategoryShare;
            $portion = min($portion, $maxForCategory);
        }

        $categoryLimits[$cat] = round($portion);
        $accumulated += $categoryLimits[$cat];
    }

    // Adjust rounding to total exactly recommendationLimit
    while ($accumulated < $recommendationLimit) {
        $firstKey = array_key_first($categoryLimits);
        $categoryLimits[$firstKey]++;
        $accumulated++;
    }
    while ($accumulated > $recommendationLimit) {
        $lastKey = array_key_last($categoryLimits);
        $categoryLimits[$lastKey]--;
        $accumulated--;
    }

    // 5️ Fetch recommendations proportionally per category
    $recommendations = [];
    $missing = 0;

    foreach ($categoryLimits as $category => $limit) {
        if ($limit <= 0) continue;

        $excludeList = empty($recommendations) ? '0' : implode(',', $recommendations);

        $query = "
            SELECT m.id AS media_id
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
              AND m.id NOT IN ($excludeList)
            GROUP BY m.id
            ORDER BY COUNT(l.id) DESC, RAND()
            LIMIT $limit
        ";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'sab_pattern' => $category . '%',
            'user_id' => $userId
        ]);

        $mediaIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $recommendations = array_merge($recommendations, $mediaIds);
        $missing += max(0, $limit - count($mediaIds));
    }

    // 6️ Fill any missing slots with random available items (no duplicates)
    if ($missing > 0) {
        $excludeList = empty($recommendations) ? '0' : implode(',', $recommendations);

        $query = "
            SELECT m.id AS media_id
            FROM media m
            JOIN copy c ON c.media_id = m.id
            LEFT JOIN loan l ON l.copy_id = c.id
            WHERE c.status = 'available'
              AND m.id NOT IN (
                  SELECT DISTINCT c2.media_id
                  FROM loan l2
                  JOIN copy c2 ON l2.copy_id = c2.id
                  WHERE l2.user_id = :user_id
              )
              AND m.id NOT IN ($excludeList)
            GROUP BY m.id
            ORDER BY COUNT(l.id) DESC, RAND()
            LIMIT $missing
        ";

        $stmt = $pdo->prepare($query);
        $stmt->execute(['user_id' => $userId]);
        $extra = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $recommendations = array_merge($recommendations, $extra);
    }

    // 7️ Remove duplicates and trim to desired length
    $recommendations = array_values(array_unique($recommendations));
    $recommendations = array_slice($recommendations, 0, $recommendationLimit);

    return $recommendations;
}

?>
