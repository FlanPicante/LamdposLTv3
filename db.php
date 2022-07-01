<?php
	
		$db= $_GET['db'];
		function connect(){
			$db= $_GET['db'];
			$ip="127.0.0.1";
			$user="superpos";
			$pass="97crela2t3";
			if (!($conn=mysqli_connect($ip,$user,$pass))) {
				echo "Error al conectar con el servidor de base de datos.";
    			exit();
			}
			if (!mysqli_select_db($conn,$db)) {
				echo "Error seleccion";
				exit();
			}else{

				if (!$conn->set_charset("utf8")) {
    				printf("Error cargando el conjunto de caracteres utf8: %s", $conn->error);
    			exit();
				} 
			}
			return $conn;
		}
		$con=connect();
		
	
	