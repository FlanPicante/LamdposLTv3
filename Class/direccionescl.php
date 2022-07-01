<?php

	if($_SERVER['REQUEST_METHOD']== 'GET'){
		require_once("../db.php");
		$idcliente= $_GET['idcliente'];
		$query = "SELECT 
  					Id,
  					Direccion,
  					Indicaciones
				FROM
					direccionesdomicilio 
				WHERE  IdClientes =$idcliente";
		$result = $con->query($query);
		
		if ($result->num_rows > 0) {
  			// VER CON QUE RECIBIR DATA
  			$rows= array();
 			while($r = $result->fetch_assoc()) {
   				$rows[]=$r;
  			}
			echo json_encode($rows, JSON_UNESCAPED_UNICODE);
		}

		$result->close();
		$con->close();
	}else if($_SERVER['REQUEST_METHOD'] == 'POST'){
		require_once("../db.php");
		require_once("../Functions/errores.php");
		$json = json_decode(file_get_contents('php://input'));
		$query="INSERT INTO direccionesdomicilio(IdClientes,Direccion,Indicaciones)
				VALUES($json->IdCliente,'$json->Direccion','$json->Indicaciones')";
		if(!$con->query($query)){
			$msgError = str_replace("'", '"', $con->error);
			$sql = str_replace("'", "''", $query);
			$dw = array('modulo' => $_SERVER['SCRIPT_NAME'], 'proceso' => $_SERVER['REQUEST_URI']);
  
			$tmp = [];
			$tmp['IdSucursales'] = $json->IdSucursales;
			$tmp['IdCajas'] = $json->IdCajas;
			$tmp['IdUsuarios'] = 0;
			$tmp['IdTransacciones'] = 0;
			$tmp['Serie'] = 0;
			$tmp['NoDocumento'] = 0;
			generarLogDB($msgError, $sql, $con, $tmp, $dw);
		}
	}
