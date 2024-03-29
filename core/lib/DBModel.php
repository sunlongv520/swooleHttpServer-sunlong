<?php
namespace Core\lib;
use Core\BeanFactory;
use Core\init\MyDB;
use Illuminate\Database\Eloquent\Model;

class DBModel extends Model{

    public function __call($method, $parameters)
    {
        return $this->invoke(function() use($method, $parameters){
            return parent::__call($method, $parameters); // TODO: Change the autogenerated stub
        });
    }
    public function save(array $options = [])
    {
        return $this->invoke(function() use($options){
            return parent::save($options);
        });

    }
    public static function all($columns = ['*']) //all这个方法 原本就有，因此不会执行__call,需要手动添加
    {
        return self::invokeStatic(function() use($columns){
            return parent::all($columns);
        });

    }
    private static function invokeStatic(callable  $func){
        $mydb=clone BeanFactory::getBean(MyDB::class);
        $obj=$mydb->genConnection();
        try{
            return $func();
        }catch (\Exception $exception){
            var_dump($exception);
            return null;
        }finally{
            $mydb->releaseConnection($obj);
        }
    }
    private function invoke(callable  $func){
        $mydb=clone BeanFactory::getBean(MyDB::class);
        $obj=$mydb->genConnection();
        try{
            return $func();
        }catch (\Exception $exception){
            var_dump($exception);
            return null;
        }finally{
            $mydb->releaseConnection($obj);
        }
    }


}