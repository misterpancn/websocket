<?php
session_start();
$returnData = $_POST['param'];
if($returnData){
    $_SESSION['data'] = $returnData;
    $filePath = __DIR__.'/'.time().'.log';
    file_put_contents($filePath, $returnData);
   // var_dump($_POST['param']);
}else{
    var_dump($_SESSION['data']);
    echo 'not post data';
}
//$filePath = __DIR__.'/'.time().'.log';
//file_put_contents($filePath, '123213123213321 \n\r 45454543534543 \n\r test') or die($filePath);
