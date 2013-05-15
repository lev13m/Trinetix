<?php

class Model
{

	/**
	 * Подключение к базе
	 * @param Configs $configs
	 * @return void
	 */
	public function connectToDb(Configs $configs)
	{
		$result = mysql_connect($configs->_host, $configs->_user, $configs->_pass);
		if (!$result) throw new Exception('Не удалось подключиться к mysql');
		$result = mysql_select_db($configs->_db);
		if (!$result) throw new Exception('Не удалось подключиться к базе');
	}

	/**
	 * Добавление узла
	 * @param string $name
	 * @param int $parentId
	 */
	public function addNode($name, $parentId = null)
	{
		if ($parentId == null) $parentId = 'null';
		$sql = 'INSERT INTO nodes
				SET nodes.`name` = "' . $name . '",
				nodes.`parent_id` = ' . $parentId;
		mysql_query($sql);
	}

	/**
	 * Удаление узла
	 * @param int $nodeId
	 */
	public function rmNode($nodeId)
	{
		$sql = "
			DELETE FROM nodes
			WHERE nodes.`id` = {$nodeId}";
		mysql_query($sql);
	}

	/**
	 * Выборки ноды и всех чайлдов
	 * @param int $nodeId
	 * @return array
	 */
	public function getNodeAndChild($nodeId)
	{
		$sql = 'CALL findChild(' . $nodeId . ');';
		$resp = mysql_query($sql);
		if (!$resp) throw new Exception(mysql_error());

		return $this->_toArray($resp);
	}

	/**
	 * Обработка результата запроса
	 * @param resource $resp
	 * @return array
	 */
	private function _toArray($resp)
	{
		$data = array();
		while ($el = mysql_fetch_array($resp))
		{
			$data[] = $el;
		}
		return $data;
	}

	/**
	 * Процедура для получения всех чайлдов определенного parent'а
	 * @throws Exception
	 */
	public function createPocedure()
	{
		$sql = '
				DELIMITER $$
				DROP PROCEDURE IF EXISTS `findChild`$$
				CREATE PROCEDURE `findChild` (IN id INT) 
				BEGIN
				DECLARE i, t INT ;
					
				DROP TABLE IF EXISTS `temp_child`;
				CREATE TEMPORARY TABLE IF NOT EXISTS  temp_child(id INT, parent_id INT, `name` VARCHAR(50), count_child INT);
					
				SET t = 1;
				INSERT INTO temp_child (`id`, `parent_id`, `name`)
				SELECT nodes.* FROM nodes WHERE nodes.`id` = id;
					
				WHILE t IS NOT NULL DO 
				SET t = NULL;
				SELECT nodes.id INTO t FROM nodes WHERE nodes.`parent_id` = id LIMIT 1;   
				
				INSERT INTO temp_child (`id`, `parent_id`, `name`, count_child)
				SELECT nodes.*, COUNT(child.`id`) AS count_child FROM nodes 
				LEFT JOIN nodes AS child ON child.`parent_id` = nodes.`id` 
				WHERE nodes.`parent_id` = id
				GROUP BY nodes.id;
    
				SET id = t;
				END WHILE ;
				SELECT * FROM temp_child;
				END $$
				DELIMITER;';
		if (mysql_query($sql)) throw new Exception('Процедура не создана');
	}

	/**
	 * Получить список первых узлов
	 * @return array
	 */
	public function getMainNodes()
	{
		$sql = '
			SELECT 
				nodes.`id`,
				nodes.`name`,
				COUNT(child.`id`) as count_child
			FROM
				nodes 
			LEFT JOIN nodes AS child 
				ON child.`parent_id` = nodes.`id` 
			WHERE nodes.`parent_id` IS NULL 
			GROUP BY nodes.`id`';
		$resp = mysql_query($sql);
		if (!$resp) throw new Exception(mysql_error());

		return $this->_toArray($resp);
	}

	/**
	 * Отдать child'ы следующего уровня определенного parent'a
	 * @param int $parentId
	 * @return array
	 * @throws Exception
	 */
	public function getChildren($parentId)
	{
		$sql = "
			SELECT 
				nodes.`id`,
				nodes.`name`,
				COUNT(child.`id`) AS count_child 
			FROM nodes 
			LEFT JOIN nodes AS child 
				ON child.`parent_id` = nodes.`id` 
			WHERE nodes.`parent_id` = {$parentId}
			GROUP BY nodes.`id` ";
		$resp = mysql_query($sql);
		if (!$resp) throw new Exception(mysql_error());

		return $this->_toArray($resp);
	}

	/**
	 * Сортировка полученных от процедуры значений
	 * @param array $nodes
	 * @param int $firstNode
	 * @return \Node
	 */
	public function sortAllNides(array $nodes, $firstNode)
	{
		$data = array();

		foreach ($nodes as $node)
		{
			$data[$node['id']] = new Node($node['id']);
			$data[$node['id']]->setData($node);

			if ($node['parent_id'] != null)
			{
				if(!isset($data[$node['parent_id']]))$data[$node['parent_id']] = new Node($node['parent_id']);
				$data[$node['parent_id']]->addChild($data[$node['id']]);
			}
		}
		return $data[$firstNode];
	}

}

class Node
{

	private $_id;
	private $_child = array();
	private $_data = array();

	public function __construct($id)
	{
		$this->_id = $id;
	}

	public function setData($data)
	{
		$this->_data['id'] = $data['id'];
		$this->_data['parent_id'] = $data['parent_id'];
		$this->_data['name'] = $data['name'];
		$this->_data['count_child'] = $data['count_child'];
	}

	public function addChild(Node $node)
	{
		$this->_child[] = $node;
	}

	public function _toArray()
	{
		$data = $this->_data;
		foreach ($this->_child as $child)
		{
			$data['children'][] = $child->_toArray();
		}
		return $data;
	}

}
