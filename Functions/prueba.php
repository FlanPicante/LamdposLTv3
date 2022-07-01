<?php
require_once("../db.php");
require_once("../Functions/functionsDb.php");
$json = json_decode(file_get_contents('php://input'));
//INSERT SAT_FEL_EMISIONES
getordenNo($json->IdSucursales, $con);