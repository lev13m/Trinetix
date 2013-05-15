<?php
ini_set('display_errors', 1);
include_once 'Configs.php';
include_once 'Model.php';

$configs = new Configs();
$model = new Model();
$model->connectToDb($configs);

$mainChild = $model->getMainNodes();
?>

<link href="/css/style.css" type=text/css rel=stylesheet>
<script src="/js/jquery.js"></script>
<script src="/js/nodes.js"></script>

<?php foreach ($mainChild as $node): ?>
	<div>
		<a href="#" data-type="<?= $node['count_child']; ?>" data-id="<?= $node['id']; ?>">
			<span><?= $node['count_child'] == 0 ? '-' : '+'; ?></span>
			<?= "{$node['name']} ({$node['count_child']})"; ?></a>
		<div class="child"></div>
	</div>
<?php endforeach; ?>

