<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  require_once("../db.php");
  require_once("../Functions/functionsDb.php");
  require_once("../Functions/errores.php");
  $json = json_decode(file_get_contents('php://input'));

  $productos = $json->Productos;
  //INCIO DE DETALLETRANSACCIONES
  $queryM = "INSERT INTO detalletransacciones(IdSucursales,IdCajas,IdTransacciones,Fecha,Upc,DescCorta,IdDepartamentos,
    PrecioUnitario,CostoUnitario,Total,TotalCosto,Cantidad,UpcReferencia,IdReferencia,TipoProducto,RequeEspText,
    DescuentoUnitario,TotalDescuento)
          VALUES";

  $queryVentasDiarias = "INSERT INTO ventasdiarias
    (IdSucursales,Fecha,UPC,DeptoAbierto,Cantidad,CostoTotal,MontoTotal,ClientesAtendidos,IdDepartamentos,PLU,
    IdDivisiones, IdCategorias, IdSubcategorias, IdProveedores,Oferta, DescCorta,IdEmpresas,ConAlcohol)
        VALUES";

  $queryVentasdelDia = "INSERT INTO ventasdeldia
      (IdSucursales,Fecha,UPC,DeptoAbierto,Cantidad,CostoTotal,MontoTotal,ClientesAtendidos,IdDepartamentos,PLU, IdDivisiones, 
      IdCategorias, IdSubcategorias, IdProveedores,Oferta, DescCorta,IdEmpresas) 
    VALUES ";

  $queryVentasxDep = "INSERT INTO ventasxdepartamento
    (IdSucursales,Fecha,IdDepartamentos,Cantidad,CostoTotal,MontoTotal,ClientesAtendidos,IdEmpresas) 
    VALUES ";

  $queryVentasxHora = "INSERT INTO ventasxhora
    (IdSucursales,Fecha,Hora,Dia,Inicio,Fin,Total,TotalCosto,NumTransacciones,CantidadVendida,ItemsVendidos,PrecioProm,TicketProm,Margen,IdEmpresas) 
    VALUES ";


  $i = 0;
  foreach ($productos as $item) {

    //PRODUCTOS DETALLETRANSACCIONES
    $query1 = $queryM . "($json->IdSucursales,$json->IdCajas,$json->IdTransacciones,'$json->FechaH','" . $item->Producto->Upc . "','" . $item->Producto->DescCorta . "',
          " . $item->Producto->IdDepartamentos . ",'" . $item->Producto->PrecioUnitario . "','" . $item->Producto->CostoUnitario . "',
          '" . $item->Producto->Total . "','" . $item->Producto->TotalCosto . "','" . $item->Producto->Cantidad .
           "',null,null,1,'" . $item->Producto->Requerimiento . "',".$item->Producto->DescuentoUnitario.",".$item->Producto->DescuentoTotal.")"; //AGREGAR PRODUCTOS ALQUERY

    //ENVIO A DETALLETRANSACCION
    if (!$con->query($query1)) {
      $msgError = str_replace("'", '"', $con->error);
      $sql = str_replace("'", "''", $query1);
      $dw = array('modulo' => $_SERVER['SCRIPT_NAME'], 'proceso' => $_SERVER['REQUEST_URI']);
      $tmp = [];
      $tmp['IdSucursales'] = $json->IdSucursales;
      $tmp['IdCajas'] = $json->IdCajas;
      $tmp['IdUsuarios'] = 0;
      $tmp['IdTransacciones'] = $json->IdTransacciones;
      $tmp['Serie'] = $json->Serie;
      $tmp['NoDocumento'] = $json->NoDoc;
      generarLogDB($msgError, $sql, $con, $tmp, $dw);
    }
    $condicion = "Fecha='$json->FechaH' AND IdCajas=$json->IdCajas AND Upc=" . $item->Producto->Upc . " AND IdTransacciones=$json->IdTransacciones";
    $idtransaccion = getid("detalletransacciones", $condicion, $con);

    //PRODUCTOS VENTAS DIARIAS
    $queryVD = $queryVentasDiarias . " ($json->IdSucursales,'$json->Fecha','" . $item->Producto->Upc . "','0',
          " . $item->Producto->Cantidad . "," . $item->Producto->TotalCosto . "," . $item->Producto->Total . ",1,
          " . $item->Producto->IdDepartamentos . ",NULL, NULL, NULL, NULL, NULL,'0', '" . $item->Producto->DescCorta . "',$json->IdEmpresas,0)
    ON DUPLICATE KEY UPDATE 
       Cantidad =          Cantidad    +  VALUES(Cantidad)    
      ,CostoTotal =        CostoTotal  +  VALUES(CostoTotal)   
      ,MontoTotal =        MontoTotal  +  VALUES(MontoTotal)   
      ,ClientesAtendidos = ClientesAtendidos + VALUES(ClientesAtendidos) 
     ,IdDepartamentos = VALUES(IdDepartamentos)";

    //ENVIO A VENTAS DIARIAS
    if (!$con->query($queryVD)) {
      $msgError = str_replace("'", '"', $con->error);
      $sql = str_replace("'", "''", $queryVD);
      $dw = array('modulo' => $_SERVER['SCRIPT_NAME'], 'proceso' => $_SERVER['REQUEST_URI']);

      $tmp = [];
      $tmp['IdSucursales'] = $json->IdSucursales;
      $tmp['IdCajas'] = $json->IdCajas;
      $tmp['IdUsuarios'] = 0;
      $tmp['IdTransacciones'] = $json->IdTransacciones;
      $tmp['Serie'] = $json->Serie;
      $tmp['NoDocumento'] = $json->NoDoc;
      generarLogDB($msgError, $sql, $con, $tmp, $dw);
    }

    //PRODUCTO VENTAS DEL DIA
    $queryVdD = $queryVentasdelDia . "($json->IdSucursales,'$json->Fecha','" . $item->Producto->Upc . "','0',"
      . $item->Producto->Cantidad . "," . $item->Producto->TotalCosto . "," . $item->Producto->Total . ",1,"
      . $item->Producto->IdDepartamentos . ",NULL, NULL, NULL, NULL, NULL,'0', '" . $item->Producto->DescCorta . "',$json->IdEmpresas)
    ON DUPLICATE KEY UPDATE 
       Cantidad =          Cantidad    +  VALUES(Cantidad)    
      ,CostoTotal =        CostoTotal  +  VALUES(CostoTotal)   
      ,MontoTotal =        MontoTotal  +  VALUES(MontoTotal)   
      ,ClientesAtendidos = ClientesAtendidos + VALUES(ClientesAtendidos) 
     ,IdDepartamentos = VALUES(IdDepartamentos)";

    //ENVIO A VENTAS DEL DIA
    if (!$con->query($queryVdD)) {
      $msgError = str_replace("'", '"', $con->error);
      $sql = str_replace("'", "''", $queryVdD);
      $dw = array('modulo' => $_SERVER['SCRIPT_NAME'], 'proceso' => $_SERVER['REQUEST_URI']);

      $tmp = [];
      $tmp['IdSucursales'] = $json->IdSucursales;
      $tmp['IdCajas'] = $json->IdCajas;
      $tmp['IdUsuarios'] = 0;
      $tmp['IdTransacciones'] = $json->IdTransacciones;
      $tmp['Serie'] = $json->Serie;
      $tmp['NoDocumento'] = $json->NoDoc;
      generarLogDB($msgError, $sql, $con, $tmp, $dw);
    }

    //PRODUCTO VENTAS x DEPARTAMENTO
    $queryVxD = $queryVentasxDep . "($json->IdSucursales,'$json->Fecha'," . $item->Producto->IdDepartamentos . ","
      . $item->Producto->Cantidad . "," . $item->Producto->TotalCosto . "," . $item->Producto->Total . ",1,$json->IdEmpresas)
      ON DUPLICATE KEY UPDATE 
        Cantidad =          Cantidad    +  VALUES(Cantidad)    
        ,CostoTotal =        CostoTotal  +  VALUES(CostoTotal)   
        ,MontoTotal =        MontoTotal  +  VALUES(MontoTotal)   
        ,ClientesAtendidos = ClientesAtendidos + VALUES(ClientesAtendidos)";

    //ENVIO A VENTAS X DEPARTAMENTO
    if (!$con->query($queryVxD)) {
      $msgError = str_replace("'", '"', $con->error);
      $sql = str_replace("'", "''", $queryVxD);
      $dw = array('modulo' => $_SERVER['SCRIPT_NAME'], 'proceso' => $_SERVER['REQUEST_URI']);

      $tmp = [];
      $tmp['IdSucursales'] = $json->IdSucursales;
      $tmp['IdCajas'] = $json->IdCajas;
      $tmp['IdUsuarios'] = 0;
      $tmp['IdTransacciones'] = $json->IdTransacciones;
      $tmp['Serie'] = $json->Serie;
      $tmp['NoDocumento'] = $json->NoDoc;
      generarLogDB($msgError, $sql, $con, $tmp, $dw);
    }


    //PRODUCTO VENTAS x HORA
    $margenH = (($item->Producto->TotalCosto / $item->Producto->Total) * 100);
    $queryVxH = $queryVentasxHora . "($json->IdSucursales,DATE('$json->Fecha'),HOUR('$json->FechaH'),DAYNAME('$json->Fecha'),
    '$json->Hora','$json->Hora'," . $item->Producto->Total . ","
      . $item->Producto->TotalCosto . ",1," . $item->Producto->Cantidad . "," . $item->Producto->Cantidad . ","
      . $item->Producto->Total / $item->Producto->Cantidad . "," . $item->Producto->Total . "," . $margenH . ",$json->IdEmpresas)
    ON DUPLICATE KEY UPDATE 
    Fin=VALUES(FIN),
    Total = Total + VALUES(Total), 
    TotalCosto = TotalCosto + VALUES(TotalCosto), 
    NumTransacciones = NumTransacciones + VALUES(NumTransacciones), 
    CantidadVendida = CantidadVendida + VALUES(CantidadVendida), 
    ItemsVendidos = ItemsVendidos + VALUES(ItemsVendidos), 
    PrecioProm = Total / CantidadVendida, 
    TicketProm = Total / NumTransacciones, 
    Margen = ((TotalCosto/Total))*100";

    //ENVIO A VENTAS X HORA
    if (!$con->query($queryVxH)) {
      $msgError = str_replace("'", '"', $con->error);
      $sql = str_replace("'", "''", $queryVxH);
      $dw = array('modulo' => $_SERVER['SCRIPT_NAME'], 'proceso' => $_SERVER['REQUEST_URI']);

      $tmp = [];
      $tmp['IdSucursales'] = $json->IdSucursales;
      $tmp['IdCajas'] = $json->IdCajas;
      $tmp['IdUsuarios'] = 0;
      $tmp['IdTransacciones'] = $json->IdTransacciones;
      $tmp['Serie'] = $json->Serie;
      $tmp['NoDocumento'] = $json->NoDoc;
      generarLogDB($msgError, $sql, $con, $tmp, $dw);
    }

    //COMPLEMENTOS
    if (!empty($item->Producto->Complementos)) { //IF DE VALIDADCION QUE ESTE VACIO
      foreach ($item->Producto->Complementos as $comp) { //FOR PARA AGREGAR COMPLEMENTOS A QUERY
        $query1 = $queryM . "($json->IdSucursales,$json->IdCajas,$json->IdTransacciones,'$json->FechaH','" . $comp->Complemento->UpcComple . "',
              '" . $comp->Complemento->DescCorta . "'," . $item->Producto->IdDepartamentos . ",
                  '" . $comp->Complemento->PrecioUnitario . "','" . $comp->Complemento->CostoUnitario . "',
                  '" . $comp->Complemento->PrecioUnitario * $item->Producto->Cantidad . "',
                  '" . $comp->Complemento->CostoUnitario * $item->Producto->Cantidad . "',
                  '" . $item->Producto->Cantidad . "','" . $item->Producto->Upc . "','" . $idtransaccion . "',5,null,0,0)";
        if (!$con->query($query1)) { //QUERY DEL INSERT A DB DETALLETRANSACCIONES
          $msgError = str_replace("'", '"', $con->error);
          $sql = str_replace("'", "''", $query1);
          $dw = array('modulo' => $_SERVER['SCRIPT_NAME'], 'proceso' => $_SERVER['REQUEST_URI']);

          $tmp = [];
          $tmp['IdSucursales'] = $json->IdSucursales;
          $tmp['IdCajas'] = $json->IdCajas;
          $tmp['IdUsuarios'] = 0;
          $tmp['IdTransacciones'] = $json->IdTransacciones;
          $tmp['Serie'] = $json->Serie;
          $tmp['NoDocumento'] = $json->NoDoc;
          generarLogDB($msgError, $sql, $con, $tmp, $dw);
        }

        //COMPLEMENTOS VENTAS DIARIAS
        $queryVD = $queryVentasDiarias . " ($json->IdSucursales,'$json->Fecha','" .  $comp->Complemento->UpcComple . "','0',
           " . $item->Producto->Cantidad . "," . $comp->Complemento->CostoUnitario * $item->Producto->Cantidad . ",
           " . $comp->Complemento->PrecioUnitario * $item->Producto->Cantidad . ",1," . $item->Producto->IdDepartamentos . "
           ,NULL, NULL, NULL, NULL, NULL,'0', '" . $comp->Complemento->DescCorta . "',$json->IdEmpresas,0)
          ON DUPLICATE KEY UPDATE 
          Cantidad =          Cantidad    +  VALUES(Cantidad)    
          ,CostoTotal =        CostoTotal  +  VALUES(CostoTotal)   
          ,MontoTotal =        MontoTotal  +  VALUES(MontoTotal)   
          ,ClientesAtendidos = ClientesAtendidos + VALUES(ClientesAtendidos) 
          ,IdDepartamentos = VALUES(IdDepartamentos)";

        //ENVIO A VENTAS DIARIAS
        if (!$con->query($queryVD)) {
          $msgError = str_replace("'", '"', $con->error);
          $sql = str_replace("'", "''", $queryVD);
          $dw = array('modulo' => $_SERVER['SCRIPT_NAME'], 'proceso' => $_SERVER['REQUEST_URI']);

          $tmp = [];
          $tmp['IdSucursales'] = $json->IdSucursales;
          $tmp['IdCajas'] = $json->IdCajas;
          $tmp['IdUsuarios'] = 0;
          $tmp['IdTransacciones'] = $json->IdTransacciones;
          $tmp['Serie'] = $json->Serie;
          $tmp['NoDocumento'] = $json->NoDoc;
          generarLogDB($msgError, $sql, $con, $tmp, $dw);
        }

        //COMPLEMENTO VENTAS DEL DIA
        $queryVdD = $queryVentasdelDia . "($json->IdSucursales,'$json->Fecha','" . $comp->Complemento->UpcComple . "','0',"
          . $item->Producto->Cantidad . "," . $comp->Complemento->CostoUnitario * $item->Producto->Cantidad . ","
          . $comp->Complemento->PrecioUnitario * $item->Producto->Cantidad . ",1,"
          . $item->Producto->IdDepartamentos . ",NULL, NULL, NULL, NULL, NULL,'0', '" . $comp->Complemento->DescCorta . "',$json->IdEmpresas)
          ON DUPLICATE KEY UPDATE 
          Cantidad =          Cantidad    +  VALUES(Cantidad)    
          ,CostoTotal =        CostoTotal  +  VALUES(CostoTotal)   
          ,MontoTotal =        MontoTotal  +  VALUES(MontoTotal)   
          ,ClientesAtendidos = ClientesAtendidos + VALUES(ClientesAtendidos) 
          ,IdDepartamentos = VALUES(IdDepartamentos)";

        //ENVIO A VENTAS DEL DIA
        if (!$con->query($queryVdD)) {
          $msgError = str_replace("'", '"', $con->error);
          $sql = str_replace("'", "''", $queryVdD);
          $dw = array('modulo' => $_SERVER['SCRIPT_NAME'], 'proceso' => $_SERVER['REQUEST_URI']);

          $tmp = [];
          $tmp['IdSucursales'] = $json->IdSucursales;
          $tmp['IdCajas'] = $json->IdCajas;
          $tmp['IdUsuarios'] = 0;
          $tmp['IdTransacciones'] = $json->IdTransacciones;
          $tmp['Serie'] = $json->Serie;
          $tmp['NoDocumento'] = $json->NoDoc;
          generarLogDB($msgError, $sql, $con, $tmp, $dw);
        }

        //COMPLEMENTOS VENTAS x DEPARTAMENTO
        $queryVxD = $queryVentasxDep . "($json->IdSucursales,'$json->Fecha'," . $item->Producto->IdDepartamentos . ","
          . $item->Producto->Cantidad . "," . $comp->Complemento->CostoUnitario * $item->Producto->Cantidad
          . "," . $comp->Complemento->PrecioUnitario * $item->Producto->Cantidad . ",1,$json->IdEmpresas)
       ON DUPLICATE KEY UPDATE 
         Cantidad =          Cantidad    +  VALUES(Cantidad)    
         ,CostoTotal =        CostoTotal  +  VALUES(CostoTotal)   
         ,MontoTotal =        MontoTotal  +  VALUES(MontoTotal)   
         ,ClientesAtendidos = ClientesAtendidos + VALUES(ClientesAtendidos)";

        //ENVIO A VENTAS X DEPARTAMENTO
        if (!$con->query($queryVxD)) {
          $msgError = str_replace("'", '"', $con->error);
          $sql = str_replace("'", "''", $queryVxD);
          $dw = array('modulo' => $_SERVER['SCRIPT_NAME'], 'proceso' => $_SERVER['REQUEST_URI']);

          $tmp = [];
          $tmp['IdSucursales'] = $json->IdSucursales;
          $tmp['IdCajas'] = $json->IdCajas;
          $tmp['IdUsuarios'] = 0;
          $tmp['IdTransacciones'] = $json->IdTransacciones;
          $tmp['Serie'] = $json->Serie;
          $tmp['NoDocumento'] = $json->NoDoc;
          generarLogDB($msgError, $sql, $con, $tmp, $dw);
        }

        //COMPLEMENTOS VENTAS x HORA
        $queryVxH = $queryVentasxHora . "($json->IdSucursales,DATE('$json->Fecha'),HOUR('$json->FechaH'),DAYNAME('$json->Fecha'),
        '$json->Hora','$json->Hora'," . $comp->Complemento->PrecioUnitario * $item->Producto->Cantidad . ","
          . $comp->Complemento->CostoUnitario * $item->Producto->Cantidad . ",1," . $item->Producto->Cantidad . "," . $item->Producto->Cantidad . ","
          .  $comp->Complemento->PrecioUnitario * $item->Producto->Cantidad  / $item->Producto->Cantidad . ","
          .  $comp->Complemento->PrecioUnitario * $item->Producto->Cantidad  . ",0,$json->IdEmpresas)
          ON DUPLICATE KEY UPDATE 
          Fin=VALUES(FIN),
          Total = Total + VALUES(Total), 
          TotalCosto = TotalCosto + VALUES(TotalCosto), 
          NumTransacciones = NumTransacciones + VALUES(NumTransacciones), 
          CantidadVendida = CantidadVendida + VALUES(CantidadVendida), 
          ItemsVendidos = ItemsVendidos + VALUES(ItemsVendidos), 
          PrecioProm = Total / CantidadVendida, 
          TicketProm = Total / NumTransacciones, 
          Margen = ((TotalCosto/Total))*100";

        //ENVIO A VENTAS X HORA
        if (!$con->query($queryVxH)) {
          $msgError = str_replace("'", '"', $con->error);
          $sql = str_replace("'", "''", $queryVxH);
          $dw = array('modulo' => $_SERVER['SCRIPT_NAME'], 'proceso' => $_SERVER['REQUEST_URI']);

          $tmp = [];
          $tmp['IdSucursales'] = $json->IdSucursales;
          $tmp['IdCajas'] = $json->IdCajas;
          $tmp['IdUsuarios'] = 0;
          $tmp['IdTransacciones'] = $json->IdTransacciones;
          $tmp['Serie'] = $json->Serie;
          $tmp['NoDocumento'] = $json->NoDoc;
          generarLogDB($msgError, $sql, $con, $tmp, $dw);
        }
      }
    }
    //EXTRAS
    if (!empty($item->Producto->Extras)) { //IF DE VALICADION QUE ESTE VACIO
      foreach ($item->Producto->Extras as $extra) { //FOR PARA AGREGAR EXTRAS A QUERY
        $query1 = $queryM . "($json->IdSucursales,$json->IdCajas,$json->IdTransacciones,'$json->Fecha','" . $extra->Extra->UpcExt . "',
             '" . $extra->Extra->DescCorta . "'," . $item->Producto->IdDepartamentos . ",
             '" . $extra->Extra->PrecioUnitario . "','" . $extra->Extra->CostoUnitario . "',
             '" . $extra->Extra->PrecioUnitario * $item->Producto->Cantidad . "',
             '" . $extra->Extra->CostoUnitario * $item->Producto->Cantidad . "',
             '" . $item->Producto->Cantidad . "','" . $item->Producto->Upc . "','" . $idtransaccion . "',6,null,0,0)";
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

        //EXTRAS VENTAS DIARIAS
        $queryVD = $queryVentasDiarias . " ($json->IdSucursales,'$json->Fecha','" .  $extra->Extra->UpcExt . "','0',
           " . $item->Producto->Cantidad . "," . $extra->Extra->CostoUnitario * $item->Producto->Cantidad . ",
           " . $extra->Extra->PrecioUnitario * $item->Producto->Cantidad . ",1," . $item->Producto->IdDepartamentos . "
           ,NULL, NULL, NULL, NULL, NULL,'0', '" . $extra->Extra->DescCorta . "',$json->IdEmpresas,0)
          ON DUPLICATE KEY UPDATE 
          Cantidad =          Cantidad    +  VALUES(Cantidad)    
          ,CostoTotal =        CostoTotal  +  VALUES(CostoTotal)   
          ,MontoTotal =        MontoTotal  +  VALUES(MontoTotal)   
          ,ClientesAtendidos = ClientesAtendidos + VALUES(ClientesAtendidos) 
          ,IdDepartamentos = VALUES(IdDepartamentos)";

        //ENVIO A VENTAS DIARIAS
        if (!$con->query($queryVD)) {
          $msgError = str_replace("'", '"', $con->error);
          $sql = str_replace("'", "''", $queryVD);
          $dw = array('modulo' => $_SERVER['SCRIPT_NAME'], 'proceso' => $_SERVER['REQUEST_URI']);

          $tmp = [];
          $tmp['IdSucursales'] = $json->IdSucursales;
          $tmp['IdCajas'] = $json->IdCajas;
          $tmp['IdUsuarios'] = 0;
          $tmp['IdTransacciones'] = $json->IdTransacciones;
          $tmp['Serie'] = $json->Serie;
          $tmp['NoDocumento'] = $json->NoDoc;
          generarLogDB($msgError, $sql, $con, $tmp, $dw);
        }

        //EXTRAS VENTAS DEL DIA
        $queryVdD = $queryVentasdelDia . "($json->IdSucursales,'$json->Fecha','" . $extra->Extra->UpcExt . "','0',"
          . $item->Producto->Cantidad . "," . $extra->Extra->CostoUnitario * $item->Producto->Cantidad . ","
          . $extra->Extra->PrecioUnitario * $item->Producto->Cantidad . ",1,"
          . $item->Producto->IdDepartamentos . ",NULL, NULL, NULL, NULL, NULL,'0', '" . $extra->Extra->DescCorta . "',$json->IdEmpresas)
          ON DUPLICATE KEY UPDATE 
          Cantidad =          Cantidad    +  VALUES(Cantidad)    
          ,CostoTotal =        CostoTotal  +  VALUES(CostoTotal)   
          ,MontoTotal =        MontoTotal  +  VALUES(MontoTotal)   
          ,ClientesAtendidos = ClientesAtendidos + VALUES(ClientesAtendidos) 
          ,IdDepartamentos = VALUES(IdDepartamentos)";

        //ENVIO A VENTAS DEL DIA
        if (!$con->query($queryVdD)) {
          $msgError = str_replace("'", '"', $con->error);
          $sql = str_replace("'", "''", $queryVdD);
          $dw = array('modulo' => $_SERVER['SCRIPT_NAME'], 'proceso' => $_SERVER['REQUEST_URI']);

          $tmp = [];
          $tmp['IdSucursales'] = $json->IdSucursales;
          $tmp['IdCajas'] = $json->IdCajas;
          $tmp['IdUsuarios'] = 0;
          $tmp['IdTransacciones'] = $json->IdTransacciones;
          $tmp['Serie'] = $json->Serie;
          $tmp['NoDocumento'] = $json->NoDoc;
          generarLogDB($msgError, $sql, $con, $tmp, $dw);
        }

        //EXTRAS VENTAS x DEPARTAMENTO
        $queryVxD = $queryVentasxDep . "($json->IdSucursales,'$json->Fecha'," . $item->Producto->IdDepartamentos . ","
          . $item->Producto->Cantidad . "," . $extra->Extra->CostoUnitario * $item->Producto->Cantidad
          . "," . $extra->Extra->PrecioUnitario * $item->Producto->Cantidad . ",1,$json->IdEmpresas)
       ON DUPLICATE KEY UPDATE 
         Cantidad =          Cantidad    +  VALUES(Cantidad)    
         ,CostoTotal =        CostoTotal  +  VALUES(CostoTotal)   
         ,MontoTotal =        MontoTotal  +  VALUES(MontoTotal)   
         ,ClientesAtendidos = ClientesAtendidos + VALUES(ClientesAtendidos)";

        //ENVIO A VENTAS X DEPARTAMENTO
        if (!$con->query($queryVxD)) {
          $msgError = str_replace("'", '"', $con->error);
          $sql = str_replace("'", "''", $queryVxD);
          $dw = array('modulo' => $_SERVER['SCRIPT_NAME'], 'proceso' => $_SERVER['REQUEST_URI']);

          $tmp = [];
          $tmp['IdSucursales'] = $json->IdSucursales;
          $tmp['IdCajas'] = $json->IdCajas;
          $tmp['IdUsuarios'] = 0;
          $tmp['IdTransacciones'] = $json->IdTransacciones;
          $tmp['Serie'] = $json->Serie;
          $tmp['NoDocumento'] = $json->NoDoc;
          generarLogDB($msgError, $sql, $con, $tmp, $dw);
        }

        //EXTRAS VENTAS x HORA
        //$margenH = 1 - ((($extra->Extra->CostoUnitario * $item->Producto->Cantidad) / ($extra->Extra->PrecioUnitario * $item->Producto->Cantidad)) * 100);
        $queryVxH = $queryVentasxHora . "($json->IdSucursales,DATE('$json->Fecha'),HOUR('$json->FechaH'),DAYNAME('$json->Fecha'),
        '$json->Hora','$json->Hora'," . $extra->Extra->PrecioUnitario * $item->Producto->Cantidad . ","
          . $extra->Extra->CostoUnitario * $item->Producto->Cantidad . ",1," . $item->Producto->Cantidad . "," . $item->Producto->Cantidad . ","
          .  $extra->Extra->PrecioUnitario * $item->Producto->Cantidad  / $item->Producto->Cantidad . "," .  $extra->Extra->PrecioUnitario * $item->Producto->Cantidad  . ",0,$json->IdEmpresas)
          ON DUPLICATE KEY UPDATE 
          Fin=VALUES(FIN),
          Total = Total + VALUES(Total), 
          TotalCosto = TotalCosto + VALUES(TotalCosto), 
          NumTransacciones = NumTransacciones + VALUES(NumTransacciones), 
          CantidadVendida = CantidadVendida + VALUES(CantidadVendida), 
          ItemsVendidos = ItemsVendidos + VALUES(ItemsVendidos), 
          PrecioProm = Total / CantidadVendida, 
          TicketProm = Total / NumTransacciones, 
          Margen = ((TotalCosto/Total))*100";

        //ENVIO A VENTAS X HORA
        if (!$con->query($queryVxH)) {
          $msgError = str_replace("'", '"', $con->error);
          $sql = str_replace("'", "''", $queryVxH);
          $dw = array('modulo' => $_SERVER['SCRIPT_NAME'], 'proceso' => $_SERVER['REQUEST_URI']);

          $tmp = [];
          $tmp['IdSucursales'] = $json->IdSucursales;
          $tmp['IdCajas'] = $json->IdCajas;
          $tmp['IdUsuarios'] = 0;
          $tmp['IdTransacciones'] = $json->IdTransacciones;
          $tmp['Serie'] = $json->Serie;
          $tmp['NoDocumento'] = $json->NoDoc;
          generarLogDB($msgError, $sql, $con, $tmp, $dw);
        }
      }
    }
    $i++; //AUTOIMCREMENTO
  }



  //INICIO DE KARDEX
  $queryk = "INSERT INTO kardexproductossucursales(Upc,IdSucursales,IdDepartamentos,Fecha,FechaHora,IdTransacciones,
        SerieDocumento,NoDocumento,DescCorta,CostoUnitario,PrecioUnitario,CostoTotal,PrecioTotal,IdCajas,ExistenciaAnterior,ExistenciaActual,CantidadSalida)
          VALUES";
  $z = 0;
  foreach ($productos as $item) {
    if ($z > 0) {
      $queryk = $queryk . ","; //VALIDACION PARA LA , EN LA LISTA DEL INSERT
    }
    //OBTENER RECETAS Y DATOS
    $detrec = getreceta($item->Producto->Upc, $json->IdSucursales, $con);
    //PRODUCTOS
    $existenciaActual = $detrec[0]['Existencia'] - $item->Producto->Cantidad;
    $queryk = $queryk . "('" . $item->Producto->Upc . "','$json->IdSucursales','" . $item->Producto->IdDepartamentos . "','$json->Fecha','$json->FechaH','$json->IdTransacciones',
              '$json->Serie','$json->NoDoc','" . $item->Producto->DescCorta . "','" . $item->Producto->CostoUnitario . "','" . $item->Producto->PrecioUnitario . "',
                '" . $item->Producto->TotalCosto . "','" . $item->Producto->Total . "','$json->IdCajas','" . $detrec[0]['Existencia'] . "','" . $existenciaActual . "','" . $item->Producto->Cantidad . "')";
    //REBAJAR EXISTENCIA DE PRODUCTO
    updateExistencia($item->Producto->Upc, $json->IdSucursales, $con, $item->Producto->Cantidad, "-");

    //COMPROBACION QUE SEA PRODUCTO CON RECETA
    if ($detrec[0]['IdTiProd'] == 2 and $detrec[0]['UpcR'] != 'NO HAY RECETA') {
      foreach ($detrec as $value) { //FOR PARA EL INSERT DE LAS RECETAS NIVEL 1
        $salida=$value['Unidades']*$item->Producto->Cantidad;
        $existenciaAc = $value['ExisP'] - $salida;
        $queryk = $queryk . ",('" . $value['UpcR'] . "','$json->IdSucursales','" . $value['IdDep'] . "','$json->Fecha',
          '$json->FechaH','$json->IdTransacciones','$json->Serie','$json->NoDoc','" . $value['DescCorta'] . "',
          '" . $value['Costo'] . "','0','" . $value['Total'] . "','0','$json->IdCajas','" . $value['ExisP'] . "',
          '" . $existenciaAc . "','" . $salida . "')";
        //REBAJAR EXISTENCIA DE RECETA NIVEL1
        updateExistencia($value['UpcR'], $json->IdSucursales, $con, ($value['Unidades']*$item->Producto->Cantidad), "-");
        //obtener datos del ingreditente por si llevara recete
        $rec2 = getreceta($value['UpcR'], $json->IdSucursales, $con);
        //la receta lleva otra receta adentro
        if ($rec2[0]['IdTiProd'] == 2) {
          foreach ($rec2 as $v2) { //FOR PARA EL INSERT DE LAS RECETAS NIVEL 2
            $salida=$v2['Unidades']*$item->Producto->Cantidad;
            $existenciaAc = $v2['ExisP'] - $salida;
            $queryk = $queryk . ",('" . $v2['UpcR'] . "','$json->IdSucursales','" . $v2['IdDep'] . "','$json->Fecha','$json->FechaH','$json->IdTransacciones',
                '$json->Serie','$json->NoDoc','" . $v2['DescCorta'] . "','" . $v2['Costo'] . "','0','" . $v2['Total'] . "','0',
                '$json->IdCajas','" . $v2['ExisP'] . "','" . $existenciaAc . "','" . $salida . "')";
            //REBAJAR EXISTENCIA DE RECETA NIVEL1
            updateExistencia($v2['UpcR'], $json->IdSucursales, $con, $salida, "-");
          }
        } //FIN NIVEL 2 RECETAS
      } //FIN NIVEL 1 RECETAS
    }
    //COMPLEMENTOS
    if (!empty($item->Producto->Complementos)) { //IF DE VALIDADCION QUE ESTE VACIO
      foreach ($item->Producto->Complementos as $comp) { //FOR PARA AGREGAR COMPLEMENTOS A QUERY
        //OBTENER RECETAS Y DATOS
        $dcomp = getreceta($comp->Complemento->UpcComple, $json->IdSucursales, $con);
        $cantidadAc = $comp->Complemento->Unidad * $item->Producto->Cantidad;
        $existenciaAc = $dcomp[0]['Existencia'] - $cantidadAc;
        $queryk = $queryk . ",('" . $comp->Complemento->UpcComple . "','$json->IdSucursales','" . $item->Producto->IdDepartamentos . "',
            '$json->Fecha','$json->FechaH','$json->IdTransacciones','$json->Serie','$json->NoDoc',
            '" . $comp->Complemento->DescCorta . "','" . $comp->Complemento->CostoUnitario . "',
            '" . $comp->Complemento->PrecioUnitario . "','" . $comp->Complemento->CostoUnitario * $item->Producto->Cantidad . "',
            '" . $comp->Complemento->PrecioUnitario * $item->Producto->Cantidad . "','$json->IdCajas','" . $dcomp[0]['Existencia'] . "',
            '" . $existenciaAc . "','" . $cantidadAc . "')";
        //REBAJAR EXISTENCIA COMPLEMENTOS
        updateExistencia($comp->Complemento->UpcComple, $json->IdSucursales, $con, $cantidadAc, "-");
        //EL COMPLEMENTO lleva receta adentro
        if ($dcomp[0]['IdTiProd'] == 2) {
          foreach ($dcomp as $r1) { //FOR PARA EL INSERT DE LAS RECETAS NIVEL 1
            $cantidadAc = $r1['Unidades'] * $item->Producto->Cantidad;
            $existenciaAc = $r1['ExisP'] - $cantidadAc;
            $queryk = $queryk . ",('" . $r1['UpcR'] . "','$json->IdSucursales','" . $r1['IdDep'] . "','$json->Fecha',
            '$json->FechaH','$json->IdTransacciones','$json->Serie','$json->NoDoc',
            '" . $r1['DescCorta'] . "','" . $r1['Costo'] . "','0','" . $r1['Total'] . "','0',
                '$json->IdCajas','" . $r1['ExisP'] . "','" . $existenciaAc . "','" . $cantidadAc . "')";
            //REBAJAR EXISTENCIA COMPLEMENTOS
            updateExistencia($r1['UpcR'], $json->IdSucursales, $con, $cantidadAc, "-");
          }
        } //FIN NIVEL 1 RECETAS
      }
    }
    //EXTRAS
    if (!empty($item->Producto->Extras)) { //IF DE VALICADION QUE ESTE VACIO
      foreach ($item->Producto->Extras as $extra) { //FOR PARA AGREGAR EXTRAS A QUERY
        //OBTENER RECETAS Y DATOS
        $cantidadAc = $extra->Extra->Unidad * $item->Producto->Cantidad;
        $dextra = getreceta($extra->Extra->UpcExt, $json->IdSucursales, $con);
        $existenciaAc = $dextra[0]['Existencia'] - $cantidadAc;
        $queryk = $queryk . ",('" . $extra->Extra->UpcExt . "','$json->IdSucursales','" . $item->Producto->IdDepartamentos . "',
            '$json->Fecha','$json->FechaH','$json->IdTransacciones','$json->Serie','$json->NoDoc',
            '" . $extra->Extra->DescCorta . "','" . $extra->Extra->CostoUnitario . "',
            '" . $extra->Extra->PrecioUnitario . "','" . $extra->Extra->CostoUnitario * $item->Producto->Cantidad . "',
            '" . $extra->Extra->PrecioUnitario * $item->Producto->Cantidad . "','$json->IdCajas','" . $dextra[0]['Existencia'] . "',
            '" . $existenciaAc . "','" . $cantidadAc . "')";
        //REBAJAR EXISTENCIA COMPLEMENTOS
        updateExistencia($extra->Extra->UpcExt, $json->IdSucursales, $con, $cantidadAc, "-");
        //EL COMPLEMENTO lleva receta adentro
        if ($dextra[0]['IdTiProd'] == 2) {
          foreach ($dextra as $r1) { //FOR PARA EL INSERT DE LAS RECETAS NIVEL 1
            $cantidadAc = $r1['Unidades'] * $item->Producto->Cantidad;
            $existenciaAc = $r1['ExisP'] - $cantidadAc;

            $queryk = $queryk . ",('" . $r1['UpcR'] . "','$json->IdSucursales','" . $r1['IdDep'] . "','$json->Fecha','$json->FechaH','$json->IdTransacciones',
                '$json->Serie','$json->NoDoc','" . $r1['DescCorta'] . "','" . $r1['Costo'] . "','0','" . $r1['Total'] . "','0',
                '$json->IdCajas','" . $r1['ExisP'] . "','" . $existenciaAc . "','" . $cantidadAc . "')";
            //REBAJAR EXISTENCIA COMPLEMENTOS
            updateExistencia($r1['UpcR'], $json->IdSucursales, $con, $cantidadAc, "-");
          }
        } //FIN NIVEL 1 RECETAS
      }
    }
    $z++;
  }
  if (!$con->query($queryk)) { //QUERY PARA INSERTAR KARDEX
    $msgError = str_replace("'", '"', $con->error);
    $sql = str_replace("'", "''", $query1);
    $dw = array('modulo' => $_SERVER['SCRIPT_NAME'], 'proceso' => $_SERVER['REQUEST_URI']);

    $tmp = [];
    $tmp['IdSucursales'] = $json->IdSucursales;
    $tmp['IdCajas'] = $json->IdCajas;
    $tmp['IdUsuarios'] = 0;
    $tmp['IdTransacciones'] = $json->IdTransacciones;
    $tmp['Serie'] = $json->Serie;
    $tmp['NoDocumento'] = $json->NoDoc;
    generarLogDB($msgError, $sql, $con, $tmp, $dw);
  }

  $con->close();
} else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  require_once("../db.php");
  $idTr = $_GET['idTr'];
  $rows = array();
  $query = "SELECT Id,IdTransacciones,IdDepartamentos,Cantidad,Upc,DescCorta,PrecioUnitario,Total,CostoUnitario,
      TotalCosto,UpcReferencia,IdReferencia,TipoProducto,TotalDescuento
      FROM detalletransacciones WHERE IdTransacciones=" . $idTr . " ORDER BY TipoProducto ASC";
  $result = $con->query($query);
  if ($result->num_rows > 0) {
    // VER CON QUE RECIBIR DATA
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
