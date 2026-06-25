<?php
    $mosha = 21;

    // if($mosha>29){
    //     echo "Shko nFakulltet";
    // }else{
    //     echo "Shko Nshkoll";
    // }

    if($mosha < 18){
        echo "Shko Ne Shkoll";
    }elseif($mosha == 21){
        echo "Nisja Fakulltetit";
    }else{
        echo "Vazhdo Me Pune";
    }
?>