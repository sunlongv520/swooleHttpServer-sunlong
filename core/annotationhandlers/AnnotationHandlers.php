<?php
namespace Core\annotationhandlers;

use Core\annotations\Bean;
use Core\annotations\Value;


return [
    //类注解
   Bean::class=>function($instance,$container,$self){
        $vars=get_object_vars($self);//获取实例所有属性

        $beanName="";
        if(isset($vars["name"]) && $vars["name"]!=""){
            $beanName=$vars["name"];
        }
        else{
            $arrs=explode("\\",get_class($instance));
            $beanName=end($arrs);
        }

        $container->set($beanName,$instance);
   },

    //属性注解
    Value::class=>function(\ReflectionProperty $prop,$instance,$self){
        $env=parse_ini_file(ROOT_PAHT."/env");
        if(!isset($env[$self->name]) || $self->name=="") return $instance;
        $prop->setValue($instance,$env[$self->name]);
        return $instance;
    }

];