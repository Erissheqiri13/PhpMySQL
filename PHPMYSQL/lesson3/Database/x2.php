<?php

try{
    $pdo = new PDO("mysql:host=localhost;dbname=autosallon","root","");

    $sql = "INSERT INTO Makina (VP,Kilometra) VALUES (2013,4000)";

    $pdo->exec($sql);

    echo "Inserted successfully!";
    
}catch(PDOException $e){
    echo "Error creating column: ". $e->getMessage();
}

?>