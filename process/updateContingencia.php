<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  require_once("../db.php");
  require_once("../Functions/functionsDb.php");
  require_once("../Functions/errores.php");
  $json = json_decode(file_get_contents('php://input'));


  //VARIABLES GENERALES
  $estado = "";
  if ($json->Contingencia == 1) {
    $estado = 'PENDIENTE';
    $queryS = "UPDATE sat_fel_emisiones SET 
                Estado='$estado',
                RespuestaMensaje='$json->Mensaje',
                RespuestaData='$json->ResponseData'
                WHERE IdTransacciones=$json->IdTransacciones";
  } else {
    $estado = 'CERTIFICADO';
    //CERTIFICADO
    $query = "UPDATE transacciones SET
                Serie='$json->Serie',
                NoDocumento=$json->NoDocu,
                FelFechaCertificacion='$json->FechaCertificacion',
                AutorizacionFEL='$json->AutorizaiconFel',
                Estado=1
                WHERE Id=$json->IdTransacciones";

    if (!$con->query($query)) {
      //MANEJO ERRORES
      $msgError = str_replace("'", '"', $con->error);
      $sql = str_replace("'", "''", $query);
      $dw = array('modulo' => $_SERVER['SCRIPT_NAME'], 'proceso' => $_SERVER['REQUEST_URI']);

      $tmp = [];
      $tmp['IdSucursales'] = 0;
      $tmp['IdCajas'] = 0;
      $tmp['IdUsuarios'] = 0;
      $tmp['IdTransacciones'] = $json->IdTransacciones;
      $tmp['Serie'] = $json->Serie;
      $tmp['NoDocumento'] = $json->NoDocu;
      generarLogDB($msgError, $sql, $con, $tmp, $dw);
    }
    $queryS = "UPDATE sat_fel_emisiones SET 
                Estado='$estado',
                FechaCertificacion='$json->FechaCertificacion',
                RespuestaMensaje='$json->Mensaje',
                RespuestaData='$json->ResponseData'
                WHERE IdTransacciones=$json->IdTransacciones";
  }

  if (!$con->query($queryS)) {
    //MANEJO ERRORES
    $msgError = str_replace("'", '"', $con->error);
    $sql = str_replace("'", "''", $queryS);
    $dw = array('modulo' => $_SERVER['SCRIPT_NAME'], 'proceso' => $_SERVER['REQUEST_URI']);

    $tmp = [];
    $tmp['IdSucursales'] = 0;
    $tmp['IdCajas'] = 0;
    $tmp['IdUsuarios'] = 0;
    $tmp['IdTransacciones'] = $json->IdTransacciones;
    $tmp['Serie'] = 0;
    $tmp['NoDocumento'] = 0;
    generarLogDB($msgError, $sql, $con, $tmp, $dw);
  }

  $con->close();
}
