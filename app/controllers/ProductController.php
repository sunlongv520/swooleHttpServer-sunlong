<?php
namespace App\controllers;

use App\models\Products;
use Core\annotations\Bean;
use Core\annotations\Lock;
use Core\annotations\Redis;
use Core\annotations\RequestMapping;
use Core\http\Request;
use Core\lib\Context;
use Core\lib\RedisHelper;
use Swoole\Coroutine;

/**
 * 商品控制器
 * @Bean()
 */
class ProductController{

    /**
     * @RequestMapping(value="/prod/test")
     */
    public function test(){
        //select  *from products limit 5 offset 5
        $prods=Products::take(5)->skip(5)->get()->toArray();
        var_dump($prods);
        return $prods;
    }
    /**
     * @Redis(prefix="hproduct",key="#prod_id",type="hash")
     * @RequestMapping(value="/prod/preheat")
     */
    public function preheat(){
        $prods=Products::all();
         return $prods;
    }
    /**
     * @Redis(prefix="stock",key="prod_id",member="prod",score="prod_stock",type="sortedset",coroutine=true)
     * @RequestMapping(value="/prod/stock")
     */
    public function stock(){
        $chan=new Coroutine\Channel(3);
        $pagesize=5;
        for($i=0;$i<3;$i++){
            go(function () use($chan,$pagesize,$i){
                $prods=Products::take($pagesize)->skip($i*$pagesize)->get()->toArray();
                $chan->push($prods);
            });
        }
        return $chan;


    }
    /**
     * @Redis(script="
        return redis.call('get','name');
           ")
     * @RequestMapping(value="/prod/script")
     */
    public function testscript(){

    }

    /**
     * @Lock(prefix="lockprod",key="#0")
     * @RequestMapping(value="/prod/buy/{pid:\d+}")
     */
    public function buy(int $pid,Request $request){
        $key="stock";
        $member="prod".$pid;
        $prodStock=RedisHelper::zScore($key,"prod".$pid);
        if($prodStock && $prodStock>0){
            //发生了卡顿
            $get=$request->getQueryParams();
            if(isset($get["delay"]))
                sleep(5);

            $newValue=RedisHelper::zIncrBy($key,-1,$member);
            return $newValue;
        }
        return 0;
    }


}