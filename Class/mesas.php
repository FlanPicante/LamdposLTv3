<?php

	if($_SERVER['REQUEST_METHOD']== 'GET'){
		require_once("../db.php");
		require_once("../Functions/functionsDb.php");
		$idSucur= $_GET['idSucur'];

		verificarMesa($idSucur,$con);

		$query = "SELECT
					1 AS Codigo, 
  					m.Id AS Id,
  					m.Nombre AS Nombre,
  					m.IdSucursales AS IdSucursales,
  					m.Numero AS Numero,
  					m.Disponible AS Disponible,
  					m.IdMesero AS IdMesero,
					v.Nombre AS NombreMesero
				FROM
  					mesas m
				LEFT JOIN vendedores v
					ON v.Id=m.IdMesero
				WHERE m.IdSucursales= $idSucur
				ORDER BY Id ASC";
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
