<?php

$username="root";
$password="";
$host="localhost";
$dbname="ecomers";

$conection=new PDO("mysql:host=$host;dbname=$dbname",$username,$password);
if($conection){
    
}else{
    echo"nooooooooooo cenect";
}



?>