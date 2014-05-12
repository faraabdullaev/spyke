<?php

class Spider{

	function Spider(){
		echo 'create new Spider';
	}

	/** create or find model */
	function getEntityID(){
		return;
	}

	/** add one page to index */
	function addToIndex($url){
		echo 'Индексируется ' . $url;
	}

	/** clear HTML text */
	function getTextOnly($html){
		return;
	}

	/** separate text to words */
	function separateWords($text){
		return;
	}

	/** if this URL in index return TRUE */
	function isIndexed($url){
		return false;
	}

	/** all URL from one page to other */
	function addLinkRef($from, $to, $text){
		return;
	}

	function run(){
		return;
	}

}