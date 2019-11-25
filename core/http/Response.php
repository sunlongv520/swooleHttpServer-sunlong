<?php
namespace Core\http;
use function PHPSTORM_META\type;

class  Response{
    /**
     * @var \Swoole\Http\Response
     */
    protected $swooleReponse;
    protected $body;


    public function __construct($swooleReponse)
    {
        $this->swooleReponse = $swooleReponse;
        $this->setHeader("Content-Type","text/plain;charset=utf8");
    }
    public static function init(\Swoole\Http\Response $response)
    {
         return new self($response);
    }
    public function getBody()
    {
        return $this->body;
    }
    public function setBody($body)
    {
        $this->body = $body;
    }
    public function setHeader($key,$value){
        $this->swooleReponse->header($key,$value);
    }
    public function writeHttpStatus(int $code){
        $this->swooleReponse->status($code);
    }
    public function writeHtml($html){
        $this->swooleReponse->write($html);
    }
    public function redirect($url,$code=301)
    {
       // $this->swooleReponse->redirect($url);
        $this->writeHttpStatus($code);
        $this->setHeader("Location",$url);
    }


    public function end(){


        $json_convert=['array',"object"];
        $ret=$this->getBody();

        if(in_array(gettype($ret),$json_convert)){
            $this->swooleReponse->header("Content-type","application/json;charset=utf-8");
            $this->swooleReponse->write(json_encode($ret));
        }
        else{
            $this->swooleReponse->write($ret);
        }
        $this->swooleReponse->end();
    }



}