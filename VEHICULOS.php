

<div id="content">     
			<hr/>
		<strong>	  <p class="mb-0 text-uppercase" ><img src="includes/contraer31.png" id="mostrar34" style="cursor:pointer;"/>
<img src="includes/contraer41.png" id="ocultar34" style="cursor:pointer;"/>&nbsp;&nbsp;&nbsp; VEHÍCULOS</p></strong></div>


<div  id="mensajeVEHICULOSEVE2">
<div class="progress" style="width: 25%;">
<div class="progress-bar" role="progressbar" style="width: <?php echo $Aeventosporcentaje ; ?>%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"><?php echo $Aeventosporcentaje ; ?>%</div></div>
									</div>
							
	        <div id="target34" style="display:block;" class="content2">
        <div class="card">
          <div class="card-body" id='actualizaVehiculos'>
                                <?php if($conexion->variablespermisos('','VEHIEVE','guardar')=='si' and $var_bloquea_fecha=='no'){ ?>
                      <form class="row g-3 needs-validation was-validated" id="VEHICULOSEVEform"  novalidate="" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
 
                      <table  style="border-collapse: collapse;" border="1" class="table mb-0 table-striped">



                      
  
				 
					  <tr style="background:#ebf8fa"> 
         <th scope="row"> <label for="validationCustom03" class="form-label">VEHÍCULO:</label></th>
         <td>

                        <span>
<?php
$encabezado = '';




if($conexion->variablespermisos('','vervehiculo','ver')=='si'){
$queryper = $conexion->lista_plantillaventavehi_todos();
}else{
$queryper = $conexion->lista_plantillaventavehi();
}



$encabezado = '<select class="form-select mb-3" aria-label="Default select example" id="VEHICULOSEVE_VEHICULO" required="" name="VEHICULOSEVE_VEHICULO" onchange="OBTENER_VEHICULO()">
<option value="">SELECCIONA UNA OPCIÓN</option>';

$fondos = array("fff0df","f4ffdf","dfffed","dffeff","dfe8ff","efdfff","ffdffd","efdfff","ffdfe9");
$num = 0;

while($row1 = mysqli_fetch_array($queryper))
{

if($num==8){$num=0;}else{$num++;}

$select='';
if($VEHICULOSEVE_VEHICULO==$row1['SUBMARCAV']){$select = "selected";};

$option20 .= '<option style="background: #'.$fondos[$num].'" '.$select.' value="'.$row1['id'].'">'.$row1['SUBMARCAV'].'</option>';
}
echo $encabezado.$option20.'</select>';		
?>
</span>

 </td>                        
         </tr>

		 
		 
         <tr style="background:#d4f1d3"> 
         <th scope="row"> <label for="validationCustom03" class="form-label">CANTIDAD:</label></th>
         <td><input type="text" class="form-control" id="validationCustom03" required=""  value="1"  name="VEHICULOSEVE_CANTIDAD"  placeholder=""  readonly="readonly"></td>
         </tr>
        
         <tr style="background:#d4f1d3"> 
         <th scope="row"> <label for="validationCustom03" class="form-label">FOTO:</label></th>

         <td id="fotos_vehiculo">

<?php
$fotos_vehiculos = $_SESSION['fotos_vehiculos'];
if($_SESSION['fotos_vehiculos']!=''){
echo $conexion->descargararchivo($fotos_vehiculos);
}

?>
<input type="hidden" name="VEHICULOSEVE_FOTO" value="<?php echo $fotos_vehiculos; ?>">  

 </td>
 


</tr>
      
          <tr style="background:#ebf8fa"> 
         <th scope="row"> <label for="validationCustom03" class="form-label">COLOR:</label></th>
<td id="OBTENER_color">

 <input type="text" class="form-control" id="" required=""   value="<?php echo $COLORV; ?>"  name="COLORV"  placeholder="" readonly="readonly">

 </td> </tr>
           <tr style="background:#ebf8fa"> 
         <th scope="row"> <label for="validationCustom03" class="form-label">PLACAS:</label></th>
<td id="OBTENER_placas">

 <input type="text" class="form-control" id="" required=""   value="<?php echo $PLACASV; ?>"  name="PLACASV"  placeholder="" readonly="readonly">

 </td> </tr>
 
 		  <tr style="background:#ebf8fa"> 

<th scope="row"> <label  for="validationCustom03" class="form-label">NOMBRE DEL QUE SOLICITA:</label></th>
<td><input type="text" class="form-control" id="validationCustom03" required=""  value="<?php echo $_SESSION["NOMBREUSUARIO"]; ?>" name="nombreingresov" placeholder="NOMBRE DEL EJECUTIVO QUE INGRESO" readonly="readonly"></td>
</tr>
		 
                 <tr style="background:#ebf8fa">
    <th style="text-align:left" scope="col">NOMBRE DEL QUE MANEJA EL VEHÍCULO:</th>
       <td>
<?php
$encabezadoA = '';
$queryper = $conexion->colaborador_generico_bueno();
$encabezadoA = '<select class="form-select mb-3" aria-label="Default select example" id="nombreocupov" required="" name="nombreocupov"  placeholder="SELECIONA UNA OPCIÓN">
<option> SELECIONA UNA OPCIÓN</option>';


$fondos = array("fff0df","f4ffdf","dfffed","dffeff","dfe8ff","efdfff","ffdffd","efdfff","ffdfe9");
$num = 0;

while($row = mysqli_fetch_array($queryper))
{

if($num==8){$num=0;}else{$num++;}

$select='';
if($_SESSION['idem']==$row['idRelacion']){
$select='selected';
}

$option2 .= '<option style="background: #'.$fondos[$num].'" '.$select.' 
value="'.$row['NOMBRE_1'].' '.$row['NOMBRE_2'].' '.$row['APELLIDO_PATERNO'].' '.$row['APELLIDO_MATERNO'].'">'.$row['NOMBRE_1'].' '.$row['NOMBRE_2'].' '.$row['APELLIDO_PATERNO'].' '.$row['APELLIDO_MATERNO'].
'</option>';
}
echo $encabezadoA.$option2.'</select>';		
?></td>

    </tr>
 
          <tr style="background:#ebf8fa"> 
         <th scope="row"> <label for="validationCustom03" class="form-label">FECHA DE ENTREGA:<br><a style="color:red;font:7px">obligatorio</a></label></th>
<td>

 <input type="date" class="form-control" id="tot" required=""   value="<?php echo $VEHICULOSEVE_ENTREGA; ?>"  name="VEHICULOSEVE_ENTREGA"  placeholder="">

 </td> </tr> 
 
 
 
 
        

          <tr style="background:#ebf8fa"> 
         <th scope="row"> <label for="validationCustom03" class="form-label">LUGAR DE ENTREGA:</label></th>
         <td>

 <input type="text" class="form-control" id="validationCustom03" required=""   value="<?php echo $VEHICULOSEVE_LUGAR; ?>"  name="VEHICULOSEVE_LUGAR"  placeholder="" >

 </td>
         </tr>
         <tr style="background:#ebf8fa"> 
         <th scope="row"> <label for="validationCustom03" class="form-label">HORA DE ENTREGA</label></th>
         <td><input type="time" class="form-control" id="validationCustom03" required=""   value="<?php echo $VEHICULOSEVE_HORA; ?>"  name="VEHICULOSEVE_HORA"  placeholder=""></td>
         </tr>
        
          <tr style="background:#ebf8fa"> 
         <th scope="row"> <label for="validationCustom03" class="form-label">FECHA DE DEVOLUCIÓN:<br><a style="color:red;font:7px">obligatorio</a></label></th>
         <td>

 <input type="date" class="form-control" id="validationCustom03" required=""   value="<?php echo $VEHICULOSEVE_DEVOLU; ?>"  name="VEHICULOSEVE_DEVOLU"  placeholder="">

 </td>
         </tr>
          <tr style="background:#ebf8fa">  

         <th scope="row"> <label for="validationCustom03" class="form-label">LUGAR DE DEVOLUCIÓN:</label></th>
         <td><input type="text" class="form-control" id="validationCustom03" required=""   value="<?php echo $VEHICULOSEVE_LUDEVO; ?>"  name="VEHICULOSEVE_LUDEVO" placeholder=""></td>
         </tr>
         	 
           <tr style="background:#ebf8fa"> 
         <th scope="row"> <label for="validationCustom03" class="form-label">HORA DE DEVOLUCIÓN:</label></th>
         <td>

 <input type="time" class="form-control" id="validationCustom03" required=""   value="<?php echo $VEHICULOSEVE_HORADEVO; ?>"  name="VEHICULOSEVE_HORADEVO"  placeholder="">

 </td>
         </tr> 
         
                                                                                                
         <tr style="background:#d4f1d3">     
         <th scope="row"> <label for="validationCustom03" class="form-label">FECHA DE SOLICITUD:</label></th>
         <td><input type="text" class="form-control" id="validationCustom03" required=""   value="<?php echo date('d-m-Y'); ?>"  name="VEHICULOSEVE_SOLICITUD"  placeholder="" readonly="readonly"></td>
         </tr>
        
		   <tr style="background:#d4f1d3"> 
         <th scope="row"> <label for="validationCustom03" class="form-label">DIAS SOLICITADOS:</label></th>
         <td>

 <input type="text" class="form-control" id="validationCustom03" required=""   value="<?php echo $VEHICULOSEVE_DIAS; ?>"  name="VEHICULOSEVE_DIAS" placeholder="">

 </td>
         </tr>
		 <tr style="background:#d4f1d3"> 
         <th  scope="row"> <label for="validationCustom03" class="form-label">COSTO:</label></th>
         <td>

         <div class="input-group mb-3"> <span class="input-group-text">$</span><input type="text"  style="width:450px;height:40px;"  class="form-control" id="VEHICULOSEVE_COSTO" required="" value="<?php echo number_format($VEHICULOSEVE_COSTO,2,'.',','); ?>" onkeyup="comasainput('VEHICULOSEVE_COSTO')" name="VEHICULOSEVE_COSTO" placeholder="" onclick="total_cantidad_x_precio()"  readonly="readonly">
 </div>
 </td>
         </tr>



		 <tr style="background:#d4f1d3"> 
         <th  scope="row"> <label for="validationCustom03" class="form-label">SUB TOTAL:</label></th>
         <td>

         <div class="input-group mb-3"> <span class="input-group-text">$</span><input type="text"  style="width:450px;height:40px;"  class="form-control" id="VEHICULOSEVE_SUB" required="" value="<?php echo number_format($VEHICULOSEVE_SUB,2,'.',','); ?>" onkeyup="comasainput('VEHICULOSEVE_SUB')" name="VEHICULOSEVE_SUB" placeholder="">
 </div>
 </td>
         </tr>



        
		   <tr style="background:#ebf8fa"> 
         <th scope="row"> <label for="validationCustom03" class="form-label">I.V.A.</label></th>
         <td>

 <input type="text" class="form-control" id="validationCustom03" required=""  value="<?php echo $VEHICULOSEVE_IVA; ?>"  name="VEHICULOSEVE_IVA" placeholder="">

 </td>
         </tr>
        

        

         <tr style="background:#d4f1d3"> 
         <th  scope="row"> <label for="validationCustom03" class="form-label">TOTAL:</label></th>
         <td>

         <div class="input-group mb-3"> <span class="input-group-text">$</span><input type="text"  style="width:450px;height:40px;"  class="form-control" id="PRECIOPESOS_SOFTWARE" required="" value="<?php echo number_format($PRECIOPESOS_SOFTWARE,2,'.',','); ?>" onkeyup="comasainput('PRECIOPESOS_SOFTWARE')" name="PRECIOPESOS_SOFTWARE" placeholder="">
 </div>
 </td>
         </tr>
         

        
            <tr style="background:#ebf8fa">     
         <th scope="row"> <label for="validationCustom03" class="form-label">OBSERVACIONES</label></th>
         <td>

 <input type="text" class="form-control" id="validationCustom03" required=""   value="<?php echo $VEHICULOSEVE_OBSERVA; ?>"  name="VEHICULOSEVE_OBSERVA" placeholder="">

 </td>
         </tr>


 
                  </table><table><tr>


   
 
                                    
    <input type="hidden" value="HVEHICULOSEVE" name="HVEHICULOSEVE"/>     
 
        
 <td>
           
</td>
      

 <td>
           

 <button  style="float:right"  class="btn btn-sm btn-outline-success px-5"  type="button" id="GUARDAR_VEHICULOSEVE" name="GUARDAR_VEHICULOSEVE">GUARDAR</button> <div style="

    text-shadow: 1px 1px 1px #919191,
        1px 2px 1px #919191,
        1px 3px 1px #919191,
        1px 4px 1px #919191,
        1px 5px 1px #919191,
        1px 6px 1px #919191,
        1px 7px 1px #919191,
        1px 8px 1px #919191,
        1px 9px 1px #919191,
        1px 10px 1px #919191,
    1px 18px 6px rgba(16,16,16,0.4),
    1px 22px 10px rgba(16,16,16,0.2),
    1px 25px 35px rgba(16,16,16,0.2),
    1px 30px 60px rgba(16,16,16,0.4);"   id="mensajeVEHICULOSEVE"></td><?php } ?></tr>
           
                   </table>

                  </form>
				  
  
  
			<form name="form_emai_vehiculos" id="form_emai_vehiculos">
			<table>
			<tr>
			<?php if($conexion->variablespermisos('','VEHIEVE','email')=='si' and $var_bloquea_fecha=='no'){ ?>	
			<td ><textarea  placeholder="ESCRIBE AQUÍ TUS CORREOS SEPARADOS POR PUNTO Y COMA EJEMPLO: NOMBRE@CORREO.ES;NOMBRE@CORREO.ES" style="width: 500px;" name="EMAIL_VEHICULOSEVE" id="EMAIL_VEHICULOSEVE" class="form-control" aria-label="With textarea"><?php echo $EMAIL_VEHICULOSEVE; ?></textarea>
            <button class="btn btn-sm btn-outline-success px-5"  type="button" id="BUTTON_VEHICULOSEVE">ENVIAR POR EMAIL</button></td> <?php } ?>  
	
			</tr>
			</table>

                        <?php
$querycontras = $altaeventos->Listado_VEHICULOSEVE();
?>

<br />
<div class='table-responsive'>
<div align='right'>
</div>
<br />
<div id='employee_table'>
<tbody= 'font-style:italic;'>
<table class="table table-striped table-bordered" style="width:100%" id='reset_VEHICULOSEVE' name='reset_VEHICULOSEVE'>
<tr style='background:#f5f9fc;text-align:center'>
<th width="10%"style="background:#c9e8e8">ENVIAR POR EMAIL</th>  
<th width="20%"style="background:#c9e8e8">VEHÍCULO</th>
<th width="20%"style="background:#c9e8e8">CANTIDAD</th>
<th width="20%"style="background:#c9e8e8">FOTO</th>
<th width="20%"style="background:#c9e8e8">COLOR</th>
<th width="20%"style="background:#c9e8e8">PLACAS</th>
<th width="20%"style="background:#c9e8e8">NOMBRE DEL QUE SOLICITA</th>
<th width="20%"style="background:#c9e8e8">NOMBRE DEL QUE MANEJA EL VEHÍCULO</th>
<th width="20%"style="background:#c9e8e8">FECHA DE ENTREGA</th>
<th width="20%"style="background:#c9e8e8">LUGAR DE ENTREGA</th>
<th width="20%"style="background:#c9e8e8">HORA DE ENTREGA</th>
<th width="20%"style="background:#c9e8e8">FECHA DE DEVOLUCIÓN</th>
<th width="20%"style="background:#c9e8e8">LUGAR DE DEVOLUCIÓN</th>
<th width="20%"style="background:#c9e8e8">HORA DE DEVOLUCIÓN</th>
<th width="20%"style="background:#c9e8e8">FECHA DE SOLICITUD</th>
<th width="20%"style="background:#c9e8e8">DIAS SOLICITADOS</th>
<th width="20%"style="background:#c9e8e8">COSTO</th>
<th width="20%"style="background:#c9e8e8">SUB TOTAL</th>
<th width="20%"style="background:#c9e8e8">IVA</th>
<th width="20%"style="background:#c9e8e8">TOTAL</th>
<th width="20%"style="background:#c9e8e8">OBSERVACIONES</th>


</tr>

<?php
$urlVEHICULOSEVE_FOTO ='';
while($row = mysqli_fetch_array($querycontras))
{	
	$urlVEHICULOSEVE_FOTO = $conexion->descargararchivo($row["VEHICULOSEVE_FOTO"]);
?>


<tr style='background:#f5f9fc;text-align:center'>
<td style="text-align:center" >
<input type="checkbox" style="width:15%" class="form-check-input" name="VEHICULOSEVE[]" id="VEHICULOSEVE" value="<?php echo $row["id"]; ?>"/> </td>
<td ><?php echo $altaeventos->nombre_vehiculo($row["VEHICULOSEVE_VEHICULO"]);?></td>
<td ><?php echo $row["VEHICULOSEVE_CANTIDAD"]; ?></td>
<td ><?php echo $urlVEHICULOSEVE_FOTO; ?></td>
<td ><?php echo $row["COLORV"]; ?></td>
<td ><?php echo $row["PLACASV"]; ?></td>

<td ><?php echo $row["nombreingresov"]; ?></td>
<td ><?php echo $row["nombreocupov"]; ?></td>
<td ><?php echo $row["VEHICULOSEVE_ENTREGA"]; ?></td>
<td ><?php echo $row["VEHICULOSEVE_LUGAR"]; ?></td>
<td ><?php echo $row["VEHICULOSEVE_HORA"]; ?></td>
<td ><?php echo $row["VEHICULOSEVE_DEVOLU"]; ?></td>
<td ><?php echo $row["VEHICULOSEVE_LUDEVO"]; ?></td>
<td ><?php echo $row["VEHICULOSEVE_HORADEVO"]; ?></td>
<td ><?php echo $row["VEHICULOSEVE_SOLICITUD"]; ?></td>
<td ><?php echo $row["VEHICULOSEVE_DIAS"]; ?></td>
<td ><?php echo number_format($row["VEHICULOSEVE_COSTO"],2,'.',','); ?></td>
<td ><?php echo number_format($row["VEHICULOSEVE_SUB"],2,'.',','); ?></td>
<td ><?php echo number_format($row["VEHICULOSEVE_IVA"],2,'.',','); ?></td>
<td ><?php echo number_format($row["PRECIOPESOS_SOFTWARE"],2,'.',','); ?></td>
<td ><?php echo $row["VEHICULOSEVE_OBSERVA"]; ?></td>
<?php if($conexion->variablespermisos('','VEHIEVE','modificar')=='si' and $var_bloquea_fecha=='no'){ ?>
<td><input type="button" name="view" value="MODIFICAR" id="<?php echo $row["id"]; ?>" class="btn btn-info btn-xs view_VEHICULOSEVE" /></td><?php } ?>
<?php if($conexion->variablespermisos('','VEHIEVE','borrar')=='si' and $var_bloquea_fecha=='no'){ ?>
<td><input type="button" name="view2" value="BORRAR" id="<?php echo $row["id"]; ?>" class="btn btn-info btn-xs view_dataVEHICULOSEVEborrar" />
</td><?php } ?>
</tr>
<?php
$GSUNTOTAL += $row["VEHICULOSEVE_SUB"];
$GIVA += $row["VEHICULOSEVE_IVA"];
$GTOTAL += $row["PRECIOPESOS_SOFTWARE"];
}
?>
<tr>

<td colspan='17' style="text-align:right;"><strong style="font-size:16px">TOTALES</strong></td><td>$ <?php echo number_format($GSUNTOTAL,2,'.',','); ?></td><td>$ <?php echo number_format($GIVA,2,'.',','); ?></td><td>$ <?php echo number_format($GTOTAL,2,'.',','); ?></td><td></td></tr>
</table>
</tbody>


			</form>
                  
</div>
</div> 
</div>
</div> 
</div>
 
 <?php 

$_SESSION['VEHICULOSEVE_VEHICULO'] = '';

?>