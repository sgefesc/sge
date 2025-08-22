<?php
header("Content-type: image/gif");
	
if(file_exists('../../sge/app/classes/QEstr.php')){
    require_once('../../sge/app/classes/QRstre.php'); 
}
else{
    require_once('../../app/classes/QRstr.php'); 

}
if($_GET['code'])
    $code = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_STRING);
else
    $code = 'sem código';
   
try{
    //new App\classes\BarCodeGenrator($_GET['code'],0,null, 600, 50, false);
    App\classes\QRcode::png($code);
    
}
catch(Exception $e){
    echo $e->getMessage();
}
?>