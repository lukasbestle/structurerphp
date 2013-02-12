<?php

// =====================================================
// You can find the demo structure in test/testStructure
// =====================================================

// Load the library
require("structurer.php");

// =====================
// Serialize a structure
// =====================

// The path to an existing folder
$structure = new Structurer("test/testStructure");

// You can do different things with that structure
	// Save it as a .structure file
	$worked = $structure->structurize("test.structure");
	
	// Get the array data
	$array = $structure->data;

	// Get the string of JSON
	$string = $structure->dataStr;
	
	// Or just use one of these to get the string of JSON:
	$string = (string)$structure;
	file_put_contents("test2.structure", $structure);

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

// Now: Save that as a folder structure (folder must not exist or will be overwritten)
$worked = $destructure->destructurize("newStructure");

// The structure and destructure objects are totally interchangeable
# $worked = $structure->destructurize("newStructure");
# $worked = $destructure->structurize("test.structure");