<?php

	if($_SERVER['REQUEST_METHOD']== 'GET'){
		require_once("../db.php");
		$idcaja= $_GET['idcaja'];
		$query = "SELECT 
  					r.Id AS IdRes,
  					r.SiguienteNumero AS SiguienteNumero,
					n.Serie AS SerieT,
  					n.Numero AS SiguienteNumT,
  					n.Id AS IdNum,
  					o.Id AS IdOrn,
  					o.Serie AS SerieOrn,
  					o.Numero AS SiguienteNumOrn
				FROM
  					resoluciones r
  				LEFT JOIN cajas c
  				ON c.IdResoluciones=r.Id
  				LEFT JOIN numeracion n
  				ON n.IdCajas=c.Id AND n.IdTiposDocumentos=1
  				LEFT JOIN numeracion o
  				ON o.IdCajas=c.Id AND o.IdTiposDocumentos=2
				WHERE c.Id= $idcaja";
		$result = $con->query($query);
		
		if ($result->num_rows > 0) {
  			// VER CON QUE RECIBIR DATA
  			while($row = $result->fetch_assoc()) {
   				$array = $row;
  			}
   			echo json_encode($array, JSON_UNESCAPED_UNICODE);
		} else {
			$msg = array('msg'=>"NO HAY DEPARTAMENTOS ASIGANOS A LA SUCURSAL");
			echo json_encode($msg, JSON_UNESCAPED_UNICODE);
			}

		$result->close();
		$con->close();
	}
