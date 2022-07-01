<?php

	if($_SERVER['REQUEST_METHOD']== 'POST'){
    require_once("../db.php");
    require_once("../Functions/functionsDb.php");
    require_once("../Functions/errores.php");
    $tpPago=$_GET['tpPago'];
		$json = json_decode(file_get_contents('php://input'));
    
    switch ($tpPago) {
      //FACTURA
      case '1':
        anularFactura($json->IdTransaccion,$con);
        
       //UPDATE SAT FEL
        $estado="";
        if ($json->Codigo==1) {
          $estado="ANULADO";
        }else{
          $estado="PENDIENTE";
        }

        $queryS="UPDATE sat_fel_emisiones
                SET AnulacionRespuestaCodigo='".$json->Codigo."',
                AnulacionRespuestaMensaje='".$json->Mensaje."',
                AnulacionRespuestaData='".$json->ResponseDATA1."',
                AnulacionEstado='".$estado."',
                AnulacionXml='".$json->xml."'
                WHERE IdTransacciones=".$json->IdTransaccion."
                AND InternalId='".$json->InternalId."'";
        if(!$con->query($queryS)){//UPDATE SAT_FEl
          $msgError = str_replace("'",'"', $con->error);
          $sql = str_replace("'","''", $queryS);
          $dw= array('modulo' => $_SERVER['SCRIPT_NAME'],'proceso'=>$_SERVER['REQUEST_URI']);
    
          $tmp = [];
          $tmp['IdSucursales'] = $json->IdSucursales;
          $tmp['IdCajas'] = $json->IdCajas;
          $tmp['IdUsuarios']=0;
          $tmp['IdTransacciones']=$json->IdTransaccion;
          $tmp['Serie']=$json->Serie;
          $tmp['NoDocumento']=$json->NoDoc;
          generarLogDB($msgError,$sql,$con,$tmp,$dw);
        }
        break;
      
      case 2:
      //TICKET
        anularFactura($json->IdTransaccion,$con);
        break;
    }

      $productos= $json->Productos;

      //UPDATE REPORTE DE VENTAS
      foreach ($productos as $item) {
      
        //PRODUCTOS VENTAS DIARIAS
        $queryVD = "UPDATE ventasdiarias SET
                    Cantidad = Cantidad - ".$item->Producto->Cantidad."
                    ,CostoTotal = CostoTotal - ".$item->Producto->TotalCosto."    
                    ,MontoTotal = MontoTotal - ".$item->Producto->Total."   
                    ,ClientesAtendidos = ClientesAtendidos - 1
                    WHERE Upc='".$item->Producto->Upc."' AND IdSucursales =$json->IdSucursales
                    AND Fecha='$json->Fecha'";
    
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
        $queryVdD = "UPDATE ventasdeldia SET
                    Cantidad = Cantidad - ".$item->Producto->Cantidad."
                    ,CostoTotal = CostoTotal - ".$item->Producto->TotalCosto."    
                    ,MontoTotal = MontoTotal - ".$item->Producto->Total."   
                    ,ClientesAtendidos = ClientesAtendidos - 1
                    WHERE Upc='".$item->Producto->Upc."' AND IdSucursales =$json->IdSucursales
                    AND Fecha='$json->Fecha'";
    
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
        $queryVxD = "UPDATE ventasxdepartamento SET
                    Cantidad = Cantidad - ".$item->Producto->Cantidad."
                    ,CostoTotal = CostoTotal - ".$item->Producto->TotalCosto."    
                    ,MontoTotal = MontoTotal - ".$item->Producto->Total."   
                    ,ClientesAtendidos = ClientesAtendidos - 1
                    WHERE IdDepartamentos='".$item->Producto->IdDepartamentos."' AND IdSucursales =$json->IdSucursales
                    AND Fecha='$json->Fecha'";
    
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
        $queryVxH = "UPDATE ventasxhora SET
                    TotalCosto = TotalCosto - ".$item->Producto->TotalCosto."    
                    ,Total = Total - ".$item->Producto->Total."
                    ,NumTransacciones= NumTransacciones - 1   
                    ,CantidadVendida = CantidadVendida - ".$item->Producto->Cantidad."
                    ,ItemsVendidos = ItemsVendidos - ".$item->Producto->Cantidad."
                    ,PrecioProm = Total / CantidadVendida
                    ,TicketProm = Total / NumTransacciones
                    ,Margen = ((TotalCosto/Total))*100
                    WHERE IdSucursales =$json->IdSucursales AND Hora = HOUR('$json->FechaH')
                    AND Fecha='$json->Fecha'";
    
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
            //COMPLEMENTOS VENTAS DIARIAS
              $queryVD = "UPDATE ventasdiarias SET
              Cantidad = Cantidad - ".$item->Producto->Cantidad."
              ,CostoTotal = CostoTotal - ".$comp->Complemento->CostoUnitario * $item->Producto->Cantidad."    
              ,MontoTotal = MontoTotal - ".$comp->Complemento->PrecioUnitario * $item->Producto->Cantidad."   
              ,ClientesAtendidos = ClientesAtendidos - 1
              WHERE Upc='".$comp->Complemento->UpcComple."' AND IdSucursales =$json->IdSucursales
              AND Fecha='$json->Fecha'";
    
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
              $queryVdD = "UPDATE ventasdeldia SET
                    Cantidad = Cantidad - ".$item->Producto->Cantidad."
                    ,CostoTotal = CostoTotal - ".$comp->Complemento->CostoUnitario * $item->Producto->Cantidad."    
                    ,MontoTotal = MontoTotal - ".$comp->Complemento->PrecioUnitario * $item->Producto->Cantidad."   
                    ,ClientesAtendidos = ClientesAtendidos - 1
                    WHERE Upc='".$comp->Complemento->UpcComple."' AND IdSucursales =$json->IdSucursales
                    AND Fecha='$json->Fecha'";
    
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
             $queryVxD = "UPDATE ventasxdepartamento SET
                    Cantidad = Cantidad - ".$item->Producto->Cantidad."
                    ,CostoTotal = CostoTotal - ".$comp->Complemento->CostoUnitario * $item->Producto->Cantidad."    
                    ,MontoTotal = MontoTotal - ".$comp->Complemento->PrecioUnitario * $item->Producto->Cantidad."   
                    ,ClientesAtendidos = ClientesAtendidos - 1
                    WHERE IdDepartamentos='".$item->Producto->IdDepartamentos."' AND IdSucursales =$json->IdSucursales
                    AND Fecha='$json->Fecha'";
    
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
              $queryVxH = "UPDATE ventasxhora SET
                    TotalCosto = TotalCosto - ".$comp->Complemento->CostoUnitario * $item->Producto->Cantidad ."    
                    ,Total = Total - ".$comp->Complemento->PrecioUnitario * $item->Producto->Cantidad."
                    ,NumTransacciones= NumTransacciones - 1   
                    ,CantidadVendida = CantidadVendida - ".$item->Producto->Cantidad."
                    ,ItemsVendidos = ItemsVendidos - ".$item->Producto->Cantidad."
                    ,PrecioProm = Total / CantidadVendida
                    ,TicketProm = Total / NumTransacciones
                    ,Margen = ((TotalCosto/Total))*100
                    WHERE IdSucursales =$json->IdSucursales AND Hora = HOUR('$json->FechaH')
                    AND Fecha='$json->Fecha'";
    
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
    
            //EXTRAS VENTAS DIARIAS
            $queryVD = "UPDATE ventasdiarias SET
              Cantidad = Cantidad - ".$item->Producto->Cantidad."
              ,CostoTotal = CostoTotal - ".$extra->Extra->CostoUnitario * $item->Producto->Cantidad."    
              ,MontoTotal = MontoTotal - ".$extra->Extra->PrecioUnitario * $item->Producto->Cantida."   
              ,ClientesAtendidos = ClientesAtendidos - 1
              WHERE Upc='".$extra->Extra->UpcExt."' AND IdSucursales =$json->IdSucursales
              AND Fecha='$json->Fecha'";
    
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
              $queryVdD = "UPDATE ventasdeldia SET
                    Cantidad = Cantidad - ".$item->Producto->Cantidad."
                    ,CostoTotal = CostoTotal - ".$extra->Extra->CostoUnitario * $item->Producto->Cantidad."    
                    ,MontoTotal = MontoTotal - ".$extra->Extra->PrecioUnitario * $item->Producto->Cantidad."   
                    ,ClientesAtendidos = ClientesAtendidos - 1
                    WHERE Upc='".$extra->Extra->UpcExt."' AND IdSucursales =$json->IdSucursales
                    AND Fecha='$json->Fecha'";
    
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
            $queryVxD = "UPDATE ventasxdepartamento SET
                    Cantidad = Cantidad - ".$item->Producto->Cantidad."
                    ,CostoTotal = CostoTotal - ".$extra->Extra->CostoUnitario * $item->Producto->Cantidad."    
                    ,MontoTotal = MontoTotal - ".$extra->Extra->PrecioUnitario * $item->Producto->Cantidad."   
                    ,ClientesAtendidos = ClientesAtendidos - 1
                    WHERE IdDepartamentos='".$item->Producto->IdDepartamentos."' AND IdSucursales =$json->IdSucursales
                    AND Fecha='$json->Fecha'";
    
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
            $queryVxH = "UPDATE ventasxhora SET
              TotalCosto = TotalCosto - ".$extra->Extra->CostoUnitario * $item->Producto->Cantidad ."    
              ,Total = Total - ".$extra->Extra->PrecioUnitario * $item->Producto->Cantidad."
              ,NumTransacciones= NumTransacciones - 1   
              ,CantidadVendida = CantidadVendida - ".$item->Producto->Cantidad."
              ,ItemsVendidos = ItemsVendidos - ".$item->Producto->Cantidad."
              ,PrecioProm = Total / CantidadVendida
              ,TicketProm = Total / NumTransacciones
              ,Margen = ((TotalCosto/Total))*100
              WHERE IdSucursales =$json->IdSucursales AND Hora = HOUR('$json->FechaH')
              AND Fecha='$json->Fecha'";
    
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
      }

     //INICIO DE KARDEX
     $queryk="INSERT INTO kardexproductossucursales(Upc,IdSucursales,IdDepartamentos,Fecha,FechaHora,IdTransacciones,
        SerieDocumento,NoDocumento,DescCorta,CostoUnitario,PrecioUnitario,CostoTotal,PrecioTotal,IdCajas,ExistenciaAnterior,ExistenciaActual,CantidadEntrada)
          VALUES";
    $z=0;
    foreach($productos as $item) {
      if ($z>0) {
        $queryk=$queryk.",";//VALIDACION PARA LA , EN LA LISTA DEL INSERT
      }
      //OBTENER RECETAS Y DATOS
      $detrec = getreceta($item->Producto->Upc,$json->IdSucursales,$con);
      //PRODUCTOS
      $existenciaActual=$detrec[0]['Existencia']+$item->Producto->Cantidad;
      $queryk=$queryk."('".$item->Producto->Upc."','$json->IdSucursales','".$item->Producto->IdDepartamentos."','$json->Fecha','$json->FechaH','$json->IdTransaccion',
              '$json->Serie','$json->NoDoc','".$item->Producto->DescCorta."','".$item->Producto->CostoUnitario."','".$item->Producto->PrecioUnitario."',
                '".$item->Producto->TotalCosto."','".$item->Producto->Total."','$json->IdCajas','".$detrec[0]['Existencia']."','".$existenciaActual."','".$item->Producto->Cantidad."')";
      //REBAJAR EXISTENCIA DE PRODUCTO
      updateExistencia($item->Producto->Upc,$json->IdSucursales,$con,$item->Producto->Cantidad,"+");
    
      //COMPROBACION QUE SEA PRODUCTO CON RECETA
      if ($detrec[0]['IdTiProd']==2 AND $detrec[0]['UpcR']!='NO HAY RECETA') {
        foreach ($detrec as $value) {//FOR PARA EL INSERT DE LAS RECETAS NIVEL 1
          $existenciaAc=$value['ExisP']+$value['Unidades'];
          $queryk=$queryk.",('".$value['UpcR']."','$json->IdSucursales','".$value['IdDep']."','$json->Fecha',
          '$json->FechaH','$json->IdTransaccion','$json->Serie','$json->NoDoc','".$value['DescCorta']."',
          '".$value['Costo']."','0','".$value['Total']."','0','$json->IdCajas','".$value['ExisP']."',
          '". $existenciaAc."','".$value['Unidades']."')";
         //REBAJAR EXISTENCIA DE RECETA NIVEL1
          updateExistencia($value['UpcR'],$json->IdSucursales,$con,$value['Unidades'],"+");
          //obtener datos del ingreditente por si llevara recete
          $rec2=getreceta($value['UpcR'],$json->IdSucursales,$con);
          //la receta lleva otra receta adentro
          if ($rec2[0]['IdTiProd']==2) {
            foreach ($rec2 as $v2) {//FOR PARA EL INSERT DE LAS RECETAS NIVEL 2
              $existenciaAc=$v2['ExisP']-$v2['Unidades'];
            $queryk=$queryk.",('".$v2['UpcR']."','$json->IdSucursales','".$v2['IdDep']."','$json->Fecha','$json->FechaH','$json->IdTransaccion',
                '$json->Serie','$json->NoDoc','".$v2['DescCorta']."','".$v2['Costo']."','0','".$v2['Total']."','0',
                '$json->IdCajas','".$v2['ExisP']."','".$existenciaAc."','".$v2['Unidades']."')";
              //REBAJAR EXISTENCIA DE RECETA NIVEL1
            updateExistencia($v2['UpcR'],$json->IdSucursales,$con,$v2['Unidades'],"+");
            }
          }//FIN NIVEL 2 RECETAS
        }//FIN NIVEL 1 RECETAS
      }
      //COMPLEMENTOS
      if (!empty($item->Producto->Complementos)) {//IF DE VALIDADCION QUE ESTE VACIO
         foreach ($item->Producto->Complementos as $comp) {//FOR PARA AGREGAR COMPLEMENTOS A QUERY
            //OBTENER RECETAS Y DATOS
            $dcomp = getreceta($comp->Complemento->UpcComple,$json->IdSucursales,$con);
            $cantidadAc=$comp->Complemento->Unidad*$item->Producto->Cantidad;
            $existenciaAc=$dcomp[0]['Existencia']+$cantidadAc;
            $queryk=$queryk.",('".$comp->Complemento->UpcComple."','$json->IdSucursales','".$item->Producto->IdDepartamentos."',
            '$json->Fecha','$json->FechaH','$json->IdTransaccion','$json->Serie','$json->NoDoc',
            '".$comp->Complemento->DescCorta."','".$comp->Complemento->CostoUnitario."',
            '".$comp->Complemento->PrecioUnitario."','".$comp->Complemento->CostoUnitario*$item->Producto->Cantidad."',
            '".$comp->Complemento->PrecioUnitario*$item->Producto->Cantidad."','$json->IdCajas','".$dcomp[0]['Existencia']."',
            '".$existenciaAc."','".$cantidadAc."')";
            //REBAJAR EXISTENCIA COMPLEMENTOS
            updateExistencia($comp->Complemento->UpcComple,$json->IdSucursales,$con,$cantidadAc,"+");
            //EL COMPLEMENTO lleva receta adentro
          if ($dcomp[0]['IdTiProd']==2) {
            foreach ($dcomp as $r1) {//FOR PARA EL INSERT DE LAS RECETAS NIVEL 1
              $cantidadAc=$r1['Unidades']*$item->Producto->Cantidad;
              $existenciaAc=$r1['ExisP']+$cantidadAc;
            $queryk=$queryk.",('".$r1['UpcR']."','$json->IdSucursales','".$r1['IdDep']."','$json->Fecha',
            '$json->FechaH','$json->IdTransaccion','$json->Serie','$json->NoDoc',
            '".$r1['DescCorta']."','".$r1['Costo']."','0','".$r1['Total']."','0',
                '$json->IdCajas','".$r1['ExisP']."','".$existenciaAc."','".$cantidadAc."')";
                //REBAJAR EXISTENCIA COMPLEMENTOS
            updateExistencia($r1['UpcR'],$json->IdSucursales,$con,$cantidadAc,"+");
            }
          }//FIN NIVEL 1 RECETAS
         }
       }
       //EXTRAS
       if (!empty($item->Producto->Extras)) {//IF DE VALICADION QUE ESTE VACIO
          foreach ($item->Producto->Extras as $extra) {//FOR PARA AGREGAR EXTRAS A QUERY
             //OBTENER RECETAS Y DATOS
            $cantidadAc=$extra->Extra->Unidad*$item->Producto->Cantidad;
             $existenciaAc=$dextra[0]['Existencia']-$cantidadAc;
             
            $dextra = getreceta($extra->Extra->UpcExt,$json->IdSucursales,$con);
            $queryk=$queryk.",('".$extra->Extra->UpcExt."','$json->IdSucursales','".$item->Producto->IdDepartamentos."',
            '$json->Fecha','$json->FechaH','$json->IdTransaccion','$json->Serie','$json->NoDoc',
            '".$extra->Extra->DescCorta."','".$extra->Extra->CostoUnitario."',
            '".$extra->Extra->PrecioUnitario."','".$extra->Extra->CostoUnitario*$item->Producto->Cantidad."',
            '".$extra->Extra->PrecioUnitario*$item->Producto->Cantidad."','$json->IdCajas','".$dextra[0]['Existencia']."',
            '".$existenciaAc."','".$cantidadAc."')";
               //REBAJAR EXISTENCIA COMPLEMENTOS
            updateExistencia($extra->Extra->UpcExt,$json->IdSucursales,$con,$cantidadAc,"+");
            //EL COMPLEMENTO lleva receta adentro
          if ($dextra[0]['IdTiProd']==2) {
            foreach ($dextra as $r1) {//FOR PARA EL INSERT DE LAS RECETAS NIVEL 1
              $cantidadAc=$r1['Unidades']*$item->Producto->Cantidad;
              $existenciaAc=$r1['ExisP']+$cantidadAc;
              
            $queryk=$queryk.",('".$r1['UpcR']."','$json->IdSucursales','".$r1['IdDep']."','$json->Fecha','$json->FechaH','$json->IdTransaccion',
                '$json->Serie','$json->NoDoc','".$r1['DescCorta']."','".$r1['Costo']."','0','".$r1['Total']."','0',
                '$json->IdCajas','".$r1['ExisP']."','".$existenciaAc."','".$cantidadAc."')";
                   //REBAJAR EXISTENCIA COMPLEMENTOS
            updateExistencia($r1['UpcR'],$json->IdSucursales,$con,$cantidadAc,"+");
            }
          }//FIN NIVEL 1 RECETAS
          }
       }
      $z++;
    }//FIN KARDEX
    echo $queryk;
    if(!$con->query($queryk)){//QUERY DEL INSERT A KARDEX
      $msgError = str_replace("'",'"', $con->error);
      $sql = str_replace("'","''", $queryk);
      $dw= array('modulo' => $_SERVER['SCRIPT_NAME'],'proceso'=>$_SERVER['REQUEST_URI']);

      $tmp = [];
      $tmp['IdSucursales'] = $json->IdSucursales;
      $tmp['IdCajas'] = $json->IdCajas;
      $tmp['IdUsuarios']=0;
      $tmp['IdTransacciones']=$json->IdTransaccion;
      $tmp['Serie']=$json->Serie;
      $tmp['NoDocumento']=$json->NoDoc;
      generarLogDB($msgError,$sql,$con,$tmp,$dw);
    }

    //FORMAS DE PAGO
    //PAGOSDETALLES
    $queryP="INSERT INTO pagosdetalles(IdTransacciones,SerieDocumento,NoDocumento,IdCajas,IdSucursales,Fecha,FechaHora,
              CodigoPago,Monto,MontoRealPago,Parcial)VALUES";

    if (sizeof($json->FormasPago)>1) {
      $parcial=1;
    }else{
      $parcial=0;
    }

    foreach ($json->FormasPago as $fm) {
      $montotmp=$fm->Monto*-1;
      $totaltmp=$fm->MontoReal*-1;
      $queryPs=$queryP."($json->IdTransaccion,'$json->Serie',$json->NoDoc,$json->IdCajas,$json->IdSucursales,'$json->Fecha',
                  '$json->FechaH','$fm->CodigoPago',".$montotmp.",".$totaltmp.",$parcial)";

      if(!$con->query($queryPs)){//QUERY DEL INSERT A PAGOSDETALLES
        $msgError = str_replace("'",'"', $con->error);
        $sql = str_replace("'","''", $queryPs);
        $dw= array('modulo' => $_SERVER['SCRIPT_NAME'],'proceso'=>$_SERVER['REQUEST_URI']);
  
        $tmp = [];
        $tmp['IdSucursales'] = $json->IdSucursales;
        $tmp['IdCajas'] = $json->IdCajas;
        $tmp['IdUsuarios']=0;
        $tmp['IdTransacciones']=$json->IdTransaccion;
        $tmp['Serie']=$json->Serie;
        $tmp['NoDocumento']=$json->NoDoc;
        generarLogDB($msgError,$sql,$con,$tmp,$dw);
      }
    }
   
      
    $con->close();

	}
