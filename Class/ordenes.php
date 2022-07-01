<?php

	if($_SERVER['REQUEST_METHOD']== 'GET'){
		require_once("../db.php");
		$idMesa= $_GET['idMesa'];
		$query = "SELECT 
					1 AS Codigo,
  					Id,
  					Serie,
  					NoDocumento,
  					TIME(Inicio) AS Inicio,
  					Total,
  					Estado,
  					IdVendedores,
					Cliente,
					Fecha
				FROM
  					pedidostemporal
				WHERE IdMesa= $idMesa
				AND Estado=6";
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
