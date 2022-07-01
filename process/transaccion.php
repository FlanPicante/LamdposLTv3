<?php

	if($_SERVER['REQUEST_METHOD']== 'POST'){
    require_once("../db.php");
    require_once("../Functions/functionsDb.php");
    require_once("../Functions/errores.php");
    $contingencia= $_GET['contingencia'];
    $tpPago=$_GET['tpPago'];
		$json = json_decode(file_get_contents('php://input'));
      //VARIABLES GENERALES
      $estado="";
      $idMesero="0";
      $tipoDoc="";
      $idcliente="0";
      //VALIDACION POR SI ES UNA ORDEN FACTURAR
    if($json->facOrden==1){
      foreach ($json->IdOrdenes as $value) {
        //producto de orden
        pagarProductoOrden($value->IdDtOrden,$con,3);

        //MARCAR PAGOS LAS ORDENES
        updateOrden($value->IdOrden,1,$con);
      }
      $idMesero=$json->IdVendedor;
    }

    //VALIDACION VERDADERA A IMPLEMENTAR
    if($json->Domicilio==1){
      $idcliente=$json->IdCliente;
    }

		if($contingencia==1 or $tpPago==2){
      //TICKET O CONTINGENCIA
      if ($tpPago==2) {
        $tipoDoc=2;
      }else if ($contingencia==1) {
        $tipoDoc=4;
      }
      //UPDATE DE RESOLUCIONES - NUMERACIONES
      if ($tpPago==2) {
        //TICKET
      $query = "INSERT INTO transacciones(Idcajas,IdSucursales,IdResoluciones,Serie,NoDocumento,
                              TiposDocumentos,IdUsuarios,Fecha,Inicio,Fin,Total,MontoRecibido,Cambio,
                              TotalProductos,Estado,NIT,Nombre,Direccion,IdEmpresas,IdVendedores,SubTotal,Propina,TotalDescuento,IdClientes,Version)
                VALUES($json->IdCajas,$json->IdSucursales,$json->IdResoluciones,'$json->Serie',$json->NoDocumento,
                $tipoDoc,$json->IdUsuarios,'$json->Fecha','$json->Inicio','$json->Inicio',$json->Total,$json->MontoRecibido,$json->Cambio,
                        $json->TotalProductos,$json->Estado,'$json->Nit','$json->Nombre','$json->Direccion',$json->IdEmpresas,$idMesero,
                        $json->SubTotal,$json->Propina,$json->TotalDescuento,$idcliente,'$json->Version')";
        if(!$con->query($query)){
          //MANEJO ERRORES
          $msgError = str_replace("'",'"', $con->error);
          $sql = str_replace("'","''", $query);
          $dw= array('modulo' => $_SERVER['SCRIPT_NAME'],'proceso'=>$_SERVER['REQUEST_URI']);
  
          $tmp = [];
          $tmp['IdSucursales'] = $json->IdSucursales;
          $tmp['IdCajas'] = $json->IdCajas;
          $tmp['IdUsuarios']=$json->IdUsuarios;
          $tmp['IdTransacciones']=0;
          $tmp['Serie']=$json->Serie;
          $tmp['NoDocumento']=$json->NoDocumento;
          generarLogDB($msgError,$sql,$con,$tmp,$dw);
        }
        $condicion="Fecha='$json->Fecha' AND IdCajas=$json->IdCajas AND Inicio='$json->Inicio' AND NoDocumento=$json->NoDocumento";
        $idtransaccion = getid("transacciones",$condicion,$con);
        $queryU="UPDATE numeracion SET Numero=Numero+1 WHERE Id=$json->IdResoluciones";
        $con->query($queryU);
      }else{
        //CONTINGENCIA
        $estado='PENDIENTE';
        //TRANSACCION
      $query = "INSERT INTO transacciones(Idcajas,IdSucursales,IdResoluciones,Serie,NoDocumento,
                              TiposDocumentos,IdUsuarios,Fecha,Inicio,Fin,Total,MontoRecibido,Cambio,
                              TotalProductos,Estado,NIT,Nombre,Direccion,IdEmpresas,IdVendedores,InternalId,SubTotal,Propina,TotalDescuento,IdClientes,Version)
                VALUES($json->IdCajas,$json->IdSucursales,$json->IdResoluciones,'$json->Serie',$json->NoDocumento,
                $tipoDoc,$json->IdUsuarios,'$json->Fecha','$json->Inicio','$json->Inicio',$json->Total,$json->MontoRecibido,$json->Cambio,
                        $json->TotalProductos,$json->Estado,'$json->Nit','$json->Nombre','$json->Direccion',$json->IdEmpresas,$idMesero,'$json->InternalId',
                        $json->SubTotal,$json->Propina,$json->TotalDescuento,$idcliente,'$json->Version')";
     if(!$con->query($query)){
      //MANEJO ERRORES
      $msgError = str_replace("'",'"', $con->error);
      $sql = str_replace("'","''", $query);
      $dw= array('modulo' => $_SERVER['SCRIPT_NAME'],'proceso'=>$_SERVER['REQUEST_URI']);

      $tmp = [];
      $tmp['IdSucursales'] = $json->IdSucursales;
      $tmp['IdCajas'] = $json->IdCajas;
      $tmp['IdUsuarios']=$json->IdUsuarios;
      $tmp['IdTransacciones']=0;
      $tmp['Serie']=$json->Serie;
      $tmp['NoDocumento']=$json->NoDocumento;
      generarLogDB($msgError,$sql,$con,$tmp,$dw);
    }
    $condicion="Fecha='$json->Fecha' AND IdCajas=$json->IdCajas AND Inicio='$json->Inicio' AND NoDocumento=$json->NoDocumento";
        $idtransaccion = getid("transacciones",$condicion,$con);
        $queryU="UPDATE resoluciones SET SiguienteNumero=SiguienteNumero+1 WHERE Id=$json->IdResoluciones";
       $con->query($queryU);
       //INSERT SAT_FEL_EMISIONES
        $queryS="INSERT INTO sat_fel_emisiones(Fecha,IdSucursales,IdCajas,IdTransacciones,NitReceptor,FechaEmision,NumeroAcceso,Estado,
                RespuestaMensaje,RespuestaData,Xml,InternalId) VALUES('$json->Inicio',$json->IdSucursales,$json->IdCajas,$idtransaccion,'$json->Nit','$json->Inicio',$json->NoDocumento,'$estado','$json->mensaje',
              '$json->response','$json->xml','$json->InternalId')";
      if(!$con->query($queryS)){
        //MANEJO ERRORES
        $msgError = str_replace("'",'"', $con->error);
        $sql = str_replace("'","''", $queryS);
        $dw= array('modulo' => $_SERVER['SCRIPT_NAME'],'proceso'=>$_SERVER['REQUEST_URI']);
  
        $tmp = [];
        $tmp['IdSucursales'] = $json->IdSucursales;
        $tmp['IdCajas'] = $json->IdCajas;
        $tmp['IdUsuarios']=$json->IdUsuarios;
        $tmp['IdTransacciones']=0;
        $tmp['Serie']=$json->Serie;
        $tmp['NoDocumento']=$json->NoDocumento;
        generarLogDB($msgError,$sql,$con,$tmp,$dw);
       }
      }
    }else{
      //FACTURA
      $estado='CERTIFICADO';
      $query = "INSERT INTO transacciones(Idcajas,IdSucursales,IdResoluciones,Serie,NoDocumento,
                              TiposDocumentos,IdUsuarios,Fecha,Inicio,Fin,Total,MontoRecibido,Cambio,
                              TotalProductos,Estado,NIT,Nombre,Direccion,AutorizacionFEL,IdEmpresas,IdVendedores,InternalId,FelFechaCertificacion,
                              SubTotal,Propina,TotalDescuento,IdClientes,Version)
              VALUES($json->IdCajas,$json->IdSucursales,$json->IdResoluciones,'$json->Serie',$json->NoDocumento,
                        4,$json->IdUsuarios,'$json->Fecha','$json->Inicio','$json->Inicio',$json->Total,$json->MontoRecibido,$json->Cambio,
                        $json->TotalProductos,$json->Estado,'$json->Nit','$json->Nombre','$json->Direccion',
                        '$json->AutorizacionFEL',$json->IdEmpresas,$idMesero,'$json->InternalId','$json->FelFechaCertificacion',
                        $json->SubTotal,$json->Propina,$json->TotalDescuento,$idcliente,'$json->Version')";
      if(!$con->query($query)){
        //MANEJO ERRORES
        $msgError = str_replace("'",'"', $con->error);
        $sql = str_replace("'","''", $query);
        $dw= array('modulo' => $_SERVER['SCRIPT_NAME'],'proceso'=>$_SERVER['REQUEST_URI']);

        $tmp = [];
        $tmp['IdSucursales'] = $json->IdSucursales;
        $tmp['IdCajas'] = $json->IdCajas;
        $tmp['IdUsuarios']=$json->IdUsuarios;
        $tmp['IdTransacciones']=0;
        $tmp['Serie']=$json->Serie;
        $tmp['NoDocumento']=$json->NoDocumento;
        generarLogDB($msgError,$sql,$con,$tmp,$dw);
      }
      $condicion="Fecha='$json->Fecha' AND IdCajas=$json->IdCajas AND Inicio='$json->Inicio' AND NoDocumento=$json->NoDocumento";
        $idtransaccion = getid("transacciones",$condicion,$con);
      //INSERT SAT_FEL_EMISIONES
      $queryS="INSERT INTO sat_fel_emisiones(Fecha,IdSucursales,IdCajas,IdTransacciones,NitReceptor,FechaEmision,Estado,
                RespuestaMensaje,Xml,InternalId) VALUES('$json->Inicio',$json->IdSucursales,$json->IdCajas,$idtransaccion,
                '$json->Nit','$json->Inicio','$estado','$json->mensaje','$json->xml','$json->InternalId')";
       if(!$con->query($queryS)){
        //MANEJO ERRORES
        $msgError = str_replace("'",'"', $con->error);
        $sql = str_replace("'","''", $queryS);
        $dw= array('modulo' => $_SERVER['SCRIPT_NAME'],'proceso'=>$_SERVER['REQUEST_URI']);

        $tmp = [];
        $tmp['IdSucursales'] = $json->IdSucursales;
        $tmp['IdCajas'] = $json->IdCajas;
        $tmp['IdUsuarios']=$json->IdUsuarios;
        $tmp['IdTransacciones']=0;
        $tmp['Serie']=$json->Serie;
        $tmp['NoDocumento']=$json->NoDocumento;
        generarLogDB($msgError,$sql,$con,$tmp,$dw);
      }
      //UPDATE DE RESOLUCIONES
      $queryU="UPDATE resoluciones SET SiguienteNumero=SiguienteNumero+1 WHERE Id=$json->IdResoluciones";
      $con->query($queryU);
    }

    //PAGOSDETALLES
    $queryP="INSERT INTO pagosdetalles(IdTransacciones,SerieDocumento,NoDocumento,IdUsuarios,IdCajas,IdSucursales,Fecha,FechaHora,
              CodigoPago,Monto,MontoRealPago,Parcial,Referencia)VALUES";

    if (sizeof($json->FormasPago)>1) {
      $parcial=1;
    }else{
      $parcial=0;
    }

    foreach ($json->FormasPago as $fm) {
      if($fm->CodigoPago=="TJ"){
        $datos=$fm->Datos;
      }else{
        $datos=" ";
      }
      $queryPs=$queryP."($idtransaccion,'$json->Serie',$json->NoDocumento,$json->IdUsuarios,$json->IdCajas,$json->IdSucursales,'$json->Fecha',
                  '$json->Inicio','$fm->CodigoPago',$fm->Monto,$fm->MontoReal,$parcial,'$datos')";
      if(!$con->query($queryPs)){
        //MANEJO ERRORES
        $msgError = str_replace("'",'"', $con->error);
        $sql = str_replace("'","''", $queryPs);
        $dw= array('modulo' => $_SERVER['SCRIPT_NAME'],'proceso'=>$_SERVER['REQUEST_URI']);

        $tmp = [];
        $tmp['IdSucursales'] = $json->IdSucursales;
        $tmp['IdCajas'] = $json->IdCajas;
        $tmp['IdUsuarios']=$json->IdUsuarios;
        $tmp['IdTransacciones']=$idtransaccion;
        $tmp['Serie']=$json->Serie;
        $tmp['NoDocumento']=$json->NoDocumento;
        generarLogDB($msgError,$sql,$con,$tmp,$dw);
      }
    }

    $noOrden=getordenNo($json->IdSucursales, $con);

    $queryUpdate="UPDATE transacciones 
    SET NoOrden=$noOrden 
    WHERE Id= $idtransaccion 
    AND IdCajas=$json->IdCajas 
    AND IdSucursales= $json->IdSucursales";

    if(!$con->query($queryUpdate)){
      //MANEJO ERRORES
      $msgError = str_replace("'",'"', $con->error);
      $sql = str_replace("'","''", $queryUpdate);
      $dw= array('modulo' => $_SERVER['SCRIPT_NAME'],'proceso'=>$_SERVER['REQUEST_URI']);

      $tmp = [];
      $tmp['IdSucursales'] = $json->IdSucursales;
      $tmp['IdCajas'] = $json->IdCajas;
      $tmp['IdUsuarios']=$json->IdUsuarios;
      $tmp['IdTransacciones']=$idtransaccion;
      $tmp['Serie']=$json->Serie;
      $tmp['NoDocumento']=$json->NoDocumento;
      generarLogDB($msgError,$sql,$con,$tmp,$dw);
    }
    
    $con->close();

    echo $idtransaccion;

	}
