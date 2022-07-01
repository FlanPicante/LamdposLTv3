<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  require_once("../db.php");
  $json = json_decode(file_get_contents('php://input'));
  $rows = array();
  //DATOS DE DEPARTAMENTOS
  $query = "SELECT
              1 AS Codigo,
              dt.IdDepartamentos AS IdDep,d.Nombre AS Nombre
            FROM detalletransacciones dt
            LEFT JOIN departamentos d
              ON d.Id=dt.IdDepartamentos
            RIGHT JOIN transacciones t
              ON t.Id=dt.IdTransacciones
            WHERE t.Inicio BETWEEN '$json->Inicio' AND '$json->Fin'
            AND t.IdCajas=$json->IdCajas AND t.IdUsuarios=$json->IdUser
            AND t.Estado !=2 AND dt.IdCajas=$json->IdCajas 
            GROUP BY dt.IdDepartamentos
            ORDER BY dt.IdDepartamentos ASC";
  $result = $con->query($query);
  if ($result->num_rows > 0) {
    $rowsD=array();
    // VER CON QUE RECIBIR DATA
    while ($r = $result->fetch_assoc()) {
      $rowsD[] = $r;
    }
    $rows[]=array('Departamentos'=>$rowsD);
    //DETELLE DE PRODUCTOS
    $query = "SELECT
                  1 AS Codigo, dt.Upc AS Upc,dt.DescCorta AS DescCorta,SUM(dt.Cantidad) AS Cant,SUM(dt.Total) AS Total,
                  dt.IdDepartamentos AS IdDep,dt.TipoProducto AS TipoProducto, d.Nombre AS Departamento
                  FROM detalletransacciones dt
                LEFT JOIN departamentos d
                    ON d.Id=dt.IdDepartamentos
                RIGHT JOIN transacciones t
                    ON t.Id=dt.IdTransacciones
                WHERE t.Inicio BETWEEN '$json->Inicio' AND '$json->Fin'
                  AND t.IdCajas=$json->IdCajas AND t.IdUsuarios=$json->IdUser
                  AND t.Estado !=2 AND dt.IdCajas=$json->IdCajas 
                GROUP BY Upc, TipoProducto
                ORDER BY dt.IdDepartamentos, dt.TipoProducto ASC";
    $result = $con->query($query);
    if ($result->num_rows > 0) {
      // VER CON QUE RECIBIR DATA
      $rowDt=array();
      while ($r = $result->fetch_assoc()) {
        $rowDt[] = $r;
      }
      $rows[]=array('Detalle'=>$rowDt); 
      echo json_encode($rows, JSON_UNESCAPED_UNICODE);
    } else {
      $msg = array('Codigo' => "0");
      echo json_encode($msg);
    }
  } else {
    $msg = array('Codigo' => "0");
    echo json_encode($msg);
  }
}
