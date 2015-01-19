<?php
namespace Controller;

class Main extends \Core\Controller
{
	public function index()
	{
		echo view('main/index.php');
	}
}