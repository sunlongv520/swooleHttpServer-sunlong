<?php
namespace Core\annotations;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Lock{
    public $prefix="";
    public $key="";
    public $retry=3;//重试次数
}