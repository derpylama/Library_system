<?php

function skip(){
    return [1,2];
}

function cardSize($imageUrl) {
    if ($imageUrl && @skip($imageUrl)) {
        $imagesize = skip($imageUrl);
        if ($imagesize) {
            $width = $imagesize[0];
            $height = $imagesize[1];
    
            if ($width / $height > 1.2 || $height === $width) {
                $style = 'style="height: fit-content;"';
            }
        }
    } elseif (empty($imageUrl) || !@skip($imageUrl)) {
        $style = 'style="height: fit-content;"';
    }
    return $style ?? '';
}

function imageType($imageUrl) {

    // if image_url is empty or returns error, show placeholder image
    if (empty($imageUrl) || !@skip($imageUrl)) {
        return '<img src="images/missing_cover.png" class="media-image-layling">';
        
    } else {
        $imagesize = skip($imageUrl ?? '');
        if ($imagesize) {
            $width = $imagesize[0];
            $height = $imagesize[1];
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