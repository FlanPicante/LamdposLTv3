<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  require_once("../db.php");
  require_once("../Functions/functionsDb.php");
  $json = json_decode(file_get_contents('php://input'));

  $query = "INSERT INTO pedidostemporal(Idcajas,IdMesa,IdSucursales,Serie,NoDocumento
                        ,IdUsuarios,Fecha,Inicio,Fin,Total,TotalProductos,Estado,Cliente";
  if ($json->IdVendedor > 0) {
    $query .= ",IdVendedores)VALUES($json->IdCajas,$json->IdMesa,$json->IdSucursales,'$json->Serie',$json->NoDocumento,
                $json->IdUsuarios,'$json->Fecha','$json->Inicio','$json->Inicio',$json->Total,$json->TotalProductos,$json->Estado,'$json->NombreOrden',$json->IdVendedor)";
  } else {
    $query .= ")VALUES($json->IdCajas,$json->IdMesa,$json->IdSucursales,'$json->Serie',$json->NoDocumento,
                $json->IdUsuarios,'$json->Fecha','$json->Inicio','$json->Inicio',$json->Total,$json->TotalProductos,$json->Estado,'$json->NombreOrden')";
  }
  if(!$con->query($query)){//QUERY DEL INSERT A DB PEDIDOTEMPORAL
    $msgError = str_replace("'",'"', $con->error);
    $sql = str_replace("'","''", $query);
    $dw= array('modulo' => $_SERVER['SCRIPT_NAME'],'proceso'=>$_SERVER['REQUEST_URI']);

    $tmp = [];
    $tmp['IdSucursales'] = $json->IdSucursales;
    $tmp['IdCajas'] = $json->IdCajas;
    $tmp['IdUsuarios']=$json->IdUsuarios;
    $tmp['IdTransacciones']=$json->IdTransacciones;
    $tmp['Serie']=$json->Serie;
    $tmp['NoDocumento']=$json->NoDocumento;
    generarLogDB($msgError,$sql,$con,$tmp,$dw);
  }
  $condicion="Fecha='$json->Fecha' AND IdCajas=$json->IdCajas AND Serie='$json->Serie' AND NoDocumento=$json->NoDocumento";
  $idtransaccion = getid("pedidostemporal",$condicion,$con);

  $queryU = "UPDATE numeracion SET Numero=Numero+1 WHERE Id=$json->IdResoluciones";
  updateMesa($json->IdMesa, 0, $con);

  $con->query($queryU);




  echo $idtransaccion;
}
