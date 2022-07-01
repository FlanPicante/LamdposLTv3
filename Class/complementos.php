<?php

	if($_SERVER['REQUEST_METHOD']== 'GET'){
		require_once("../db.php");
		$upc= $_GET['upc'];
		$gpCom= $_GET['gpCom'];
		$query = "SELECT
					UpcProductos,DescCorta,Costo,Precio,Unidades,IdGruposComplementos
				FROM productoscomplementos
				WHERE Upc= $upc AND IdGruposcomplementos= $gpCom";
		$result = $con->query($query);
		
		if ($result->num_rows > 0) {
  			// VER CON QUE RECIBIR DATA
  			$rows= array();
 			while($r = $result->fetch_assoc()) {
   				$rows[]=$r;
  			}
			echo json_encode($rows, JSON_UNESCAPED_UNICODE);
		} else {
			$msg = array('msg'=>"NO HAY COMPLEMENTOS");
			echo json_encode($msg, JSON_UNESCAPED_UNICODE);
			}

		$result->close();
		$con->close();
	}
