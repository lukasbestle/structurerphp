<?php

// =====================================================
// You can find the demo structure in /test/testStructure
// =====================================================

// Load the library
require("../structurer.php");

// =====================
// Serialize a structure
// =====================

// The path to an existing folder
$structure = new Structurer("../test/testStructure");

// You can do different things with that structure
	// Save it as a .structure file
	// $compress: Compress the file using gzencode()? (If zlib is not available, Structurer will not encode)
	$compress = false;
	$worked = $structure->structurize("test.structure", $compress);
	
	// Get the array data
	$array = $structure->data;

	// Get the string of JSON
	$string = $structure->dataStr;
	
	// Or just use one of these to get the string of JSON:
	$string = (string)$structure;
	file_put_contents("test2.structure", $structure);
	
	// Please note that manual reading of the data string does not create gzip-compressed files as structurize() does!

// =======================
// Deserialize a structure
// =======================

// You can do that in different ways (it's all the same!):
	// Pass a string of JSON
	$destructure = new Structurer($string);
	
	// Pass an array
	$destructure = new Structurer($array);
	
	// Pass a file name (must have the extension ".structure" to get recognized)
	$destructure = new Structurer("test.structure");

// Now: Save that as a folder structure (folder must not exist)
$worked = $destructure->destructurize("newStructure");

// Overwrite an existing folder (second param: $force)
// $force == true:                Remove the old folder
// $force == false:               Add the new files in
// $force == null (or not given): Warning
$worked = $destructure->destructurize("newStructure", true);

// The structure and destructure objects are totally interchangeable
# $worked = $structure->destructurize("newStructure");
# $worked = $destructure->structurize("test.structure");