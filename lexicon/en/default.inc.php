<?php
/**
 * Default English Lexicon Entries
 * 
 */

// Determine package
$packageKey = basename(dirname(__FILE__,3));

// Start language keys
$_lang[$packageKey] = ucfirst($packageKey);

// Create additional keys as needed using the dynamic packageKey value
// ex: $_lang["{$packageKey}.err.some_error"] = 'Some error has occurred.';