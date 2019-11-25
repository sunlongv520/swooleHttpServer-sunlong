<?php
namespace Core\annotations;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Redis{
    public $source="default";
    public $key="";
    public $prefix="";
    public $type="string";
    public $expire=0;
    public $incr="";//暂时只支持 hash类型
    public $score="";//这个属性是给sortedset用的
    public $member="";//sortedset专用
    public $coroutine=false;
    public $script="";//lua



}
