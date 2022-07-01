<?php

	if($_SERVER['REQUEST_METHOD']== 'GET'){
		require_once("../db.php");
		$idsucur= $_GET['sucur'];
		$iddep=$_GET['dep'];
		$query = "SELECT 
  					Upc,DescCorta,DescLarga,Precio,
  					DirectoCarrito AS DireC,
  					Costo,
					IdTipoImpresion
				FROM
  					productos 
				WHERE IdSucursales= $idsucur
				AND IdDepartamentos=$iddep";
		$result = $con->query($query);
		
		if ($result->num_rows > 0) {
  			// VER CON QUE RECIBIR DATA
  			$rows= array();
 			while($r = $result->fetch_assoc()) {
   				$rows[]=$r;
  			}
			echo json_encode($rows, JSON_UNESCAPED_UNICODE);
		} else {
			$msg = array('msg'=>"NO HAY DEPARTAMENTOS ASIGANOS A LA SUCURSAL");
			echo json_encode($msg, JSON_UNESCAPED_UNICODE);
			}

		$result->close();
		$con->close();
	}
