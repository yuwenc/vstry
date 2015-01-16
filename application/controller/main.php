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
//		$user = new \Model\User(1112);
//		$user->load();
		//var_dump($user);
//		var_dump($user->account);exit();
		/*
		foreach ($user->account as $account)
		{
		$account->create_time = W_START_TIME;
		$account->save();
		}
		*/
//	    $rs = \Model\User::count(array('user_id > 1044'));
//	    var_dump($rs);

        $rs = \Model\User::row(array('user_id'=>1110));
        var_dump($rs);
	}
}