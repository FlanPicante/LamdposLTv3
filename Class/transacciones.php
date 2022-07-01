<?php

	if($_SERVER['REQUEST_METHOD']== 'GET'){
		require_once("../db.php");
		$idSucur= $_GET['idSucur'];
		$idUsuario= $_GET['idUsuario'];
		$idCajas= $_GET['idCajas'];
		$inicio= $_GET['inicio'];
		$fin= $_GET['fin'];
		
		$query = "SELECT 
					1 AS Codigo,
  					t.Id AS Id,
  					t.Serie AS Serie,
  					t.NoDocumento AS NoDocumento,
  					t.Fecha AS Fecha,
  					t.Inicio AS Inicio,
  					t.Total+t.Propina AS Total,
					t.SubTotal AS SubTotal,
					t.TotalDescuento AS TotalDescuento,
					t.Propina AS Propina,
  					t.Cambio AS Cambio,
  					t.Nombre AS Nombre,
  					t.NIT AS NIT,
  					t.Direccion AS Direccion,
  					t.Estado AS Estado,
  					t.TiposDocumentos AS TiposDocumentos,
  					t.AutorizacionFEL AS AutorizacionFEL,
  					t.FelFechaCertificacion AS FelFechaCertificacion,
  					t.InternalId AS InternalId,
					t.IdClientes AS IdClientes,
					c.Telefono AS Celular,
					t.Version AS Version
				FROM
  					transacciones t
				LEFT JOIN clientes c
				ON c.Id=t.IdClientes
				WHERE t.IdSucursales= $idSucur
				AND t.IdUsuarios=$idUsuario
				AND t.IdCajas=$idCajas
				AND t.Inicio BETWEEN '$inicio' AND '$fin'
				ORDER BY Inicio DESC";
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
	}else if($_SERVER['REQUEST_METHOD']== 'POST'){
		require_once("../db.php");
		$json = json_decode(file_get_contents('php://input'));
		$query = "SELECT 
					1 AS Codigo,
  					t.Id AS Id,
  					t.Serie AS Serie,
  					t.NoDocumento AS NoDocumento,
  					t.Fecha AS Fecha,
  					t.Inicio AS Inicio,
  					t.Total+t.Propina AS Total,
					t.SubTotal AS SubTotal,
					t.TotalDescuento AS TotalDescuento,
					t.Propina AS Propina,
  					t.Cambio AS Cambio,
  					t.Nombre AS Nombre,
  					t.NIT AS NIT,
  					t.Direccion AS Direccion,
  					t.Estado AS Estado,
  					t.TiposDocumentos AS TiposDocumentos,
  					t.AutorizacionFEL AS AutorizacionFEL,
  					t.FelFechaCertificacion AS FelFechaCertificacion,
  					t.InternalId AS InternalId,
					t.IdClientes AS IdClientes,
					c.Telefono AS Celular,
					t.Version AS Version
				FROM
  					transacciones t
				LEFT JOIN clientes c
				ON c.Id=t.IdClientes
				WHERE t.IdSucursales= $json->IdSucursales
				AND t.IdUsuarios=$json->IdUser
				AND t.IdCajas=$json->IdCajas
				AND t.Fecha ='$json->Fecha'
				AND t.Serie='$json->Serie'
				AND t.NoDocumento=$json->NoDocumento";
				$result = $con->query($query);
				if ($result->num_rows > 0) {
					// VER CON QUE RECIBIR DATA
					while($row = $result->fetch_assoc()) {
						$array = $row;
				   }
					echo json_encode($array, JSON_UNESCAPED_UNICODE);
			  }
	}
