<?php

/**
 * Structurer
 * A prototyping library to build folder structures using JSON files. Like zip but without zip.
 *
 * @version 1.1.1
 * @author Lukas Bestle <http://lu-x.me>
 * @link https://github.com/vis7mac/structurerphp
 * @copyright Copyright 2013 Lukas Bestle
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @file structurer.php
 */

define("STRUCTURER_ADDED", 1);
define("STRUCTURER_DELETED", 2);
define("STRUCTURER_CHANGED", 4);
define("STRUCTURER_EVERYTHING", STRUCTURER_ADDED | STRUCTURER_DELETED | STRUCTURER_CHANGED);

class Structurer {
	public $data = array();
	public $dataStr = "{}";
	
	public function __construct($data) {
		if(is_string($data) && file_exists($data)) {
			if(is_dir($data)) {
				$this->parseStructureFrom($data);
			} else {
				$this->loadStructureFile($data);
			}
		} else if(is_array($data) || (is_string($data)) && $this->is_JSON($data)) {
			$this->setData($data);
		} else {
			throw new InvalidArgumentException("Could not match your request.");
		}
	}
	
	// ================
	// Public functions
	public function structurize($filename, $compress=true) {
		if(function_exists("gzencode") && $compress) {
			$data = gzencode($this->dataStr, 9);
		} else {
			$data = $this->dataStr;
		}
		
		return (file_put_contents($filename, $data) != false);
	}
	
	public function destructurize($output, $force = null) {
		if(file_exists($output)) {
			if($force === true) {
				$this->rrmdir($output);
			} else if($force === null) {
				throw new Exception('The folder ' . realpath($output) . ' already exists. Please remove it or use $force');
			}
		}
		
		return $this->buildStructure($output, $this->data) && $this->checkStructure($output);
	}
	
	public function checkStructure($path, $bitmask=STRUCTURER_DELETED) {
		return new StructurerScore($this->checkValidity($this->data, $path, $bitmask));
	}
	
	// ===========
	// OOP Helpers
	public function __toString() {
		return $this->dataStr;
	}
	
	// ===============
	// Private helpers
	private function parseStructureFrom($folder) {
		$this->setData($this->getStructure($folder));
	}
	
	private function loadStructureFile($file) {
		if(!file_exists($file) || !is_file($file)) throw new RuntimeException("Wrong method call.");
		
		$data = file_get_contents($file);
		if(!$this->is_JSON($data)) {
			if(!function_exists("gzdecode")) {
				throw new Exception("The contents of $file seem to be gzip encoded, but gzdecode() is not available.");
			}
			
			$data = gzdecode($data);
			
			if($data == false) {
				throw new Exception("The contents of $file are invalid.");
			}
		}
		
		$this->setData($data);
	}
	
	private function setData($data) {
		if(is_array($data)) {
			$this->data = $data;
		} else if(is_string($data)) {
			if(!$this->is_JSON($data)) throw new Exception("Invalid JSON.");

			$this->data = json_decode($data, true);
		} else {
			throw new RuntimeException("Wrong method call.");
		}
		
		$this->dataStr = json_encode($this->data);
	}
	
	private function is_JSON($data) {
		return !(json_decode($data) == null);
	}
	
	private function rrmdir($dir) {
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
	
	private function buildStructure($output, $data) {
		if(!file_exists($output)) {
			if(!mkdir($output)) return false;
		}
		
		foreach($data as $name => $item) {
			$name = $output . "/" . $name;
			
			if(is_array($item)) {
				$this->buildStructure($name, $item);
			} else {
				if(!file_put_contents($name, $item)) return false;
			}
		}
		
		return true;
	}
	
	private function getStructure($folder) {
		$data = array();
		
		foreach(scandir($folder) as $item) {
			$path = $folder . "/" . $item;
			
			if($this->is_dot($item)) continue;
			
			if(is_file($path)) {
				$data[$item] = file_get_contents($path);
			} else {
				$data[$item] = $this->getStructure($path);
			}
		}
		
		return $data;
	}
	
	private function is_dot($file) {
		return ($file == "." || $file == "..");
	}
	
	public function checkValidity($data, $path, $bitmask) {
		$score = 0;
		foreach($data as $name => $item) {
			if(is_array($item)) {
				if(($bitmask & STRUCTURER_DELETED) && !is_dir($path . "/" . $name)) $score++;
				$score += $this->checkValidity($item, $path . "/" . $name, $bitmask);
				continue;
			}
			
			if(($bitmask & STRUCTURER_DELETED) && !is_file($path . "/" . $name)) $score++;
			
			if(($bitmask & STRUCTURER_CHANGED) && is_file($path . "/" . $name) && file_get_contents($path . "/" . $name) != $item) $score++;
		}
		if($bitmask & STRUCTURER_ADDED) {
			foreach(scandir($path) as $file) {
				if($this->is_dot($file)) continue;
				if(!isset($data[$file])) $score++;
			}
		}
		
		return $score;
	}
}

class StructurerScore {
	public $score;
	public $bool;
	
	public function __construct($score) {
		$this->bool = ($score == 0)? true : false;
		$this->score = $score;
	}
}