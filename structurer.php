<?php

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
	public function structurize($filename) {
		return (file_put_contents($filename, $this->dataStr) != false);
	}
	
	public function destructurize($output, $force = false) {
		if(file_exists($output)) {
			if($force) {
				$this->rrmdir($output);
			} else {
				throw new Exception('The folder ' . realpath($output) . ' already exists. Please remove it or use $force');
			}
		}
		
		$this->buildStructure($output, $this->data);
		
		return true;
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
		mkdir($output);
		
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
}