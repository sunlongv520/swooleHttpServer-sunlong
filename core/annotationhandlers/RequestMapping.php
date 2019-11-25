<?php
namespace Core\annotationhandlers;

use Core\annotations\RequestMapping;
use Core\BeanFactory;
use Core\init\DecoratorCollector;

return [
   RequestMapping::class=>function(\ReflectionMethod $method,$instance,$self){
            $path=$self->value;//uri
            $rquest_method=count($self->method)>0?$self->method:['GET'];
            $router_collector=BeanFactory::getBean("RouterCollector");
           $router_collector->addRouter($rquest_method,$path,function ($parms,$ext_params) use($method,$instance){
               $inputParams=[];//传入的参数
               $ref_params=$method->getParameters();//得到方法的反射参数
               foreach ($ref_params as $ref_param){
                    if(isset($parms[$ref_param->getName()]))
                        $inputParams[]=$parms[$ref_param->getName()]; //如果 路由参数中有 就置入参数集合
                    else
                    {
                        foreach ($ext_params as $ext_param){//$ext_param 都是实例对象，譬如request response,xxx,
                            if($ref_param->getClass() && $ref_param->getClass()->isInstance($ext_param)){
                                $inputParams[]=$ext_param;
                                goto  end;
                            }
                        }
                       $inputParams[]=false;
                    }
                   end:
               }
                $d_collector=BeanFactory::getBean(DecoratorCollector::class);
                return $d_collector->exec($method,$instance,$inputParams);
              //return $method->invokeArgs($instance,$inputParams);


           });
           return $instance;


   }
];