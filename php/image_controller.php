<?php

function cardSize($imageUrl) {
    if ($imageUrl && @getimagesize($imageUrl)) {
        $imagesize = getimagesize($imageUrl);
        if ($imagesize) {
            $width = $imagesize[0];
            $height = $imagesize[1];
    
            if ($width / $height > 1.2 || $height === $width) {
                $style = 'style="height: fit-content;"';
            }
        }
    } elseif (empty($imageUrl) || !@getimagesize($imageUrl)) {
        $style = 'style="height: fit-content;"';
    }
    return $style ?? '';
}

function imageType($imageUrl) {

    // if image_url is empty or returns error, show placeholder image
    if (empty($imageUrl) || !@getimagesize($imageUrl)) {
        echo '<img src="images/missing_cover.png" class="media-image-layling">';
        
    } else {
        $imagesize = getimagesize($imageUrl ?? '');
        if ($imagesize) {
            $width = $imagesize[0];
            $height = $imagesize[1];
            if ($width / $height > 1.2) {// esle image missing_cover.png as placeholder
                echo !empty($imageUrl) ? '<img src="'.htmlspecialchars($imageUrl).'" class="media-image-layling">' : '' ;
            } elseif ($height === $width) {
                echo !empty($imageUrl) ? '<img src="'.htmlspecialchars($imageUrl).'" class="media-image-square">' : '' ;
            } else {
                echo !empty($imageUrl) ? '<img src="'.htmlspecialchars($imageUrl).'" class="media-image">' : '' ;
            }
        }
    }
            
}