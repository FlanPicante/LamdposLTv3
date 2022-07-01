<?php

	if($_SERVER['REQUEST_METHOD']== 'GET'){
		require_once("../db.php");
		$numero= $_GET['numero'];
		$query = "SELECT 
  					Id,
  					Telefono AS Celular,
  					Nombre,Nit
				FROM
  					clientes 
				WHERE  Telefono = '$numero'";
		$result = $con->query($query);
		if ($result->num_rows > 0) {
  			// VER CON QUE RECIBIR DATA
  			$rows= array();
			while($row = $result->fetch_assoc()) {
				$array = $row;
		   }
		   $rows[]=$array;
		   echo json_encode($rows, JSON_UNESCAPED_UNICODE);


		}

		$result->close();
		$con->close();
	}else if($_SERVER['REQUEST_METHOD']== 'POST'){
		require_once("../db.php");
		require_once("../Functions/errores.php");
		require_once("../Functions/functionsDb.php");
		$json = json_decode(file_get_contents('php://input'));
		$query="INSERT INTO clientes(IdSucursales,Nit,Telefono,Nombre)
					VALUES($json->IdSucursales,'$json->Nit',$json->Celular,'$json->Nombre')";
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
		$condicion="IdSucursales= $json->IdSucursales AND Nit = '$json->Nit' AND Telefono='$json->Celular' AND Nombre ='$json->Nombre'";
		$idCliente=getid('clientes',$condicion,$con);
		$query="INSERT INTO direccionesdomicilio(IdClientes,Direccion,Indicaciones)
				VALUES($idCliente,'$json->Direccion','$json->Indicaciones')";
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
