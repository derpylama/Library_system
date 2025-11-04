<?php







// for barcodes
function getFiveLetters($input) {
    // Remove all characters except letters a–z and digits 0–9
    $filtered = preg_replace('/[^a-z0-9]/i', '', $input);
    // Convert to uppercase
    $filtered = strtoupper($filtered);
    // Take the first 5 characters
    return substr($filtered, 0, 5);
}



//use function if titel does not have an existing barcode


function generateBarcode($titel, $barcodes) {
    $base = getFiveLetters($titel);
    $newbarcode = $base;

    // Helper function to generate suffix sequences like A..Z, AA..ZZ, etc.
    $letters = range('A', 'Z');
    $suffix = '';

    while (in_array($newbarcode, $barcodes)) {
        // If suffix is empty, start with A; else increment like base26
        if ($suffix === '') {
            $suffix = 'A';
        } else {
            // increment the suffix like Excel columns
            $i = strlen($suffix) - 1;
            while ($i >= 0) {
                if ($suffix[$i] !== 'Z') {
                    $suffix[$i] = chr(ord($suffix[$i]) + 1);
                    break;
                } else {
                    $suffix[$i] = 'A';
                    $i--;
                }
            }
            if ($i < 0) {
                // overflow, add new letter in front
                $suffix = 'A' . $suffix;
            }
        }

        $newbarcode = $base . '_' . $suffix;
    }

    return $newbarcode;
}


//takes first five letters or numbers and creates a unique barcode 
// Example usage:
$existingBarcodes = ['HARRY', 'HARRY_A', 'HARRY_B', 'DURD0'];
echo ("<pre>");
echo generateBarcode("harry potter", $existingBarcodes) . "\n"; // HARRY_C
echo generateBarcode("du är_ d0m", $existingBarcodes) . "\n";   // DURD0_A
echo generateBarcode("disahgdiuhas", $existingBarcodes) . "\n";  //DISAH
echo ("</pre>");
?>





