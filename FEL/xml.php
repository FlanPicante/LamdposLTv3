<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  require_once("../Functions/functionsDb.php");
  $json = file_get_contents('php://input');
  $jo = json_decode($json);

  $arrayMain = '<?xml version="1.0"?><dte:GTDocumento xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:dte="http://www.sat.gob.gt/dte/fel/0.2.0" Version="0.1"><dte:SAT ClaseDocumento="dte"><dte:DTE ID="DatosCertificados"><dte:DatosEmision ID="DatosEmision">';
  //echo $arrayheader1;
  /*if($contingencia==1){
        $arraycontin='<dte:DatosGenerales Tipo="FACT" FechaHoraEmision="'.$jo->Fecha.'T'.$jo->Hora.'" CodigoMoneda="GTQ" NumeroAcceso="'.$jo->NumAcces.'"/>';
      }else{
        $arraycontin='<dte:DatosGenerales Tipo="FACT" FechaHoraEmision="'.$jo->Fecha.'T'.$jo->Hora.'" CodigoMoneda="GTQ"/>';
      }*/
  $arrayMain .= '<dte:DatosGenerales Tipo="FACT" FechaHoraEmision="' . $jo->Fecha . 'T' . $jo->Hora . '" CodigoMoneda="GTQ" NumeroAcceso="' . $jo->NumAcces . '"/>';
  //echo $arraycontin;
  $arrayMain .= '<dte:Emisor NITEmisor="' . $jo->NitEmisor . '" NombreEmisor="' . $jo->NombreE . '" CodigoEstablecimiento="' . $jo->CodEsta . '" NombreComercial="' . $jo->NombreComer . '" AfiliacionIVA="GEN"><dte:DireccionEmisor><dte:Direccion>' . $jo->Direccion . '</dte:Direccion><dte:CodigoPostal>' . $jo->CodPost . '</dte:CodigoPostal><dte:Municipio>' . $jo->Municipio . '</dte:Municipio><dte:Departamento>' . $jo->Departamento . '</dte:Departamento><dte:Pais>GT</dte:Pais></dte:DireccionEmisor></dte:Emisor><dte:Receptor NombreReceptor="' . $jo->NombreClien . '" IDReceptor="' . $jo->Nit . '"><dte:DireccionReceptor><dte:Direccion>' . $jo->DirecClie . '.</dte:Direccion><dte:CodigoPostal>01010</dte:CodigoPostal><dte:Municipio>GUATEMALA</dte:Municipio><dte:Departamento>GUATEMALA</dte:Departamento><dte:Pais>GT</dte:Pais></dte:DireccionReceptor></dte:Receptor><dte:Frases><dte:Frase TipoFrase="' . $jo->TipFras . '" CodigoEscenario="' . $jo->CodEscen . '"/></dte:Frases><dte:Items>';
  //echo $arrayheader2;
  $items = $jo->carrito;
  $i = 0;
  foreach ($items as $value) {
    $i++;
    $arrayMain .= '<dte:Item NumeroLinea="' . $i . '" BienOServicio="B"><dte:Cantidad>' . $value->producto->cantidad . '</dte:Cantidad><dte:UnidadMedida>U</dte:UnidadMedida><dte:Descripcion>' . $value->producto->desc . '</dte:Descripcion><dte:PrecioUnitario>' . $value->producto->preciouni . '</dte:PrecioUnitario><dte:Precio>' . $value->producto->preciototal . '</dte:Precio><dte:Descuento>' . $value->producto->descuento . '</dte:Descuento><dte:Impuestos><dte:Impuesto><dte:NombreCorto>IVA</dte:NombreCorto><dte:CodigoUnidadGravable>1</dte:CodigoUnidadGravable><dte:MontoGravable>' . $value->producto->montoGrav . '</dte:MontoGravable><dte:MontoImpuesto>' . $value->producto->montoImpues . '</dte:MontoImpuesto></dte:Impuesto></dte:Impuestos><dte:Total>' . $value->producto->totalproducto . '</dte:Total></dte:Item>';
    //echo $arrayitem;
  }
  $arrayMain .= '</dte:Items><dte:Totales><dte:TotalImpuestos><dte:TotalImpuesto NombreCorto="IVA" TotalMontoImpuesto="' . $jo->totalIva . '"/></dte:TotalImpuestos><dte:GranTotal>' . $jo->total . '</dte:GranTotal></dte:Totales></dte:DatosEmision></dte:DTE><dte:Adenda><dtecomm:Informacion_COMERCIAL xsi:schemaLocation="https://www.digifact.com.gt/dtecomm" xmlns:dtecomm="https://www.digifact.com.gt/dtecomm"><dtecomm:InformacionAdicional Version="7.1234654163"><dtecomm:REFERENCIA_INTERNA>';
  $internalid = getInternalId($jo->IdCaja, $jo->IdSucur, $jo->NitEmisor);
  $arrayMain .= $internalid . '</dtecomm:REFERENCIA_INTERNA><dtecomm:FECHA_REFERENCIA>' . $jo->Fecha . 'T' . $jo->Hora . '</dtecomm:FECHA_REFERENCIA><dtecomm:VALIDAR_REFERENCIA_INTERNA>VALIDAR</dtecomm:VALIDAR_REFERENCIA_INTERNA>';
  $arrayMain .= '<dtecomm:INFORMACION_ADICIONAL><dtecomm:Detalle Data="PROPINA" Value="Q ' . $jo->Propina . '"/></dtecomm:INFORMACION_ADICIONAL></dtecomm:InformacionAdicional></dtecomm:Informacion_COMERCIAL></dte:Adenda></dte:SAT></dte:GTDocumento>';
  $json = array('xml' => $arrayMain, 'internalid' => $internalid);
  echo json_encode($json);
} else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  require_once("../db.php");
  $id = $_GET['id'];
  $query = "SELECT
    t.IdCajas AS IdCaja,
    t.IdSucursales AS IdSucur,
    t.Fecha AS Fecha,
    TIME(t.Inicio) AS Hora,
    e.NIT AS NitEmisor,
    e.Nombre AS NombreE,
    s.CodigoEstablecimiento AS CodEsta,
    e.NombreComun AS NombreComer,
    s.Direccion AS Direccion,
    s.CodigoPostal AS CodPost,
    s.Municipio AS Municipio,
    s.Departamento AS Departamento,
    REPLACE(t.Nombre,'&','&amp;') AS NombreClien,
    t.NIT AS Nit,
    t.Direccion AS DirecClie,
    e.TipoFrase AS TipFrase,
    e.CodigoEscenario AS CodEscen,
    t.NoDocumento AS NumAcces,
    FORMAT(t.Total,2) AS total,
    FORMAT((t.Total/1.12)*0.12,4) AS totalIva,
    t.Propina AS Propina,
    t.InternalId AS internalId
    FROM transacciones t
    LEFT JOIN empresas e
    ON e.Id=t.IdEmpresas
    LEFT JOIN sucursales s
    ON s.Id=t.IdSucursales
    WHERE t.Id=$id";
  $result = $con->query($query);
  if ($result->num_rows > 0) {
    // VER CON QUE RECIBIR DATA
    while ($row = $result->fetch_assoc()) {
      $array = $row;
    }
    $query = "SELECT
       d.Cantidad AS cantidad,
       d.DescCorta AS desccorta,
       d.PrecioUnitario AS preciouni,
       FORMAT(d.PrecioUnitario*d.Cantidad,4) AS preciototal,
       d.Total AS totalproducto,
       d.TotalDescuento AS descuento,
       FORMAT(d.Total/1.12,4) AS montoGrav,
       FORMAT((d.Total/1.12)*0.12,4) AS montoImpues
       FROM detalletransacciones d
       WHERE d.IdTransacciones=$id";
    $result = $con->query($query);

    if ($result->num_rows > 0) {
      // VER CON QUE RECIBIR DATA
      $rows = array();
      while ($r = $result->fetch_assoc()) {
        $rows[] = $r;
      }
      $jodt = json_encode($rows, JSON_UNESCAPED_UNICODE);
      $arrayMain = '<?xml version="1.0"?><dte:GTDocumento xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:dte="http://www.sat.gob.gt/dte/fel/0.2.0" Version="0.1"><dte:SAT ClaseDocumento="dte"><dte:DTE ID="DatosCertificados"><dte:DatosEmision ID="DatosEmision">';
      $arrayMain .= '<dte:DatosGenerales Tipo="FACT" FechaHoraEmision="' . $array['Fecha'] . 'T' . $array['Hora'] . '" CodigoMoneda="GTQ" NumeroAcceso="' . $array['NumAcces'] . '"/>';
      //echo $arraycontin;
      $arrayMain .= '<dte:Emisor NITEmisor="' . $array['NitEmisor'] . '" NombreEmisor="' . $array['NombreE'] . '" CodigoEstablecimiento="' . $array['CodEsta'] . '" NombreComercial="' . $array['NombreComer'] . '" AfiliacionIVA="GEN"><dte:DireccionEmisor><dte:Direccion>' . $array['Direccion'] . '</dte:Direccion><dte:CodigoPostal>' . $array['CodPost'] . '</dte:CodigoPostal><dte:Municipio>' . $array['Municipio'] . '</dte:Municipio><dte:Departamento>' . $array['Departamento'] . '</dte:Departamento><dte:Pais>GT</dte:Pais></dte:DireccionEmisor></dte:Emisor><dte:Receptor NombreReceptor="' . $array['NombreClien'] . '" IDReceptor="' . $array['Nit'] . '"><dte:DireccionReceptor><dte:Direccion>' . $array['DirecClie'] . '.</dte:Direccion><dte:CodigoPostal>01010</dte:CodigoPostal><dte:Municipio>GUATEMALA</dte:Municipio><dte:Departamento>GUATEMALA</dte:Departamento><dte:Pais>GT</dte:Pais></dte:DireccionReceptor></dte:Receptor><dte:Frases><dte:Frase TipoFrase="' . $array['TipFrase'] . '" CodigoEscenario="' . $array['CodEscen'] . '"/></dte:Frases><dte:Items>';
      //echo $arrayheader2;
      $i = 0;
      foreach ($rows as $value) {
        $i++;
        $arrayMain .= '<dte:Item NumeroLinea="' . $i . '" BienOServicio="B"><dte:Cantidad>' . $value['cantidad'] . '</dte:Cantidad><dte:UnidadMedida>U</dte:UnidadMedida><dte:Descripcion>' . $value['desccorta'] . '</dte:Descripcion><dte:PrecioUnitario>' . $value['preciouni'] . '</dte:PrecioUnitario><dte:Precio>' . $value['preciototal'] . '</dte:Precio><dte:Descuento>' . $value['descuento'] . '</dte:Descuento><dte:Impuestos><dte:Impuesto><dte:NombreCorto>IVA</dte:NombreCorto><dte:CodigoUnidadGravable>1</dte:CodigoUnidadGravable><dte:MontoGravable>' . $value['montoGrav'] . '</dte:MontoGravable><dte:MontoImpuesto>' . $value['montoImpues'] . '</dte:MontoImpuesto></dte:Impuesto></dte:Impuestos><dte:Total>' . $value['totalproducto'] . '</dte:Total></dte:Item>';
        //echo $arrayitem;
      }
      $arrayMain .= '</dte:Items><dte:Totales><dte:TotalImpuestos><dte:TotalImpuesto NombreCorto="IVA" TotalMontoImpuesto="' . $array['totalIva'] . '"/></dte:TotalImpuestos><dte:GranTotal>' . $array['total'] . '</dte:GranTotal></dte:Totales></dte:DatosEmision></dte:DTE><dte:Adenda><dtecomm:Informacion_COMERCIAL xsi:schemaLocation="https://www.digifact.com.gt/dtecomm" xmlns:dtecomm="https://www.digifact.com.gt/dtecomm"><dtecomm:InformacionAdicional Version="7.1234654163"><dtecomm:REFERENCIA_INTERNA>';
      $arrayMain .= $array['internalId'] . '</dtecomm:REFERENCIA_INTERNA><dtecomm:FECHA_REFERENCIA>' . $array['Fecha'] . 'T' . $array['Hora'] . '</dtecomm:FECHA_REFERENCIA><dtecomm:VALIDAR_REFERENCIA_INTERNA>VALIDAR</dtecomm:VALIDAR_REFERENCIA_INTERNA>';
      $arrayMain .= '<dtecomm:INFORMACION_ADICIONAL><dtecomm:Detalle Data="PROPINA" Value="Q ' . $array['Propina'] . '"/></dtecomm:INFORMACION_ADICIONAL></dtecomm:InformacionAdicional></dtecomm:Informacion_COMERCIAL></dte:Adenda></dte:SAT></dte:GTDocumento>';
      echo $arrayMain;
    }
  }
}
