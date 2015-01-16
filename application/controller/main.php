<?php
namespace Controller;

class Main extends \Core\Controller
{
	public function index()
	{
		echo 'index';
	}
	
	public function show()
	{
		$url = \Core\URI::a2p(array('demo'=>'target', 'name'=>'yuwenc', 'path_id'=>14));
		redirect($url);
	}
}