<?php

class Searcher {

	private $ignoreList = null;

	function __construct(){
		$this->ignoreList = require_once('IgnoreWords.php');
	}

	function getMatchRows($q){

	}

}