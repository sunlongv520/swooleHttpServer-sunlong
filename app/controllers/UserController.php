<?php
namespace App\controllers;
use App\models\Users;
use Core\annotations\Bean;
use Core\annotations\DB;
use Core\annotations\Redis;
use Core\annotations\RequestMapping;
use Core\annotations\Value;
use Core\http\Request;
use Core\http\Response;
use Core\init\MyDB;
use DI\Annotation\Inject;
/**
 * @Bean(name="user")
 */
class UserController{

    /**
     * @DB(source="default")
     * @var MyDB
     */
    private $db1;

    /**
     * @DB(source="abc")
     * @var MyDB
     */
    private $db2;


    /**
     * @Redis
     * @RequestMapping(value="/test")
     */
    public function test(){

         return Users::find(1);
    }
    /**
     *
     * @RequestMapping(value="/test2")
     */
    public function test2(){
        return Users::find(1);
    }
    /**
     * @RequestMapping(value="/test3")
     */
    public function test3(){
        $tx=$this->db1->Begin();
        $newUser=new Users();
        $newUser->user_name="abc";
        $newUser->user_score=123;
        $newUser->save(); //注意这个方法是 Model本身有的 所以不会执行_call魔术方法
        sleep(6);
        $tx->Rollback();
        return ["test3"];
    }


    /**
     * @Value(name="version")
     */
    public $version;
    /**
     * @Redis(key="#1",prefix="husers",expire="30",type="hash",incr="usercount")
     * @RequestMapping(value="/user/{uid:\d+}")
     */
    public function user(Request $r,int $uid,Response $response){
          return Users::find($uid);
    }


}