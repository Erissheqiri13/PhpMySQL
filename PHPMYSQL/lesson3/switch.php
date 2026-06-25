<?php
    $age = 18;

    switch($age){
        case ($age >=0 && $age <18):
            echo "You are a minor (0-18 Years Old) <br>";
            break;

        case ($age >=18 && $age <25):
            echo "You are a Young Adult <br>";
            break;

        case ($age >=25):
            echo "You are an Adult <br>";
            break;

        default:
            echo "Invalid Age input. <br>";
            break;
    }
?>