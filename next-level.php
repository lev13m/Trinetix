<?php

include_once 'Configs.php';
include_once 'Model.php';

$configs = new Configs();
$model = new Model();
$model->connectToDb($configs);

$nextChild = $model->getChildren($_POST['node_id']);
echo json_encode($nextChild, JSON_FORCE_OBJECT);
?>
