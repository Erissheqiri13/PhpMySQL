<?php
$Filename = "ds.txt";
$File = fopen($Filename,"r");
$FileSize = filesize($Filename);
$Fread = fread($File,$FileSize);

echo $Fread;
?>