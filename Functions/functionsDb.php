<?php
function getVersion()
{
  return 4;
}

function getid($table, $condicion, $conlocal)
{
  $queryId = "SELECT 
  				      Id 
				      FROM $table 
				      WHERE $condicion ";
  $result = $conlocal->query($queryId);
  if ($result->num_rows > 0) {
    // VER CON QUE RECIBIR DATA
    while ($row = $result->fetch_assoc()) {
      $array = $row;
    }
    return $array["Id"];
  }
}

function getreceta($upc, $sucursal, $conlocal)
{

  $queryr = "SELECT p.Existencia AS Existencia,IF(pc.Upc IS NULL,'NO HAY RECETA',pc.Upc)AS UpcR, pc.Unidades AS Unidades,p.IdtiposProductos AS IdTiProd,
				trec.DescCorta AS DescCorta,trec.IdDepartamentos AS IdDep,pc.Costo AS Costo,pc.Total AS Total,
				trec.Existencia AS ExisP
                FROM productos p
                LEFT JOIN productoscomponentes pc
                ON pc.UpcPrincipal=p.Upc AND p.IdSucursales=pc.IdSucursales 
                LEFT JOIN (SELECT Upc AS UpcR,DescCorta,IdDepartamentos,Existencia FROM productos) trec
                ON trec.Upcr=pc.Upc
                WHERE p.Upc=" . $upc . " AND p.IdSucursales=$sucursal";
  $dtrec = $conlocal->query($queryr);
  if ($dtrec->num_rows > 0) {
    // VER CON QUE RECIBIR DATA
    $rows = array();
    while ($r = $dtrec->fetch_assoc()) {
      $rows[] = $r;
    }
    $dtrec = $rows;
  }
  return $dtrec;
}

function updateExistencia($upc, $sucursal, $conlocal, $cantidad, $operador)
{
  $queryup = "UPDATE productos SET Existencia=Existencia" . $operador . $cantidad . " WHERE Upc='" . $upc . "' AND IdSucursales=$sucursal";
  $conlocal->query($queryup);
}

function getordenNo($sucursal, $conlocal)
{
  $queryS = "SELECT Ordenes FROM sucursales WHERE Id=$sucursal";
  $result = $conlocal->query($queryS);
  if ($result->num_rows > 0) {
    // VER CON QUE RECIBIR DATA
    while ($row = $result->fetch_assoc()) {
      $array = $row;
    }
    $sum = $array["Ordenes"] + 1;
    $queryU = "UPDATE sucursales SET Ordenes=$sum WHERE Id=$sucursal";
    $conlocal->query($queryU);
    return $sum;
  }
}

function getOrdenTr($IdTr, $IdSucursal, $conlocal)
{
  $query = "SELECT NoOrden 
  FROM transacciones 
  WHERE Id=$IdTr AND IdSucursales=$IdSucursal";
  $result = $conlocal->query($query);
  if ($result->num_rows > 0) {
    // VER CON QUE RECIBIR DATA
    while ($row = $result->fetch_assoc()) {
      $array = $row;
    }
    return $array["NoOrden"];
  }
}

function getInternalId($idCaja, $idSucursal, $nit)
{
  date_default_timezone_set('America/Guatemala');
  $fecha = getdate();
  $idSucursal = str_pad($idSucursal, 3, "0", STR_PAD_LEFT);
  $idCaja = str_pad($idCaja, 3, "0", STR_PAD_LEFT);

  $internalid = $idSucursal . $idCaja . "-" . $nit . "-" . $fecha['year'] . $fecha['mon'] . $fecha['wday'] . "-" . $fecha['mday'] . $fecha['hours'] . $fecha['minutes'] . $fecha['seconds'];

  return $internalid;
}

function updateMesa($IdMesa, $Disponible, $conlocal)
{
  $queryup = "UPDATE mesas SET Disponible='" . $Disponible . "' WHERE Id=" . $IdMesa;
  $conlocal->query($queryup);
}

function updateOrden($IdOrden, $Estado, $conlocal)
{
  if ($Estado == 1) {
    if (verificarOrden($IdOrden, $conlocal) == 0) {
      $queryup = "UPDATE pedidostemporal SET Estado='" . $Estado . "' WHERE Id=" . $IdOrden;
      $conlocal->query($queryup);
    }
  } else if ($Estado == 3) {
    if (verificarOrden($IdOrden, $conlocal) == 0) {
      $queryup = "UPDATE pedidostemporal SET Estado='2' WHERE Id=" . $IdOrden;
      $conlocal->query($queryup);
    }
  } else {
    $queryup = "UPDATE pedidostemporal SET Estado='" . $Estado . "' WHERE Id=" . $IdOrden;
    $conlocal->query($queryup);
  }
}

function verificarOrden($IdOrden, $conlocal)
{
  $query = "SELECT COUNT(Id) AS Cantidad FROM pedidosdetalletmp
          WHERE IdTransacciones=$IdOrden AND Estatus=1";
  $result = $conlocal->query($query);
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $array = $row;
    }
    return $array['Cantidad'];
  }
}

function verificarMesa($IdSucursal, $conlocal)
{
  $subQuery = "SELECT
                  m.Id AS IdM,
                IF(COUNT(p.Id)>0,'0','1') AS Disponible,
                p.IdVendedores AS IdMesero
              FROM mesas m
              LEFT JOIN pedidostemporal p
                ON m.Id=p.IdMesa AND p.Estado=6
              WHERE m.IdSucursales=$IdSucursal
              GROUP BY m.Id";

  $queryup = "UPDATE mesas m, ($subQuery) v
                SET m.Disponible=v.Disponible,
                m.IdMesero=IF(v.Disponible=0,v.IdMesero,'0')
              WHERE m.Id=v.IdM";
  $conlocal->query($queryup);
}

