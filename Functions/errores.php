<?php
function generarLogDB($msgError,$query,$conLocal,$datos,$dw){

    date_default_timezone_set('America/Guatemala');
    $fecha=getdate();
    $fechaHora=$fecha['year']."-".str_pad($fecha['mon'],2,"0", STR_PAD_LEFT)."-".$fecha['mday']." ".
        $fecha['hours'].":".$fecha['minutes'].":".$fecha['seconds'];
    $query="INSERT INTO log_errors(IdSucursales,IdCajas,IdUsuarios,FechaHora,IdTransacciones,SerieDocumento,NoDocumento,Programa,VerPrograma,
    SQLDescripcion,Proceso,SQLError)
    VALUES(".$datos['IdSucursales'].",".$datos['IdCajas'].",".$datos['IdUsuarios'].",'$fechaHora',".$datos['IdTransacciones'].",
        '".$datos['Serie']."',".$datos['NoDocumento'].",'WebServiceLT','V1','$msgError','".$dw['proceso']."','$query')";
    $conLocal->query($query);
       
}