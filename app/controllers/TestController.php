<?php
namespace App\controllers;
use App\models\Users;
use Core\annotations\Bean;
use Core\annotations\DB;
use Core\annotations\RequestMapping;
use Core\annotations\Value;
use Core\http\Request;
use Core\http\Response;
use Core\init\MyDB;
use DI\Annotation\Inject;


/**
 * @Bean(name="test")
 */
class TestController{

    /**
     * @DB(source="default")
     * @var MyDB
     */
    private $db1;

    /**
     * @RequestMapping(value="/abc")
     */
    public function test(){
       return $this->db1->teststr;
        // return Users::find(1);
    }




}