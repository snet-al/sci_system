<?php 
class SCI_Template {
	public $owner;
	public $tpl;
	
	public function __construct($owner,$tpl_engine){
		$this->owner=$owner;
		require $tpl_engine.'/Autoloader.php';
		Mustache_Autoloader::register();
		$this->tpl=new Mustache_Engine;
	}
	public function load($text,$data){
		return $this->tpl->render($text,$data);
	}
}
?>