function anularFactura($IdTr, $conlocal)
{
  $queryUp = "UPDATE transacciones
                SET Estado=2,
                TotalAnulaciones=Total,
                Propina=0,
                SubTotal=0,
                Total=0
                WHERE Id=$IdTr";
  $conlocal->query($queryUp);
}

function verificarEstadoTr($idTr, $conlocal)
{
  $query = "SELECT Estado FROM transacciones WHERE Id=$idTr";
  $result = $conlocal->query($query);
  if ($result->num_rows > 0) {
    // VER CON QUE RECIBIR DATA
    while ($row = $result->fetch_assoc()) {
      $array = $row;
    }
    echo $array["Estado"];
  }
}

function obFormaPTr($idTr, $conlocal)
{
  $queryP = "SELECT 1 AS Codigo,f.Id AS Id,p.CodigoPago AS CodigoPago ,p.Monto AS Monto,
              p.MontoRealPago AS MontoReal, f.Nombre AS Nombre, p.Referencia AS Datos
                FROM pagosdetalles p
                LEFT JOIN formasdepago f
              ON f.CodigoPago=p.CodigoPago
              WHERE p.IdTransacciones=$idTr
              AND Monto>0";
  $result = $conlocal->query($queryP);
  if ($result->num_rows > 0) {
    // VER CON QUE RECIBIR DATA
    $rows = array();
    while ($r = $result->fetch_assoc()) {
      $rows[] = $r;
    }
    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
  } else {
    $msg = array('Codigo' => "0");
    echo json_encode($msg, JSON_UNESCAPED_UNICODE);
  }
}

function obUsuario($usuario, $idsucur, $idnivel, $conlocal)
{
  $queryP = "SELECT
                1 AS Codigo, 
                Id,Password,Activo
              FROM usuarios
              WHERE Usuario='$usuario' AND IdSucursales=$idsucur
              AND IdNivel=$idnivel";
  $result = $conlocal->query($queryP);
  if ($result->num_rows > 0) {
    // VER CON QUE RECIBIR DATA
    while ($row = $result->fetch_assoc()) {
      $array = $row;
    }
    echo json_encode($array, JSON_UNESCAPED_UNICODE);
  } else {
    $msg = array('Codigo' => "0");
    echo json_encode($msg, JSON_UNESCAPED_UNICODE);
  }
}

function pagarProductoOrden($idReg, $conlocal, $estado)
{
  $queryP = "UPDATE pedidosdetalletmp SET Estatus = $estado
              WHERE Id=$idReg";
  if ($conlocal->query($queryP)) {
    $queryP = "UPDATE pedidosdetalletmp SET Estatus = $estado
              WHERE IdReferencia=$idReg";
    $conlocal->query($queryP);
  }
}

function obDatosContingencia($idCaja, $conlocal)
{
  $subQuery = "SELECT
        Id
      FROM transacciones
      WHERE IdCajas='$idCaja'
        AND Estado=5";

  $queryM = "SELECT
                1 AS Codigo,
                IdTransacciones AS Id,
                Xml
              FROM sat_fel_emisiones
              WHERE IdTransacciones IN ($subQuery)";

  $result = $conlocal->query($queryM);
  if ($result->num_rows > 0) {
    // VER CON QUE RECIBIR DATA
    $rows = array();
    while ($r = $result->fetch_assoc()) {
      $rows[] = $r;
    }
    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
  } else {
    $msg = array('Codigo' => "0");
    echo json_encode($msg, JSON_UNESCAPED_UNICODE);
  }
}
function obParamTmp($conlocal, $IdCaja)
{
  $queryM = "SELECT
        Value
      FROM parametros
      WHERE Id  = 'LAMDPOSLT_" . $IdCaja . "_ULTIMA_VERSION'";
  $result = $conlocal->query($queryM);
  if ($result->num_rows > 0) {
    // VER CON QUE RECIBIR DATA
    while ($row = $result->fetch_assoc()) {
      $array = $row;
    }
    return $array['Value'];
  } else {
    return "0";
  }
}
function updateTotalOrden($idOrden, $conlocal)
{
  $subQuery = "SELECT SUM(Total) FROM pedidosdetalletmp WHERE IdTransacciones=$idOrden AND Estatus IN(6,1)";
  $queryM = "UPDATE pedidostemporal SET Total=($subQuery) WHERE Id=$idOrden";
  $conlocal->query($queryM);
}
function updateCliente($json, $conlocal)
{
  $query = "UPDATE clientes SET
      Nit='$json->Nit', Telefono='$json->Celular',
      Nombre='$json->Nombre'
      WHERE Id=$json->Id";
  if (!$conlocal->query($query)) {
    //MANEJO ERRORES
    echo $conlocal->error;
  }
}
function updateDireccionCl($json, $conlocal)
{
  $query = "UPDATE direccionesdomicilio SET
      Direccion='$json->Direccion', Indicaciones='$json->Indicaciones'
      WHERE Id=$json->Id AND IdClientes=$json->IdCliente";
  if (!$conlocal->query($query)) {
    //MANEJO ERRORES
    echo $conlocal->error;
  }
}
function validarTable($conlocal, $table)
{
  $query = "DESCRIBE $table";
  $result = $conlocal->query($query);
  if ($result->num_rows > 0) {
    // VER CON QUE RECIBIR DATA
    $row = "";
    while ($r = $result->fetch_assoc()) {
      $row .= $r["Field"] . ",";
    }
    return $row;
  }
}
