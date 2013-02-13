<?php

require_once('structurer.php');

class StructurerTest extends PHPUnit_Framework_TestCase {
	private $testData = "{}";
	
	public function tearDown() {
		$this->rrmdir("demo/newStructure");
		$this->rrmdir("demo/test.structure");
		$this->rrmdir("demo/test2.structure");
	}
	
	public function __construct() {
		$this->testData = file_get_contents("test/test.structure");
		$this->testDataCompressed = gzencode($this->testData, 9);
	}
	
	public function testShouldLoadStructureAndSerializeIt() {
		$structure = new Structurer("test/testStructure");
		
		$this->assertEquals($this->testData, $structure->dataStr);
		$this->assertEquals($this->testData, (string)$structure);
		$this->assertEquals(json_decode($this->testData, true), $structure->data);
		
		$this->assertTrue($structure->structurize("demo/test.structure", false));
		$this->assertEquals($this->testData, file_get_contents("demo/test.structure"));
		
		$this->assertTrue($structure->structurize("demo/test2.structure"));
		$this->assertEquals($this->testDataCompressed, file_get_contents("demo/test2.structure"));
	}
	
	public function testShouldDeserializeStructure() {
		$destructure1 = new Structurer($this->testData);
		$destructure2 = new Structurer(json_decode($this->testData, true));
		$destructure3 = new Structurer("test/test.structure");
		
		$this->assertEquals($this->testData, $destructure1->dataStr);
		$this->assertEquals($this->testData, $destructure2->dataStr);
		$this->assertEquals($this->testData, $destructure3->dataStr);
		
		$this->assertEquals(json_decode($this->testData, true), $destructure1->data);
		$this->assertEquals(json_decode($this->testData, true), $destructure2->data);
		$this->assertEquals(json_decode($this->testData, true), $destructure3->data);
		
		$this->assertTrue($destructure1->destructurize("demo/newStructure"));
		
		$success = false;
		try {
			$destructure1->destructurize("demo/newStructure");
		} catch(Exception $e) {
			$success = true;
		}
		$this->assertTrue($success);
		
		$this->assertTrue($destructure1->destructurize("demo/newStructure", true));
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
}