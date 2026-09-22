<?php

try{
    $pdo = new PDO("mysql:host=localhost;dbname=db4","root","");

    $sql = "DROP TABLE users";

    $pdo->exec($sql);

    echo "table dropped successfully!";
    
}catch(PDOException $e){
    echo "Error creating column: ". $e->getMessage();
}

?>