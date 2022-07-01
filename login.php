<?php

	if($_SERVER['REQUEST_METHOD']== 'GET'){
		require_once("db.php");
		$user= $_GET['user'];
		$query = "SELECT
					IdNivel,
					Id AS IdUsuario,
					Password,
					Activo,
					Nombres
					FROM usuarios
					WHERE Usuario='$user'";
		$result = $con->query($query);
		
		if ($result->num_rows > 0) {
  			// VER CON QUE RECIBIR DATA
 			while($row = $result->fetch_assoc()) {
   				$array = $row;
  			}
			
   				echo json_encode($array, JSON_UNESCAPED_UNICODE);
		} else {
			$msg = array('msg'=>"Usuario incorrecto");
			echo json_encode($msg, JSON_UNESCAPED_UNICODE);
			}

		$result->close();
		$con->close();
	}


