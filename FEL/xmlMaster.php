<?php

	if($_SERVER['REQUEST_METHOD']== 'POST'){
    $contingencia= $_GET['contingencia'];
		$json = file_get_contents('php://input');
		$jo = json_decode($json);
		$arrayheader1='<?xml version="1.0"?>
<dte:GTDocumento xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:dte="http://www.sat.gob.gt/dte/fel/0.2.0" Version="0.1">
  <dte:SAT ClaseDocumento="dte">
    <dte:DTE ID="DatosCertificados">
      <dte:DatosEmision ID="DatosEmision">';
      echo $arrayheader1;
      if($contingencia==1){
        $arraycontin='<dte:DatosGenerales Tipo="FACT" FechaHoraEmision="'.$jo->Fecha.'T'.$jo->Hora.'" CodigoMoneda="GTQ" NumeroAcceso="'.$jo->NumAcces.'"/>';
      }else{
        $arraycontin='<dte:DatosGenerales Tipo="FACT" FechaHoraEmision="'.$jo->Fecha.'T'.$jo->Hora.'" CodigoMoneda="GTQ"/>';
      }
      echo $arraycontin;
        $arrayheader2='<dte:Emisor NITEmisor="'.$jo->NitEmisor.'" NombreEmisor="'.$jo->NombreE.'" CodigoEstablecimiento="'.$jo->CodEsta.'" NombreComercial="'.$jo->NombreComer.'" AfiliacionIVA="GEN">
          <dte:DireccionEmisor>
            <dte:Direccion>'.$jo->Direccion.'</dte:Direccion>
            <dte:CodigoPostal>'.$jo->CodPost.'</dte:CodigoPostal>
            <dte:Municipio>'.$jo->Municipio.'</dte:Municipio>
            <dte:Departamento>'.$jo->Departamento.'</dte:Departamento>
            <dte:Pais>GT</dte:Pais>
          </dte:DireccionEmisor>
        </dte:Emisor>
        <dte:Receptor NombreReceptor="'.$jo->NombreClien.'" IDReceptor="'.$jo->Nit.'">
          <dte:DireccionReceptor>
            <dte:Direccion>Ciudad.</dte:Direccion>
            <dte:CodigoPostal>01010</dte:CodigoPostal>
            <dte:Municipio>GUATEMALA</dte:Municipio>
            <dte:Departamento>GUATEMALA</dte:Departamento>
            <dte:Pais>GT</dte:Pais>
          </dte:DireccionReceptor>
        </dte:Receptor>
        <dte:Frases>
          <dte:Frase TipoFrase="'.$jo->TipFras.'" CodigoEscenario="'.$jo->CodEscen.'"/>
        </dte:Frases>
        <dte:Items>';
    echo $arrayheader2;
    $items = $jo->carrito;
    $i=0; 
    foreach ($items as $value) {
      $i++;
      $arrayitem='<dte:Item NumeroLinea="'.$i.'" BienOServicio="B">
                   <dte:Cantidad>'.$value->producto->cantidad.'</dte:Cantidad>
                    <dte:UnidadMedida>U</dte:UnidadMedida>
                    <dte:Descripcion>'.$value->producto->desc.'</dte:Descripcion>
                    <dte:PrecioUnitario>'.$value->producto->preciouni.'</dte:PrecioUnitario>
                    <dte:Precio>'.$value->producto->preciot.'</dte:Precio>
                    <dte:Descuento>0.00</dte:Descuento>
                    <dte:Impuestos>
                      <dte:Impuesto>
                        <dte:NombreCorto>IVA</dte:NombreCorto>
                        <dte:CodigoUnidadGravable>1</dte:CodigoUnidadGravable>
                        <dte:MontoGravable>'.$value->producto->montoGrav.'</dte:MontoGravable>
                        <dte:MontoImpuesto>'.$value->producto->montoImpues.'</dte:MontoImpuesto>
                      </dte:Impuesto>
                    </dte:Impuestos>
                    <dte:Total>'.$value->producto->preciot.'</dte:Total>
                  </dte:Item>';
          echo $arrayitem;
         }
    $arrayfin='</dte:Items>
        <dte:Totales>
          <dte:TotalImpuestos>
            <dte:TotalImpuesto NombreCorto="IVA" TotalMontoImpuesto="'.$jo->totalIva.'"/>
          </dte:TotalImpuestos>
          <dte:GranTotal>'.$jo->total.'</dte:GranTotal>
        </dte:Totales>
      </dte:DatosEmision>
    </dte:DTE>
  </dte:SAT>
</dte:GTDocumento>';
echo $arrayfin;


	}
