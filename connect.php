<?php

    $errorconn = '';

    $conn = mysqli_connect('localhost','root','','newcore2');

    if(mysqli_connect_error()){
        $errorconn = "Cant Connect: " . mysqli_connect_error();
    }else{
        $errorconn ='Connected to database!';
    }
    
?>