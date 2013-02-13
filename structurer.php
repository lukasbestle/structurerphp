<?php

/**
 * Structurer
 * A prototyping library to build folder structures using JSON files. Like zip but without zip.
 *
 * @version 1.1
 * @author Lukas Bestle <http://lu-x.me>
 * @link https://github.com/vis7mac/structurerphp
 * @copyright Copyright 2013 Lukas Bestle
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @file structurer.php
 */

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
		
		$this->buildStructure($output, $this->data);
		
		return true;
	}
	
	public function checkStructure($path) {
		return $this->checkValidity($this->data, $path);
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
		if(!file_exists($output)) mkdir($output);
		
		foreach($data as $name => $item) {
			$name = $output . "/" . $name;
			
			if(is_array($item)) {
				$this->buildStructure($name, $item);
			} else {
				file_put_contents($name, $item);
			}
		}
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
	
	public function checkValidity($data, $path) {
		foreach($data as $name => $item) {
			if(is_array($item)) {
				if(!is_dir($path . "/" . $name)) return false;
				if(!$this->checkValidity($item, $path . "/" . $name)) return false;
				continue;
			}
			
			if(!is_file($path . "/" . $name)) return false;
		}
		
		return true;
	}
}