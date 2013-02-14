#!/usr/bin/php
<?php

require("../structurer.php");

if(posix_isatty(STDOUT)) echo "\033[0;32mstructurer \033[0;34mv1.2.1\033[0m\n\n";

if(!isset($argv[1])) {
	// Home screen
	
	echo "Usage: \033[0;32mstructurer \033[0;34m<command>\033[0m\n\nwhere \033[0;34m<command>\033[0m is one of:\n\n";
	
	echo "  \033[0;34mstructurize     \033[0m[-c] [PATH]                              Pack a folder and put save it as <DIRNAME>.structure.\n";
	echo "  \033[0;34mdestructurize   \033[0mFILENAME.structure [PATH]                Unpack a .structure file to <PATH>.\n";
	echo "  \033[0;34mcheck           \033[0mFILENAME.structure PATH [-c] [-d] [-a]   Check if a path fits a .structure file.\n\n";
	
	echo "You can find more information under \033[0;34m\033[4mhttps://github.com/vis7mac/structurer\033[0m.\n";
} else if($argv[1] == "structurize") {
	// Structurize
	
	$argvClean = array_slice($argv, 2);
	
	$compress = false;
	foreach($argvClean as $arg) {
		if(substr($arg, 0, 1) == "-") {
			if($arg == "-c") {
				$compress = true;
			}
		} else {
			$path = $arg;
		}
	}
	
	if(isset($path)) {
		if(is_dir($path)) {
			$dir = realpath($path);
		} else {
			die("\033[0;31m!! Could not find '" . $path . "'!\033[0m\n");
		}
	} else {
		$dir = getcwd();
	}
	
	if(!is_string($dir)) die("\033[0;31m!! Could not get current working directory.\033[0m\n");
	
	$structure = new Structurer($dir);
	
	if(posix_isatty(STDOUT)) {
		// No redirection
		
		$filename = basename($dir) . ".structure";
		
		$structure->structurize($filename, $compress);
		echo "\033[0;32mSuccessfully saved the contents of \033[0;34m$dir\033[0;32m to \033[0;34m$filename\033[0;32m.\n";
	} else {
		if(function_exists("gzencode") && $compress) {
			echo gzencode($structure, 9);
			die(0);
		}
		
		echo $structure;
	}
} else if($argv[1] == "destructurize") {
	// Destructurize
	
	if(!isset($argv[2]) || substr($argv[2], -10) != ".structure" || !file_exists($argv[2])) {
		die("\033[0;31m!! Please provide a valid .structure file!\033[0m\n");
	}
	
	$structure = new Structurer($argv[2]);
	
	if(isset($argv[3])) {
		$path = $argv[3];
	} else {
		$path = getcwd();
	}
	
	if(!is_string($path)) die("\033[0;31m!! Could not get current working directory.\033[0m\n");
	
	$structure->destructurize($path, false);
	
	echo "\033[0;32mSuccessfully saved the contents of \033[0;34m" . $argv[2] . "\033[0;32m to \033[0;34m$path\033[0;32m.\n";
} else if($argv[1] == "check") {
	// Check
	
	$argvClean = array_slice($argv, 2);
	$args = array("bitmask" => STRUCTURER_DELETED);
	foreach($argvClean as $arg) {
		if(substr($arg, 0, 1) == "-") {
			switch($arg) {
				case "-c":
					$args["bitmask"] |= STRUCTURER_CHANGED;
					break;
				case "-d":
					break;
				case "-a":
					$args["bitmask"] |= STRUCTURER_ADDED;
			}
		} else if(substr($arg, -10) == ".structure" && file_exists($arg)) {
			$args["structureFile"] = $arg;
		} else {
			$args["path"] = $arg;
		}
	}
	
	if(!isset($args["structureFile"])) {
		die("\033[0;31m!! Please provide a valid .structure file!\033[0m\n");
	}
	
	if(!isset($args["path"]) || !is_dir($args["path"])) {
		die("\033[0;31m!! Please provide a dir to check!\033[0m\n");
	}
	
	$structure = new Structurer($args["structureFile"]);
	
	if($structure->checkStructure($args["path"], $args["bitmask"])->bool) {
		echo "\033[0;34m" . $args["structureFile"] . "\033[0;32m fits to \033[0;34m" . $args["path"] . "\033[0;32m.\n";
	} else {
		echo "\033[0;34m" . $args["structureFile"] . "\033[0;31m does not fit to \033[0;34m" . $args["path"] . "\033[0;31m.\n";
	}
} else {
	echo "\033[0;31m!! Command not found! Please use \033[0;32mstructurer\033[0;31m for help.\033[0m\n";
}

function rrmdir($dir) {
	if(is_dir($dir)) {
		$objects = scandir($dir);
		
		foreach ($objects as $object) {
			if($object != "." && $object != "..") {
				if(filetype($dir . "/" . $object) == "dir") {
					$this->rrmdir($dir . "/" . $object);
				} else {
					unlink($dir . "/" . $object);
				}
			}
		}
		
		reset($objects);
		rmdir($dir);
		
		return true;
	} else if(is_file($dir)) {
		unlink($dir);
		return true;
	} else {
		return false;
	}
}