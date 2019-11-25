<?php
namespace Core\lib;
use Core\BeanFactory;
use Core\init\PHPRedisPool;

/**
 * Class RedisHelper
 * @method  static string get(string $key)
 * @method  static bool set(string $key,string $value)
 * @method  static bool setex(string $key,int $ttl,string $value)
 * @method  static array hgetall(string $key)
 * @method  static bool hmset(string $key,array $keyandvalues)
 * @method  static int hIncrBy( $key, $hashKey, $value )
 * @method  static int zAdd( $key, $score, $member )
 * @method static mixed eval($script, $args = array(), $numKeys = 0)
 * @method static float zScore($key,$member)
 * @method static float zIncrBy($key,$value,$member)
 */
class RedisHelper{


    public static function __callStatic($name, $arguments)
    {
        /** @var  $pool PHPRedisPool */
        $pool=BeanFactory::getBean(PHPRedisPool::class);
        $redis_obj=$pool->getConnection();
        try{
            if(!$redis_obj) return false;
            $redis=$redis_obj->redis;
       //  new \Redis()
            return $redis->$name(...$arguments);
        }catch (\Exception $exception){
            var_dump($exception->getMessage());
            return false;
        }finally{
            if($redis_obj)
                $pool->close($redis_obj);
        }
    }
}