<?php

$fieldWeights = [
    "isbn" => 5,
    "isan" => 5,
    "sab_code" => 4,
    "title" => 3,
    "author" => 2,
    "description" => 1
];

$fieldWeights_adminUsers = [
    "username" => 1
]

$fieldWeights_adminMedia = [
    "media_type" => 6,
    "isbn" => 5,
    "isan" => 5,
    "sab_code" => 4,
    "title" => 3,
    "author" => 2,
    "description" => 1
]

$fieldWeights_adminCopies = [
    "barcode" => 3,
    "title" => 2,
    "status" => 1
];

$fieldWeights_adminLoans = [
    "loan_date" => 5,
    "due_date" => 5,
    "return_date" => 5,
    "username" => 4,
    "title" => 3,
    "barcode" => 2,
    "status" => 1
]

// Function to split search term into tokens, handling quoted phrases
function tokenizeSearchTerm(string $term): array {
    $tokens = [];
    preg_match_all('/"([^"]+)"|(\S+)/', $term, $matches);
    foreach ($matches[1] as $i => $match) {
        $tokens[] = $match !== "" ? $match : $matches[2][$i];
    }
    return $tokens;
}

function normalizeForMatching(string $field, string $value): string {
    // Remove hyphens only for isbn/isan
    if (in_array($field, ['isbn', 'isan'])) {
        return str_replace('-', '', mb_strtolower($value));
    }
    // Case sensitive for BARCODE
    if ($field === 'barcode') {
        return $value;
    }

    // case-insensitive for other fields
    return mb_strtolower($value); 
}

// Returns [{mediaId, score, matches=[{field,index,length,score,token},...]},...]
// If passed uses $weightOverrides instead of default weights, NOT MERGED WITH DEFAULTS
function SearchMedia($medias, string $searchTerm, ?string $filterType = null, ?array $weightOverrides = null): array {
    global $fieldWeights;

    $usedWeights = [];
    if ($weightOverrides !== null) {
        // Merge overrides
        $usedWeights = $weightOverrides;
    } else {
        $usedWeights = $fieldWeights;
    }

    // If term is empty return empty
    if (trim($searchTerm) === '') {
        return [];
    }

    // Tokenize search term
    $tokens = tokenizeSearchTerm($searchTerm);

    // Iterate tokens
    $results = [];
    foreach ($medias as $mediaIndex => $media) {
        $mediaId = $media['id'] ?? $mediaIndex;

        // filterType?
        if ($filterType !== null && isset($media['media_type']) && $media['media_type'] !== $filterType) {
            continue;
        }

        $mediaMatches = [];

        foreach ($usedWeights as $field => $weight) {
            $content = $media[$field] ?? '';

            foreach ($tokens as $token) {
                $normalizedToken = normalizeForMatching($field, $token);
                $normalizedContent = normalizeForMatching($field, $content);

                // Check for match and if so where
                $pos = stripos($normalizedContent, $normalizedToken);
                if ($pos !== false) {
                    // Get the length of the matching token
                    $matchLength = mb_strlen($token);

                    // Calculate score bsaed on $weight * amnt-matched
                    $amntMatched = $matchLength / max(1, mb_strlen($normalizedContent));
                    $score = $weight * $amntMatched;

                    // Record the match
                    $mediaMatches[] = [
                        'field' => $field,
                        'index' => $pos,
                        'length' => $matchLength,
                        'score' => $score,
                        'token' => $token
                    ];
                }
            }
        }

        // Sum scores for this media
        if (!empty($mediaMatches)) {
            // Sum scores for sorting
            $totalScore = array_sum(array_column($mediaMatches, 'score'));

            $results[] = [
                'mediaId' => $mediaId,
                'score' => $totalScore,
                'matches' => $mediaMatches
            ];
        }
    }
    
    // Sort results by score descending
    usort($results, function($a, $b) {
        if ($a['score'] == $b['score']) {
            return 0;
        }
        return ($a['score'] < $b['score']) ? 1 : -1;
    });

    return $results;
}