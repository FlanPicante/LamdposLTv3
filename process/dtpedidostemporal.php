<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  require_once("../db.php");
  require_once("../Functions/functionsDb.php");
  $json = json_decode(file_get_contents('php://input'));
  $productos = $json->Productos;
  //INCIO DE DETALLETRANSACCIONES
  $queryM = "INSERT INTO pedidosdetalletmp(IdSucursales,IdCajas,IdTransacciones,FechaHora,Upc,DescCorta,IdDepartamentos,PrecioUnitario,CostoUnitario,
    Total,TotalCosto,Cantidad,UpcReferencia,IdReferencia,TipoProducto)
          VALUES";
  $i = 0;
  foreach ($productos as $item) {

    //PRODUCTOS
    $query1 = $queryM . "($json->IdSucursales,$json->IdCajas,$json->IdTransacciones,'$json->FechaH','" . $item->Producto->Upc . "','" . $item->Producto->DescCorta . "',
          " . $item->Producto->IdDepartamentos . ",'" . $item->Producto->PrecioUnitario . "','" . $item->Producto->CostoUnitario . "',
          '" . $item->Producto->Total . "','" . $item->Producto->TotalCosto . "','" . $item->Producto->Cantidad . "',null,null,1)"; //AGREGAR PRODUCTOS ALQUERY

    if (!$con->query($query1)) { //QUERY DEL INSERT A DB DETALLETRANSACCIONES
      $msgError = str_replace("'", '"', $con->error);
      $sql = str_replace("'", "''", $query1);
      $dw = array('modulo' => $_SERVER['SCRIPT_NAME'], 'proceso' => $_SERVER['REQUEST_URI']);

      $tmp = [];
      $tmp['IdSucursales'] = $json->IdSucursales;
      $tmp['IdCajas'] = $json->IdCajas;
      $tmp['IdUsuarios'] = 0;
      $tmp['IdTransacciones'] = $json->IdTransacciones;
      $tmp['Serie'] = 0;
      $tmp['NoDocumento'] = 0;
      generarLogDB($msgError, $sql, $con, $tmp, $dw);
    }

    $condicion = "FechaHora='$json->FechaH' AND IdCajas=$json->IdCajas AND Upc=" . $item->Producto->Upc . " AND IdTransacciones=$json->IdTransacciones";
    $idtransaccion = getid("pedidosdetalletmp", $condicion, $con);

    //COMPLEMENTOS
    if (!empty($item->Producto->Complementos)) { //IF DE VALIDADCION QUE ESTE VACIO
      foreach ($item->Producto->Complementos as $comp) { //FOR PARA AGREGAR COMPLEMENTOS A QUERY
        $query1 = $queryM . "($json->IdSucursales,$json->IdCajas,$json->IdTransacciones,'$json->FechaH','" . $comp->Complemento->UpcComple . "',
              '" . $comp->Complemento->DescCorta . "'," . $item->Producto->IdDepartamentos . ",
                  '" . $comp->Complemento->PrecioUnitario . "','" . $comp->Complemento->CostoUnitario . "',
                  '" . $comp->Complemento->PrecioUnitario * $item->Producto->Cantidad . "',
                  '" . $comp->Complemento->CostoUnitario * $item->Producto->Cantidad . "',
                  '" . $item->Producto->Cantidad . "','" . $item->Producto->Upc . "','" . $idtransaccion . "',5)";
        if (!$con->query($query1)) { //QUERY DEL INSERT A DB DETALLETRANSACCIONES
          $msgError = str_replace("'", '"', $con->error);
          $sql = str_replace("'", "''", $query1);
          $dw = array('modulo' => $_SERVER['SCRIPT_NAME'], 'proceso' => $_SERVER['REQUEST_URI']);

          $tmp = [];
          $tmp['IdSucursales'] = $json->IdSucursales;
          $tmp['IdCajas'] = $json->IdCajas;
          $tmp['IdUsuarios'] = 0;
          $tmp['IdTransacciones'] = $json->IdTransacciones;
          $tmp['Serie'] = 0;
          $tmp['NoDocumento'] = 0;
          generarLogDB($msgError, $sql, $con, $tmp, $dw);
        }
      }
    }
    //EXTRAS
    if (!empty($item->Producto->Extras)) { //IF DE VALICADION QUE ESTE VACIO
      foreach ($item->Producto->Extras as $extra) { //FOR PARA AGREGAR EXTRAS A QUERY
        $cantidAc = $item->Producto->Cantidad * $extra->Extra->Unidad;
        $query1 = $queryM . "($json->IdSucursales,$json->IdCajas,$json->IdTransacciones,'$json->Fecha','" . $extra->Extra->UpcExt . "',
             '" . $extra->Extra->DescCorta . "'," . $item->Producto->IdDepartamentos . ",
             '" . $extra->Extra->PrecioUnitario . "','" . $extra->Extra->CostoUnitario . "',
             '" . $extra->Extra->PrecioUnitario * $item->Producto->Cantidad . "',
             '" . $extra->Extra->CostoUnitario * $item->Producto->Cantidad . "',
             '" . $item->Producto->Cantidad . "','" . $item->Producto->Upc . "','" . $idtransaccion . "',6)";
        if (!$con->query($query1)) { //QUERY DEL INSERT A DB DETALLETRANSACCIONES
          $msgError = str_replace("'", '"', $con->error);
          $sql = str_replace("'", "''", $query1);
          $dw = array('modulo' => $_SERVER['SCRIPT_NAME'], 'proceso' => $_SERVER['REQUEST_URI']);

          $tmp = [];
          $tmp['IdSucursales'] = $json->IdSucursales;
          $tmp['IdCajas'] = $json->IdCajas;
          $tmp['IdUsuarios'] = 0;
          $tmp['IdTransacciones'] = $json->IdTransacciones;
          $tmp['Serie'] = 0;
          $tmp['NoDocumento'] = 0;
          generarLogDB($msgError, $sql, $con, $tmp, $dw);
        }
      }
    }
    $i++; //AUTOIMCREMENTO
  }
} else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  require_once("../db.php");
  $idOrden = $_GET['idOrden'];

  $query = "SELECT Id,IdTransacciones,IdDepartamentos,Cantidad,Upc,DescCorta,PrecioUnitario,Total,CostoUnitario,
      TotalCosto,UpcReferencia,IdReferencia,TipoProducto 
      FROM pedidosdetalletmp WHERE IdTransacciones=" . $idOrden . " AND Estatus=1 ORDER BY TipoProducto ASC";
  $result = $con->query($query);
  if ($result->num_rows > 0) {
    // VER CON QUE RECIBIR DATA
    $rows = array();
    while ($r = $result->fetch_assoc()) {
      $rows[] = $r;
    }
    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
  } else {
    $msg = array('msg' => "NO HAY DEPARTAMENTOS ASIGANOS A LA SUCURSAL");
    echo json_encode($msg, JSON_UNESCAPED_UNICODE);
  }

  $result->close();
  $con->close();
}
