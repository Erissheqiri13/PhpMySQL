<?php

try{
    $pdo = new PDO("mysql:host=localhost;dbname=autosallon","root","");

    $sql = "DROP TABLE Makina";

    $pdo->exec($sql);

    echo "Table dropped successfully!";
    
}catch(PDOException $e){
    echo "Error creating column: ". $e->getMessage();
}

?>