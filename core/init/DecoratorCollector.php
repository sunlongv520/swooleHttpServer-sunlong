<?php
namespace Core\init;
use Core\annotations\Bean;

/**
 * Class DecoratorCollector   装饰器收集类
 * @Bean()
 */
class DecoratorCollector{
        public $dSet=[];

        public function  exec(\ReflectionMethod $method,$instance,$inputParams){
            $key=get_class($instance)."::".$method->getName();
            if(isset($this->dSet[$key])){
                $func=$this->dSet[$key];
                return $func($method->getClosure($instance))($inputParams);//装饰执行
            }
            return $method->invokeArgs($instance,$inputParams);//原样执行
        }
}