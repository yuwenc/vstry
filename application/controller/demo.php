<?php
namespace Controller;

class Demo extends \Core\Controller
{
    public function target()
    {
        var_dump(\Core\URI::p2a());
    }
}