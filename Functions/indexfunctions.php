<?php
require_once("../db.php");
require_once("../Functions/functionsDb.php");
$json = json_decode(file_get_contents('php://input'));
$funcion = $json->funcion;
switch ($funcion) {
  case 'anularOrden':
    updateOrden($json->IdOrden, 2, $con);
    break;
  case 'verificarEstadoTr':
    verificarEstadoTr($json->IdTr, $con);
    break;
  case 'obtenerFormasPago':
    obFormaPTr($json->IdTr, $con);
    break;
  case 'obUsuario':
    obUsuario($json->Usuario, $json->IdSucur, $json->IdNivel, $con);
    break;
  case 'anularProductoOrden':
    pagarProductoOrden($json->idreg, $con, 2);
    updateOrden($json->idorden, 3, $con);
    break;
  case 'obDatosContingencia':
    obDatosContingencia($json->IdCajas, $con);
    break;
  case 'obId':
    echo getid($json->Tabla, $json->Condicion, $con);
    break;
  case 'obNumOrden':
    echo getordenNo($json->IdSucursales, $con);
    break;
  case 'updateTotalOrden':
    updateTotalOrden($json->IdOrden, $con);
    break;
  case 'obNoOrdenTr':
    echo getOrdenTr($json->IdTr, $json->IdSucur, $con);
    break;
  case 'updateCliente':
    updateCliente($json, $con);
    break;
  case 'updateDireccionCl':
    updateDireccionCl($json, $con);
    break;
  case 'prueba':
    if (obParamTmp($con, 1) == 1) {
      echo "holas";
    } else {
      echo "si";
    }
    break;
}
