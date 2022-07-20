<?php

	if($_SERVER['REQUEST_METHOD']== 'GET'){
		require_once("../db.php");
		$idcaja= $_GET['caja'];
		$idsucur= $_GET['sucur'];
		if(obParamTmp($con, $idcaja)==1){
			$query = "SELECT 
  					Id,
  					Value,
  					StrValue
				FROM
  					parametros 
				WHERE  Id ='LAMDPOSLT_FEL_TOKEN'
				OR Id LIKE 'LAMDPOSLT_".$idcaja."_%' 
				OR Id= 'LAMDPOSLT_FORMADEPAGO' OR Id='LAMDPOSLT_PROPINA_SUGERIDA'
					OR Id='LAMDPOSLT_COMANDA_NOMBRE'";
		}else{
			$query = "SELECT 
  					Id,
  					Value,
  					StrValue
				FROM
  					parametros 
				WHERE  Id ='LAMDPOSLT_FEL_TOKEN'
				OR Id LIKE 'LAMDPOSLT_".$idsucur."_%' OR Id LIKE 'LAMDPOSLT_".$idsucur."_".$idcaja."_%' 
				OR Id= 'LAMDPOSLT_FORMADEPAGO' OR Id='LAMDPOSLT_PROPINA_SUGERIDA'
					OR Id='LAMDPOSLT_COMANDA_NOMBRE'";
		}
		$result = $con->query($query);
		
		if ($result->num_rows > 0) {
  			// VER CON QUE RECIBIR DATA
  			$rows= array();
 			while($r = $result->fetch_assoc()) {
   				$rows[]=$r;
  			}
			echo json_encode($rows, JSON_UNESCAPED_UNICODE);
		} else {
			$msg = array('msg'=>"NO DATA");
			echo json_encode($msg, JSON_UNESCAPED_UNICODE);
			}

		$result->close();
		$con->close();
	}
