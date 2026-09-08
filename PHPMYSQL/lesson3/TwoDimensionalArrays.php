<?php

$dogs = array(
    array("Chihuahua","Mexico",20),
    array("Husky","Siberia",15),
    array("Bulldog","England",25),
);

echo $dogs[0][0]." Origin: ".$dogs[0][1]." Age: ".$dogs[0][2]."<br>";
echo $dogs[1][0]." Origin: ".$dogs[1][1]." Age: ".$dogs[1][2]."<br>";
echo $dogs[2][0]." Origin: ".$dogs[2][1]." Age: ".$dogs[2][2]."<br>";

for($row=0;$row<3;$row++){
    echo "<p><b>Row Number $row</b></p>";
    echo "<ul>";
    for($col=0;$col<3;$col++){
        echo "<li>".$dogs[$row][$col]."</li>";
    }
    echo "</ul>";
}

for ($i=1;$i<4;$i++){
    for($j=1;$j<4;$j++){
        echo "Array: $i Element $j <br>";
    }
}

?>