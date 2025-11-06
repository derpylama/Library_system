<?php

function getImageSizeW(string $imageUrl): ?array {
    if (empty($imageUrl)) {
        return null;
    }
    try {
        $r = @getimagesize($imageUrl);
        if ($r === false) {
            return null;
        }
        return $r;
    } catch (Exception $e) {
        return null;
    }
}

function cardSize(?array $imageSize): string {
    $style = '';

    if ($imageSize && $imageSize !== null && $imageSize !== false) {

        if ($imageSize && $imageSize !== null) {
            $width = $imageSize[0];
            $height = $imageSize[1];
    
            if ($width / $height > 1.2 || $height === $width) {
                $style = 'style="height: fit-content;"';
            }
        }

    } else {
        $style = 'style="height: fit-content;"';
    }

    return $style;
}

function imageType(string $imageUrl, ?array $imageSize, string $imageAlt = "Media Image", bool $lazy = true, string $missingImagePath = "./assets/missing_cover.png"): string {

    $imageSrc = $missingImagePath;
    $imageClass = "media-image-landscape";

    // if image_url is empty or returns error, show placeholder image
    if (!empty($imageUrl) && $imageSize !== null && $imageSize !== false) {
        if ($imageSize !== null && $imageSize !== false) {
            $width = $imageSize[0];
            $height = $imageSize[1];

            $imageSrc = htmlspecialchars($imageUrl);

            if ($width / $height > 1.2) {
                // Already set to layling
            } elseif ($height === $width) {
                $imageClass = "media-image-square";
            } else {
                $imageClass = "media-image-portrait"; // portrait
            }
        }
    }

    // Return
    return '<img src="' . $imageSrc . '" class="media-image ' . $imageClass . '" alt="' . htmlspecialchars($imageAlt) . '"' . ($lazy ? ' loading="lazy"' : '') . '>';
            
}