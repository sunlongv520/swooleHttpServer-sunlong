<?php
namespace Core\annotationhandlers;


use Core\annotations\Lock;
use Core\BeanFactory;
use Core\init\DecoratorCollector;
use Core\lib\RedisHelper;
function getLock(Lock $self,$params){
    $script = <<<LUA
            local key = KEYS[1]
            return redis.call('setnx', key, 1)
LUA;
    return RedisHelper::eval($script,[$self->prefix.getKey($self->key,$params)],1);
}
function delLock(Lock $self,$params){
    $script = <<<LUA
            local key = KEYS[1]
             return redis.call('del',key)
LUA;
    return RedisHelper::eval($script,[$self->prefix.getKey($self->key,$params)],1);
}
function lock(Lock $self,$params){
    $retry=$self->retry;
    while($retry-->0){
        $getLock=getLock($self,$params);
        if($getLock){
            return true;
        }
        usleep(1000*10*1);//休眠100毫秒
    }
    return false;
}
function run($self,$params,$func){
    try{
        if(lock($self,$params)){ //获取锁成功
            $ret=call_user_func($func, ...$params);
            delLock($self,$params);
            return $ret;
        }
        return false;

    }catch (\Exception $exception){
        delLock($self,$params);
        return false;
    }
}



return [
   Lock::class=>function(\ReflectionMethod $method,$instance,$self){
       $d_collector=BeanFactory::getBean(DecoratorCollector::class);
       $key=get_class($instance)."::".$method->getName();
       $d_collector->dSet[$key]=function($func) use($self){ //收集装饰器 放入 装饰器收集类
           return function($params) use($func,$self) {
               /** $self Lock */
               if($self->key!=""){
                   $ret=run($self,$params,$func);
                   if($ret===false){
                       return ["data locked"];
                   }
                   else
                   return $ret;
               }
               return call_user_func($func, ...$params);
           };

       };
       return $instance;
   }

];