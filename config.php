<?php

	if($_SERVER['REQUEST_METHOD']== 'GET'){
		require_once("db.php");
		$serdip= $_GET['serial'];
		$query = "SELECT
					c.Id AS IdCajas,
					c.IdEmpresas,
					c.IdSucursales,
					c.Nombre,
					c.Comanda,
					c.noComandas,
					c.ComandaFactura,
					c.TipoPago
					FROM cajas c
					WHERE c.SerieEquipos='$serdip'";
		$result = $con->query($query);
		
		if ($result->num_rows > 0) {
  			// VER CON QUE RECIBIR DATA
 			while($row = $result->fetch_assoc()) {
   				$array = $row;
  			}
			
   				echo json_encode($array);
		} else {
			$msg = array('msg'=>"Equipo no registrado");
			echo json_encode($msg);
			}

		$result->close();
		$con->close();
	}


