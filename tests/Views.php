<?php

require_once sprintf('%s/index.php',dirname(__FILE__));

class Views extends \PHPUnit_Framework_TestCase{

	public function testReplaceTag(){
		$this -> assertEquals('pass',\Twist::View()->replace('{data:test}',array('test' => 'pass')));
	}

	public function testTagIf(){
		$this -> assertEquals('pass',\Twist::View()->replace("{data:test=='OK'?'pass':'fail'}",array('test' => 'OK')));
	}

	public function testTagYear(){
		$this -> assertEquals(date('Y'),\Twist::View()->replace("{date:Y}"));
	}

	public function testTagMD5(){
		$this -> assertEquals(md5('pass'),\Twist::View()->replace("{md5[data:test]}",array('test' => 'pass')));
	}

	public function testTagResource(){

		$strJqueryStatus = \Twist::View()->replace("{resource:jquery}");
		if(strstr($strJqueryStatus,'<script') && strstr($strJqueryStatus,'twist/Core/Resources/jquery/jquery-2.1.4.min.js')){
			$strJqueryStatus = 'pass';
		}

		$this -> assertEquals('pass',$strJqueryStatus);
	}

	public function testTagCSS(){

		$strTagStatus = \Twist::View()->replace("{css:twist/Core/Resources/arable/arable.min.css}");
		if(strstr($strTagStatus,'<link') && strstr($strTagStatus,'twist/Core/Resources/arable/arable.min.css')){
			$strTagStatus = 'pass';
		}

		$this -> assertEquals('pass',$strTagStatus);

		//Create an override JS file
		$this -> assertEquals('pass',sprintf('%sTwist/Core/Resources/arable/',TWIST_APP));

		mkdir(sprintf('%sTwist/Core/Resources/arable/',TWIST_APP),0777,true);

		if(is_dir(sprintf('%sTwist/Core/Resources/arable/',TWIST_APP))){
			$this -> assertEquals('pass','Created');
		}else{
			$this -> assertEquals('pass','Failed to Create');
		}

		file_put_contents(sprintf('%sTwist/Core/Resources/arable/arable.min.css',TWIST_APP),'test over-ride file');

		$strTagStatus = \Twist::View()->replace("{css:twist/Core/Resources/arable/arable.min.css}");
		if(strstr($strTagStatus,'<link') && strstr($strTagStatus,'app/Twist/Core/Resources/arable/arable.min.css')){
			$strTagStatus = 'override-pass';
		}

		$this -> assertEquals('override-pass',$strTagStatus);
	}

	public function testTagJS(){

		$strTagStatus = \Twist::View()->replace("{js:twist/Core/Resources/jquery/jquery-2.1.4.min.js}");
		if(strstr($strTagStatus,'<script') && strstr($strTagStatus,'twist/Core/Resources/jquery/jquery-2.1.4.min.js')){
			$strTagStatus = 'pass';
		}

		$this -> assertEquals('pass',$strTagStatus);

		//Create an override JS file
		mkdir(sprintf('%sTwist/Core/Resources/jquery/',TWIST_APP),0777,true);
		file_put_contents(sprintf('%sTwist/Core/Resources/jquery/jquery-2.1.4.min.js',TWIST_APP),'test over-ride file');

		$strTagStatus = \Twist::View()->replace("{css:twist/Core/Resources/jquery/jquery-2.1.4.min.js}");
		if(strstr($strTagStatus,'<script') && strstr($strTagStatus,'app/Twist/Core/Resources/jquery/jquery-2.1.4.min.js')){
			$strTagStatus = 'override-pass';
		}

		$this -> assertEquals('override-pass',$strTagStatus);
	}

	public function testTagImg(){

		$strTagStatus = \Twist::View()->replace("{img:twist/Core/Resources/twist/logos/logo.png}");
		if(strstr($strTagStatus,'<img') && strstr($strTagStatus,'twist/Core/Resources/twist/logos/logo.png')){
			$strTagStatus = 'pass';
		}

		$this -> assertEquals('pass',$strTagStatus);

		$strTagStatus = \Twist::View()->replace("{img:twist/Core/Resources/twist/logos/logo.png,id=test1}");
		if(strstr($strTagStatus,'<img') && strstr($strTagStatus,'twist/Core/Resources/twist/logos/logo.png') && strstr($strTagStatus,' id="test1"')){
			$strTagStatus = 'param-pass';
		}

		$this -> assertEquals('param-pass',$strTagStatus);

		//Create an override JS file
		mkdir(sprintf('%sTwist/Core/Resources/twist/logos/',TWIST_APP),0777,true);
		file_put_contents(sprintf('%sTwist/Core/Resources/twist/logos/logo.png',TWIST_APP),'test over-ride file');

		$strTagStatus = \Twist::View()->replace("{css:twist/Core/Resources/twist/logos/logo.png}");
		if(strstr($strTagStatus,'<link') && strstr($strTagStatus,'app/Twist/Core/Resources/twist/logos/logo.png')){
			$strTagStatus = 'override-pass';
		}

		$this -> assertEquals('override-pass',$strTagStatus);
	}
}