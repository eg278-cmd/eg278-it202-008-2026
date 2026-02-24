<?php
// copilot: disable
// @ts-nocheck
require_once "base.php";

$ucid = "eg278"; // <-- set your ucid

// Don't edit the arrays below, they are used to test your code
$array1 = ["hello world!", "php programming", "special@#$%^&characters", "numbers 123 456", "mIxEd CaSe InPut!"];
$array2 = ["hello world", "php programming", "this is a title case test", "capitalize every word", "mixEd CASE input"];
$array3 = ["  hello   world  ", "php    programming  ", "  extra    spaces  between   words   ",
    "      leading and trailing spaces      ", "multiple      spaces"];
$array4 = ["hello world", "php programming", "short", "a", "even"];


function transformText($arr, $arrayNumber) {
    echo "<div class='problem-item'>";
    // Only make edits between the designated "Start" and "End" comments
    printScenario4ArrayInfo($arr, $arrayNumber);
    // This should be solved without Copilot auto-completion, to toggle it, click the Copilot chat bubble at the top of the editor.
    //  Configure inline suggestions to "Disabled Inline Suggestions" (or similar) when writing code for this problem.
   
    // Challenge 1: Remove non-alphanumeric characters except spaces
    // Challenge 2: Convert text to Title Case
    // Challenge 3: Remove leading/trailing spaces and remove duplicate spaces between words
    // Result 1-3: Assign final phrase to `placeholderForModifiedPhrase`
    // Challenge 4 (extra credit): Extract up to middle 3 characters (beginning starts at middle of phrase, exclude the first and last character for shorter phrases, effectively middle index +/- 1)
    // Assign result to 'placeholderForMiddleCharacters'
    // If not enough characters in a word, instead assign "Not enough characters" to `placeholderForMiddleCharacters`

    $placeholderForModifiedPhrase = "";
    $placeholderForMiddleCharacters = "";
    foreach ($arr as $index => $text) {
        // Start Solution Edits
        // Plan (eg278  2/3/26)
        // 1. Remove non-alphanumeric characters except letters, numbers, and spaces.
        // 2. Convert text to Title Case.
        // 3. Remove trailing spaces.
        // 4. Replace duplicate spaces with a single space.
        // 5. Assign final result to $placeholderForModifiedPhrase.

        $stepA = preg_replace('/[^0-9a-zA-Z ]/', '', $phrase);

        $stepB = ucwords(strtolower($stepA));

        $stepC = preg_replace('/\s+/', '', $stepB);
        $stepC = trim($stepC);

        $placeholderForModifiedPhrase = $stepC;

        // End Solution Edits
    
        printScenario4Transformations($index, $placeholderForModifiedPhrase, $placeholderForMiddleCharacters);
        
    }

    echo "</div>";
}

// Run the problem

echo "<div class='scenario4-grid'>";
transformText($array1, 1);
transformText($array2, 2);
transformText($array3, 3);
transformText($array4, 4);
echo "</div>";
printFooter($ucid, 4);

?>