<?php

	if($_SERVER['REQUEST_METHOD']== 'POST'){
		$json = file_get_contents('php://input');
		$decodedData = json_decode($json);
		
		$items = $decodedData->carrito;
		$i=0;	
		foreach ($items as $value) {
			$i++;
			$array='<dte:Item NumeroLinea="'.$i.'" BienOServicio="B">
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
        echo $array;
		}
		//$producto = $decodedData['carrito'][0]['producto'];

		//$desc = $producto['desc'];
	
		//echo $desc;
		
	}
