<?php
include_once 'Configs.php';
include_once 'Model.php';

$configs = new Configs();
$model = new Model();
$model->connectToDb($configs);

$allChild = $model->getNodeAndChild($_POST['node_id']);
$allChild = $model->sortAllNides($allChild, $_POST['node_id']);
echo json_encode($allChild->_toArray(), JSON_FORCE_OBJECT);
?>
