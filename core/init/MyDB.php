<?php
namespace Core\init;
use Core\annotations\Bean;
use Core\BeanFactory;
use DI\Annotation\Inject;
use Illuminate\Database\Capsule\Manager as lvdb;

/**
 * @Bean
 * @method \Illuminate\Database\Query\Builder table(string  $table,string|null  $connection=null)
 */
class MyDB{
    private $lvDB;
    private $dbSource="default";
    private $transctionDB=false;//事务DB对象

    /**
     * @var PDOPool
     */
    public $pdopool;
    /**
     * @return string
     */
    public function getDbSource(): string
    {
        return $this->dbSource;
    }

    /**
     * @param string $dbSource
     */
    public function setDbSource(string $dbSource)
    {
        $this->dbSource = $dbSource;
    }
    public function __construct($db_obj=false)
    {
        global $GLOBAL_CONFIGS;
        //default 为默认数据源
        if(isset($GLOBAL_CONFIGS['db'])){
            $configs=$GLOBAL_CONFIGS['db'];
            $this->lvDB=new lvdb();
            foreach ($configs as $key=>$value)
            {
              $this->lvDB->addConnection(["driver"=>"mysql"],$key);
            }
            $this->lvDB->setAsGlobal();
            $this->lvDB->bootEloquent();
        }
        $this->transctionDB=$db_obj;
        $this->pdopool=BeanFactory::getBean(PDOPool::class);
        if($db_obj){ //如果有值，代表事务了，则直接设置PDO对象和开启事务
            $this->lvDB->getConnection($this->dbSource)->setPdo( $this->transctionDB->db);
            $this->lvDB->getConnection($this->dbSource)->beginTransaction();
        }
    }
    public function Begin(){
        return new self($this->pdopool->getConnection());
    }
    public function Commit(){
        try{
            $this->lvDB->getConnection($this->dbSource)->commit();
        }finally{
            if( $this->transctionDB){
                $this->pdopool->close($this->transctionDB);//放回连接
                $this->transctionDB=false; //重新设置为false
            }
        }
    }
    public function Rollback(){//回滚
        try{
            $this->lvDB->getConnection($this->dbSource)->rollBack();
        }catch (\Exception $exception){
            var_dump($exception);
        }
        finally{
            if( $this->transctionDB){
                $this->pdopool->close($this->transctionDB);
                $this->transctionDB=false;
            }
        }
    }
    public function releaseConnection($pdo_object){
        if($pdo_object && !$this->transctionDB)
            $this->pdopool->close($pdo_object); //放回连接
    }
    public function genConnection()
    { //从连接池里 借出 一个连接
        $isTranstion = false;
        if ($this->transctionDB) {//代表正在事务过程中
            $pdo_object = $this->transctionDB;
            $isTranstion = true;
        } else
            $pdo_object = $this->pdopool->getConnection();
        if (!$isTranstion && $pdo_object) //只有不在事务中才需要设置pdo对象
        {
            $this->lvDB->getConnection($this->dbSource)->setPdo($pdo_object->db);
            return $pdo_object;
        }
        return false;
    }

    public function __call($methodName, $arguments)
    {
        $pdo_object=false;
        $isTranstion=false;
        if($this->transctionDB){//代表正在事务过程中
            $pdo_object=$this->transctionDB;
            $isTranstion=true;
        }else
            $pdo_object=$this->pdopool->getConnection();
        try{
            if(!$pdo_object) return [];
            if(!$isTranstion) //只有不在事务中才需要设置pdo对象
            {
                $this->lvDB->getConnection($this->dbSource)->setPdo($pdo_object->db);
            }

            $ret=$this->lvDB::connection($this->dbSource)->$methodName(...$arguments);
            return $ret;
        }catch (\Exception $exception){
            return null;
        }
        finally{
            if($pdo_object && !$isTranstion)
                $this->pdopool->close($pdo_object); //放回连接
        }

     }
}