<?php

	if($_SERVER['REQUEST_METHOD']== 'POST'){
    require_once("../db.php");
    require_once("../Functions/functionsDb.php");
		$json = json_decode(file_get_contents('php://input'));
    $queryLog="INSERT INTO log_errors(IdSucursales,IdCajas,IdUsuarios,FechaHora,Idtransacciones,SerieDocumento,NoDocumento,Programa,VerPrograma,Proceso,SQLDescripcion,ErrorMsg,SQLError)
    VALUES($json->IdSucursales,$json->IdCajas,$json->IdUsuarios,'".$json->FechaHora."',$json->IdTransacciones,'".$json->Serie."','".$json->NoDocu."','".$json->Programa."',
    '$json->VerPrograma','$json->Proceso','$json->SQLDESC','$json->ErrorMsg','$json->SqlError')";
     $con->query($queryLog);
	$con->close();
	}
