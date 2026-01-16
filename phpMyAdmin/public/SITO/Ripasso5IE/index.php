<?php
session_start();
header("Content-Type: application/json");
$S=json_encode($_SERVER,JSON_PRETTY_PRINT);
echo $S;

/*
if (isset($_SESSION["n"]) && $_SESSION["n"]>=10)
    echo "TOO MANY REQUEST";
else if(isset($_SESSION["ciao"])){
	echo "Utente riconosciuto :".$_SESSION["n"];
    $_SESSION["n"]++;
}
else{
	echo "Prima volta";
    $_SESSION["ciao"]="Benvenuto";
    $_SESSION["n"]=1;
}
*/





?>