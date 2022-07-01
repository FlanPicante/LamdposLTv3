<?php

	if($_SERVER['REQUEST_METHOD']== 'GET'){
		require_once("../db.php");
		$sucur= $_GET['sucur'];
		$query = "SELECT 
 					 e.Nombre AS NombreEmpresa,
 					 e.NombreComun AS NombreC,
				 	 e.NIT AS Nit,
 					 e.TipoFrase AS Tpfras,
 					 e.CodigoEscenario AS CodEsc,
 					 s.Direccion AS Direccion,
 					 s.CodigoEstablecimiento AS CodEst,
					 s.Nombre AS Sucursal,
					 s.CodigoPostal AS CodPost,
					 s.Municipio AS Municipio,
					 s.Departamento AS Departamento
				FROM sucursales s 
  				LEFT JOIN empresas e 
    				ON s.IdEmpresas = e.Id
				WHERE s.Id = $sucur ";
		$result = $con->query($query);
		
		if ($result->num_rows > 0) {
  			// VER CON QUE RECIBIR DATA
 			while($row = $result->fetch_assoc()) {
   				$array = $row;
  			}
			
   				echo json_encode($array, JSON_UNESCAPED_UNICODE);
		} else {
			$msg = array('msg'=>"NO DATA");
			echo json_encode($msg, JSON_UNESCAPED_UNICODE);
			}

		$result->close();
		$con->close();
	}


