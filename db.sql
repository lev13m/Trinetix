/*
SQLyog Enterprise v10.42 
MySQL - 5.5.31-0ubuntu0.12.04.1 : Database - trinetix
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`trinetix` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `trinetix`;

/*Table structure for table `nodes` */

DROP TABLE IF EXISTS `nodes`;

CREATE TABLE `nodes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `nodes_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `nodes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

/*Data for the table `nodes` */

insert  into `nodes`(`id`,`parent_id`,`name`) values (2,NULL,'node 1'),(3,2,'node 1-1'),(4,2,'node 1-2'),(5,3,'node 1-1-1'),(6,NULL,'node 2');

/* Procedure structure for procedure `findChild` */

/*!50003 DROP PROCEDURE IF EXISTS  `findChild` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`root`@`localhost` PROCEDURE `findChild`(IN id INT)
BEGIN
  DECLARE i,
  t INT ;
  DROP TABLE IF EXISTS `temp_child` ;
  CREATE TEMPORARY TABLE IF NOT EXISTS temp_child (
    id INT,
    parent_id INT,
    `name` VARCHAR (50),
    count_child INT
  ) ;
  SET t = 1 ;
  INSERT INTO temp_child (`id`, `parent_id`, `name`) 
  SELECT 
    nodes.* 
  FROM
    nodes 
  WHERE nodes.`id` = id ;
  WHILE
    t IS NOT NULL DO SET t = NULL ;
    SELECT 
      nodes.id INTO t 
    FROM
      nodes 
    WHERE nodes.`parent_id` = id 
    LIMIT 1 ;
    INSERT INTO temp_child (
      `id`,
      `parent_id`,
      `name`,
      count_child
    ) 
    SELECT 
      nodes.*,
      COUNT(child.`id`) AS count_child 
    FROM
      nodes 
      LEFT JOIN nodes AS child 
        ON child.`parent_id` = nodes.`id` 
    WHERE nodes.`parent_id` = id 
    GROUP BY nodes.id ;
    SET id = t ;
  END WHILE ;
  SELECT 
    * 
  FROM
    temp_child ;
END */$$
DELIMITER ;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
