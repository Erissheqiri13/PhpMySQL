<?php

try{
    $pdo = new PDO("mysql:host=localhost;dbname=db4","root","");

    $sql = "ALTER TABLE users DROP column email";

    $pdo->exec($sql);

    echo "Column dropped successfully!";
    
}catch(PDOException $e){
    echo "Error creating column: ". $e->getMessage();
}

?>