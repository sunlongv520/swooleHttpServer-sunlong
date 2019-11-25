<?php
namespace App\models;
use Core\lib\DBModel;

class Users extends DBModel {
    protected $table = 'users';
    //指定主键
    protected $primaryKey = 'user_id';
    //protected $connection="abc";
    public $timestamps = false;
}