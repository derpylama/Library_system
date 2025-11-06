<?php

function getImageSizeW(string $imageUrl) {
    try {
        return getimagesize($imageUrl);
    } catch (Exception $e) {
        return null;
    }
}

function cardSize($imageSize) {
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

function imageType($imageUrl, $imageSize) {

    // if image_url is empty or returns error, show placeholder image
    if (empty($imageUrl) || ($imageSize === null || $imageSize === false)) {
        return '<img src="images/missing_cover.png" class="media-image-layling">';
        
    } else {
        if ($imageSize !== null && $imageSize !== false) {
            $width = $imageSize[0];
            $height = $imageSize[1];

            if ($width / $height > 1.2) {// esle image missing_cover.png as placeholder
                return !empty($imageUrl) ? '<img src="'.htmlspecialchars($imageUrl).'" class="media-image-layling">' : '' ;
            } elseif ($height === $width) {
                return !empty($imageUrl) ? '<img src="'.htmlspecialchars($imageUrl).'" class="media-image-square">' : '' ;
            } else {
                return !empty($imageUrl) ? '<img src="'.htmlspecialchars($imageUrl).'" class="media-image">' : '' ;
            }
        }
    }
            
}