<?php

	if($_SERVER['REQUEST_METHOD']== 'GET'){
		require_once("../db.php");
		$idSucur= $_GET['idSucur'];

		$query = "SELECT
					1 AS Codigo, 
  					Id,
  					Nombre,
  					IdSucursales
				FROM
  					vendedores
				WHERE IdSucursales= $idSucur";
		$result = $con->query($query);
		
		if ($result->num_rows > 0) {
  			// VER CON QUE RECIBIR DATA
  			$rows= array();
 			while($r = $result->fetch_assoc()) {
   				$rows[]=$r;
  			}
			echo json_encode($rows, JSON_UNESCAPED_UNICODE);
		} else {
			$msg = array();
			$msg[]=array('Codigo'=>"0");
			echo json_encode($msg, JSON_UNESCAPED_UNICODE);
			}

		$result->close();
		$con->close();
	}
