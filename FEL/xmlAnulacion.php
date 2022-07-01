<?php

	if($_SERVER['REQUEST_METHOD']== 'POST'){
		$json = file_get_contents('php://input');
		$jo = json_decode($json);

		$arrayMain='<?xml version="1.0" encoding="utf-8"?><dte:GTAnulacionDocumento xmlns:dte="http://www.sat.gob.gt/dte/fel/0.1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Version="0.1"><dte:SAT><dte:AnulacionDTE  ID = "DatosCertificados">';
      $arrayMain.='<dte:DatosGenerales ID = "DatosAnulacion" NumeroDocumentoAAnular = "'.$jo->Autorizacion.'"';
      //DATOS GENERALES
        $arrayMain.=' NITEmisor = "'.$jo->NitEmisor.'"';
        $arrayMain.=' IDReceptor = "'.$jo->IdReceptor.'"';
        $arrayMain.=' FechaEmisionDocumentoAnular = "'.$jo->FechaEmision.'"'; 
        $arrayMain.=' FechaHoraAnulacion = "'.$jo->FechaAnulacion.'"'; 
        $arrayMain.=' MotivoAnulacion = "Cliente no esta Convencido" /></dte:AnulacionDTE></dte:SAT></dte:GTAnulacionDocumento>'; 
    echo $arrayMain;


	}
