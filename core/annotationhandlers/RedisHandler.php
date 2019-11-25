<?php
namespace Core\annotationhandlers;

use Core\annotations\Redis;
use Core\BeanFactory;
use Core\init\DecoratorCollector;

use Core\lib\RedisHelper;
use Swoole\Coroutine;

function getKey(string $key,array $params){
    $pattern="/^#(\d+)/i";
    if(preg_match($pattern,$key,$matches)){
        return $params[$matches[1]];
    }
    return $key;
}
function getKeyFromData($key,array $arr){
    $pattern="/^#(\w+)/i";
    if(preg_match($pattern,$key,$matches)){
        if(isset($arr[$matches[1]]))
            return $arr[$matches[1]];
    }
    return $key;
}


function RedisByString(Redis $self,array $params,$func){
    $_key=$self->prefix.getKey($self->key,$params); //缓存key
    $getFromRedis=RedisHelper::get($_key);
    if($getFromRedis){ //缓存如果有，直接返回
        return $getFromRedis;
    }else{ //缓存没有，则直接执行原控制器方法，并返回
        $getData=call_user_func($func,...$params);
        if($self->expire>0) //过期时间
              RedisHelper::setex($_key,$self->expire,json_encode($getData));
         else
               RedisHelper::set($_key,json_encode($getData));
        return $getData;
    }
}
function RedisByHash(Redis $self,array $params,$func){
    $_key=$self->prefix.getKey($self->key,$params); //缓存key
    $getFromRedis=RedisHelper::hgetall($_key);
    if($getFromRedis){ //缓存如果有，直接返回
        if($self->incr!="")
            RedisHelper::hIncrBy($_key,$self->incr,1);
        return $getFromRedis;
    }else{ //缓存没有，则直接执行原控制器方法，并返回
        $getData=call_user_func($func,...$params);
        if(is_object($getData)){//如果是对象，转换成数组
            $getData=json_decode(json_encode($getData),1);
        }
        $dataKeys=implode("",array_keys($getData));
        if(preg_match("/^\d+$/",$dataKeys)){
            foreach ($getData as $data){
                RedisHelper::hmset($self->prefix.getKeyFromData($self->key,$data),$data);
            }
        }else{
            RedisHelper::hmset($_key,$getData); //[['prod_id'=>101,'prod_name'=>'xxxx'],['prod_id'=>102,'prod_name'=>'xxxx']]

        }

        return $getData;
    }
}
function RedisBySortedSet(Redis $self,array $params,$func){
    if($self->coroutine){ //代表是协程获取
        $chan=call_user_func($func,...$params);
        $getData=[];
        for($i=0;$i<$chan->capacity;$i++){
            $ret=$chan->pop(2);
            if(!$ret) continue;
            $getData=array_merge($getData,$ret);
        }
        if(!$getData)   return ["result"=>"error"];
        echo "使用了协程".PHP_EOL;
    }
    else
       $getData=call_user_func($func,...$params);
    if(is_object($getData)){//如果是对象，转换成数组
        $getData=json_decode(json_encode($getData),1);
    }
    foreach ($getData as $data){
        RedisHelper::zAdd($self->prefix,$data[$self->score],$self->member.$data[$self->key]);
    }
    //防错处理，请大家自行完成
    return ["result"=>"success"];
}

function RedisByLua(Redis $self,array $params,$func){
    $ret=RedisHelper::eval($self->script);
    return $ret;
}


return [
  Redis::class=>function(\ReflectionMethod $method,$instance,$self){
      $d_collector=BeanFactory::getBean(DecoratorCollector::class);
      $key=get_class($instance)."::".$method->getName();
      $d_collector->dSet[$key]=function($func) use($self){ //收集装饰器 放入 装饰器收集类
          return function($params) use($func,$self){
              /** @var $self Redis */
              if($self->script!=""){
                  return RedisByLua($self,$params,$func);
              }

                if($self->key!=""){ //处理缓存
                    switch($self->type){
                        case "string":
                            return RedisByString($self,$params,$func);
                        case "hash":
                            return RedisByHash($self,$params,$func);
                        case "sortedset":
                            return RedisBySortedSet($self,$params,$func);
                        default:
                            return call_user_func($func,...$params);
                    }
                }
              return call_user_func($func,...$params);
          };
      };
    return $instance;
  }

];