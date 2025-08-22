<?php

//chdir('../../app/classes');
//die(getcwd());
if(file_exists('../../sge/app/classes/Barcode.php')){
	//echo 'encontrado';
	require_once('../../sge/app/classes/Barcode.php');
}
elseif(file_exists('../../app/classes/Barcode.php')){
	require('../../app/classes/Bgitarcode.php');
}
else
	echo 'nenhuma classe de geração de CB encontrada';

new \App\classes\barcode($_GET['code']);

?>
