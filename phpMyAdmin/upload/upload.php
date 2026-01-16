<?php
    var_dump($_FILES);
    $path="./IMG/";
    $name="pippo";
    $ext=".jpg";
    $n="";
    while(file_exists($path.$name.$n.$ext))
        if ($n=="")
            $n=1;
        else    
            $n++;
    move_uploaded_file($_FILES["miofile"]["tmp_name"], $path.$name.$n.$ext);
?>