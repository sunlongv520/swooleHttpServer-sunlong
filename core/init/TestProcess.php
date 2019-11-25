<?php
namespace Core\init;

use Core\BeanFactory;
use Core\helper\FileHelper;
use Swoole\Process;

class TestProcess{
    private  $md5file;
    public function run(){
        return new Process(function(){
            while(true){
                sleep(3);

                $md5_value=FileHelper::getFileMd5(ROOT_PAHT."/app/*","/app/config");
                if($this->md5file==""){
                    $this->md5file=$md5_value;
                    continue;
                }
                if(strcmp($this->md5file,$md5_value)!==0){ //代表文件有改动
                    echo "reloading....".PHP_EOL;
                    $getpid=intval(file_get_contents("./Wolf.pid"));
                    Process::kill($getpid,SIGUSR1);
                    $this->md5file=$md5_value;
                    echo "reloaded".PHP_EOL;
                }

            }
        });
    }
}