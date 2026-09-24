<?php

try{
    $pdo = new PDO("mysql:host=localhost;dbname=autosallon","root","");

    $sql = "CREATE TABLE Makina (

        id INT(6) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        VP INT NOT NULL ,
        Motorri INT NOT NULL,
        Kilometra INT NOT NULL,
        Garancion INT NOT NULL

        )";

    $pdo->exec($sql);

    echo "Column created successfully!";
    
}catch(PDOException $e){
    echo "Error creating column: ". $e->getMessage();
}

?>