<?php
namespace App\models;
use Core\lib\DBModel;

class Products extends DBModel {
    protected $table = 'products';
    //指定主键
    protected $primaryKey = 'prod_id';
    //protected $connection="abc";
    public $timestamps = false;
}