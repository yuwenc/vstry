<?php
namespace Model;

class User extends \Model\Master
{
    public static $table = 'vs_user';
    public static $key = 'user_id';
    
    public static $has_to = array(
        'account'=>array('model'=>'\Model\Account', 'relation_key'=>'user_id'),
    );
}