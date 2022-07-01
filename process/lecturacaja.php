<?php

	if($_SERVER['REQUEST_METHOD']== 'POST'){
    require_once("../db.php");
		$json = json_decode(file_get_contents('php://input'));
    $rows= array();
      //DATOS DE VENTAS
      $query = "SELECT
                1 AS Codigo, 
                  COUNT(t.Id) AS TotalTransacciones, 
                  SUM(t.Total)+SUM(t.Propina) AS Total,
                  SUM(t.SubTotal)  AS Ventas, 
                  SUM(t.TotalAnulaciones) AS TotalAnulaciones,
                  SUM(t.Propina) AS TotalPropinas, 
                  SUM(t.TotalDescuento) AS TotalDescuento,
                  u.Nombre AS NombreCaja, 
                  SUM(t.SubTotal)-SUM(t.TotalDescuento) AS TotalVentas
                FROM transacciones t
                LEFT JOIN cajas u
                  ON u.Id=t.IdCajas
                WHERE t.IdCajas=$json->IdCajas AND t.IdSucursales=$json->IdSucursales
                 AND t.Inicio BETWEEN '$json->Inicio' AND '$json->Fin'";
      $result=$con->query($query);
      if ($result->num_rows > 0) {
        // VER CON QUE RECIBIR DATA
      while($r = $result->fetch_assoc()) {
          $rows[] = $r;
        }

      //DETALLE VENTAS
        $queryV="SELECT
                  1 AS Codigo, 
                  p.CodigoPago AS CodigoPago,
                  f.Nombre AS Nombre,
                  SUM(p.MontoRealPago) AS Monto,
                  COUNT(p.CodigoPago) AS Cantidad
                FROM pagosdetalles p
                LEFT JOIN transacciones t
                  ON t.Id=p.Idtransacciones
                  LEFT JOIN formasdepago f
                  ON f.CodigoPago=p.CodigoPago
                WHERE t.IdCajas=$json->IdCajas AND t.IdSucursales=$json->IdSucursales
                AND t.Inicio BETWEEN '$json->Inicio' AND '$json->Fin'
                AND t.Estado !=2
                GROUP BY p.CodigoPago";
        $resultV=$con->query($queryV);

        if ($resultV->num_rows > 0) {
          // VER CON QUE RECIBIR DATA
          $rowsV=array();
          while($r = $resultV->fetch_assoc()) {
            $rowsV[] = $r;
          }
          $rows[]=array('FormasPago'=>$rowsV); 
          echo json_encode($rows, JSON_UNESCAPED_UNICODE);
          
        } else {
          $rowsV=array();
          $rowsV[] = array('Codigo'=>'0');;
          $rows[]=array('FormasPago'=>$rowsV); 
          echo json_encode($rows, JSON_UNESCAPED_UNICODE);
        }

      } else {
        $msg=array();
        $msg[] = array('Codigo'=>"0");
        echo json_encode($msg);
      }

	}
