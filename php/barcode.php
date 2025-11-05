<?php






// MARK:  Barcode Generation Helpers
// for barcodes helper
function getFiveLetters($input) {
    // Remove all characters except letters a–z and digits 0–9
    
    
    $input=mb_strtolower($input, 'UTF-8');

    //replace åäö with AAO
    $input = str_replace(
        ['å', 'ä', 'ö'],
        ['a', 'a', 'o'],
        $input
    );

    $filtered = preg_replace('/[^a-z0-9]/i', '', $input);
    // Convert to uppercase
    $filtered = strtoupper($filtered);
    // Take the first 5 characters
    return substr($filtered, 0, 5);
}



//use function if titel does not have an existing barcode

// MARK:  Unique Barcode Generator
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
/*

$existingBarcodes = ['HARRY', 'HARRY_A', 'HARRY_B', 'DURD0'];
echo ("<pre>");
echo generateBarcode("harry potter", $existingBarcodes) . "\n"; // HARRY_C
echo generateBarcode("du är_ d0m", $existingBarcodes) . "\n";   // DURD0_A
echo generateBarcode("disahgdiuhas", $existingBarcodes) . "\n";  //DISAH
echo ("</pre>");

*/

// MARK:  Copy Barcode Generator
//fills empty copies slots ex copies [1,3]exist and you want 2 new copies it will return copies [2,4]
function BarcodesForCopy($amount, $existingCopies, $barcode) {
    //amount: amount of copies to generate barcodes for 
    //existingCopies: array of all existing copies of that media 
    //barcode: barcode of that media you are adding copies to
    $newBarcodes = [];

    // Collect all existing copy numbers for this barcode
    $existingNums = [];
    foreach ($existingCopies as $copy) {
        if (preg_match('/^' . preg_quote($barcode, '/') . '_(\d+)$/', $copy, $matches)) {
            $existingNums[] = intval($matches[1]);
        }
    }

    // Find the next available numbers, filling gaps first
    $nextNum = 1;
    while (count($newBarcodes) < $amount) {
        if (!in_array($nextNum, $existingNums)) {
            $newBarcodes[] = $barcode . '_' . $nextNum;
        }
        $nextNum++;
    }

    return $newBarcodes;
}


//  Example: barcodecopies
/* 
$existingCopies = ['HARRY_A_3','HARRY_A_1'];
print_r(BarcodesForCopy(2, $existingCopies, 'HARRY_A'));
 */

// Output:
// Array
// (
//     [0] => HARRY_A_2
//     [1] => HARRY_A_4
// )


/* //MARK: FULL TEST

//Full test 
$existingCopies2 = ['HARRY_C_3','HARRY_C_1','HARRY_C_17'];
$existingBarcodes2 = ['HARRY', 'HARRY_A', 'HARRY_B', 'DURD0'];
$amount = 20;




print_r(BarcodesForCopy($amount, $existingCopies2,generateBarcode("härry potter", $existingBarcodes2)));
// Array ( [0] => HARRY_C_2 [1] => HARRY_C_4 )


?> */







