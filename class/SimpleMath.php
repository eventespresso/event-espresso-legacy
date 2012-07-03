<?php 
class SimpleMath {
	var $a;
	var $b;
	var $math;
	
	public function add($a, $b){
		$math = $a + $b;
		return $math;
	}
	public function subtract($a, $b){
		$math = $a - $b;
		return $math;
	}
	public function multiply($a, $b){
		$math = $a * $b;
		return $math;
	}
	public function divide($a, $b){
		$math = $a / $b;
		return $math;
	}
}