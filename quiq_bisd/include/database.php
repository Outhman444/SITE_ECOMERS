<?php

$username="";
$password="";
$host="";
$dbname="";

$conection=new PDO("mysql:host=$host;dbname=$dbname",$username,$password);
if($conection){
    
}else{
    echo"nooooooooooo cenect";
}



?>