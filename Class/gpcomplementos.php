<?php

	if($_SERVER['REQUEST_METHOD']== 'GET'){
		require_once("../db.php");
		$idsucur= $_GET['sucur'];
		$upc= $_GET['upc'];
		$query = "SELECT
					Id,Nombre
				FROM gruposcomplementos
				WHERE Id IN(SELECT 
								IdGruposcomplementos 
							FROM productoscomplementos 
							WHERE Upc=$upc 
							GROUP BY IdGruposcomplementos)
				ORDER BY Orden ASC";
		$result = $con->query($query);
		
		if ($result->num_rows > 0) {
  			// VER CON QUE RECIBIR DATA
  			$rows= array();
 			while($r = $result->fetch_assoc()) {
   				$rows[]=$r;
  			}
			echo json_encode($rows, JSON_UNESCAPED_UNICODE);
		} else {
			$msg = array('msg'=>"NO HAY GRUPOS COMPLEMENTOS");
			echo json_encode($msg, JSON_UNESCAPED_UNICODE);
			}

		$result->close();
		$con->close();
	}
