SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `fdugroup` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `fdugroup` ;

-- -----------------------------------------------------
-- Table `fdugroup`.`category`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fdugroup`.`category` (
  `cat_id` INT NOT NULL AUTO_INCREMENT,
  `cat_name` VARCHAR(45) NOT NULL,
  `cat_pid` INT NOT NULL,
  PRIMARY KEY (`cat_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fdugroup`.`user_role`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fdugroup`.`user_role` (
  `rol_id` INT NOT NULL AUTO_INCREMENT,
  `rol_name` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`rol_id`))
ENGINE = InnoDB;

CREATE UNIQUE INDEX `rol_name_UNIQUE` ON `fdugroup`.`user_role` (`rol_name` ASC);


-- -----------------------------------------------------
-- Table `fdugroup`.`users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fdugroup`.`users` (
  `u_id` INT NOT NULL AUTO_INCREMENT,
  `u_role_id` INT NOT NULL,
  `u_name` VARCHAR(45) NOT NULL,
  `u_mail` VARCHAR(45) NOT NULL,
  `u_password` VARCHAR(255) NOT NULL,
  `u_region` VARCHAR(45) NULL COMMENT '地区',
  `u_mobile` VARCHAR(45) NULL COMMENT '手机号',
  `u_qq` VARCHAR(45) NULL,
  `u_weibo` VARCHAR(45) NULL,
  `u_register_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `u_status` INT NOT NULL DEFAULT 0 COMMENT '0: 无效用户\n1: 激活用户',
  `u_picture` VARCHAR(45) NULL COMMENT '头像 url',
  `u_intro` TEXT NULL,
  `u_homepage` VARCHAR(45) NULL,
  `u_credits` INT NULL DEFAULT 0 COMMENT '积分',
  `u_permission` INT NULL,
  `u_privacy` INT NULL,
  PRIMARY KEY (`u_id`, `u_role_id`))
ENGINE = InnoDB;

CREATE UNIQUE INDEX `u_name_UNIQUE` ON `fdugroup`.`users` (`u_name` ASC);

CREATE UNIQUE INDEX `u_mail_UNIQUE` ON `fdugroup`.`users` (`u_mail` ASC);

-- -----------------------------------------------------
-- Table `fdugroup`.`friends`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fdugroup`.`friends` (
  `f_id` INT NOT NULL AUTO_INCREMENT,
  `f_uid` INT NOT NULL,
  `f_fid` INT NOT NULL,
  PRIMARY KEY (`f_id`),
  FOREIGN KEY (`f_uid`) REFERENCES `fdugroup`.`users`(`u_id`),
  FOREIGN KEY (`f_fid`) REFERENCES `fdugroup`.`users`(`u_id`))
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `fdugroup`.`groups`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fdugroup`.`groups` (
  `gro_id` INT NOT NULL AUTO_INCREMENT,
  `gro_creator` INT NOT NULL,
  `cat_id` INT NULL,
  `gro_name` VARCHAR(45) NOT NULL,
  `gro_member_count` INT NOT NULL,
  `gro_created_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `gro_intro` LONGTEXT NULL,
  `gro_picture` VARCHAR(255) NULL,
  PRIMARY KEY (`gro_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fdugroup`.`topic`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fdugroup`.`topic` (
  `top_id` INT NOT NULL AUTO_INCREMENT,
  `gro_id` INT NOT NULL,
  `u_id` INT NOT NULL,
  `top_title` VARCHAR(45) NOT NULL,
  `top_created_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `top_content` LONGTEXT NOT NULL,
  `top_last_comment_time` TIMESTAMP NULL,
  `top_comment_count` INT NOT NULL,
  PRIMARY KEY (`top_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fdugroup`.`comment`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fdugroup`.`comment` (
  `com_id` INT NOT NULL AUTO_INCREMENT,
  `com_pid` INT NOT NULL,
  `top_id` INT NOT NULL,
  `u_id` INT NOT NULL,
  `com_created_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `com_content` TEXT NOT NULL,
  PRIMARY KEY (`com_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fdugroup`.`group_has_users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fdugroup`.`group_has_users` (
  `gro_id` INT NOT NULL,
  `u_id` INT NOT NULL,
  `join_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '加入时间',
  `status` INT NOT NULL,
  `comment` TEXT NULL,
  PRIMARY KEY (`gro_id`, `u_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fdugroup`.`entity_type`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fdugroup`.`entity_type` (
  `typ_id` INT NOT NULL AUTO_INCREMENT,
  `typ_name` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`typ_id`))
ENGINE = InnoDB;

CREATE UNIQUE INDEX `typ_name_UNIQUE` ON `fdugroup`.`entity_type` (`typ_name` ASC);


-- -----------------------------------------------------
-- Table `fdugroup`.`tag`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fdugroup`.`tag` (
  `tag_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tag_name` VARCHAR(45) NOT NULL,
  `entity_type` INT NOT NULL,
  `entity_id` INT NULL,
  PRIMARY KEY (`tag_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fdugroup`.`group_has_group`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fdugroup`.`group_has_group` (
  `group_id1` INT NOT NULL,
  `group_id2` INT NOT NULL,
  PRIMARY KEY (`group_id1`, `group_id2`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fdugroup`.`message_type`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fdugroup`.`message_type` (
  `msg_type_id` INT NOT NULL AUTO_INCREMENT,
  `msg_type_name` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`msg_type_id`))
ENGINE = InnoDB;

CREATE UNIQUE INDEX `msg_type_name_UNIQUE` ON `fdugroup`.`message_type` (`msg_type_name` ASC);


-- -----------------------------------------------------
-- Table `fdugroup`.`messages`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fdugroup`.`messages` (
  `msg_id` INT NOT NULL AUTO_INCREMENT,
  `msg_type_id` INT NOT NULL,
  `msg_receiver_id` INT NOT NULL,
  `msg_sender_id` INT NOT NULL COMMENT 'According to message_type\ntype=\'system\', sender_id=0\ntype=\'group\', sender_id=group_id\ntype=\'user\', sender_id = user_id',
  `msg_title` VARCHAR(45) NULL,
  `msg_content` TEXT NOT NULL,
  `msg_status` INT NOT NULL DEFAULT 0 COMMENT '1: not read\n2: read\nothers..',
  `msg_send_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`msg_id`, `msg_type_id`, `msg_receiver_id`))
ENGINE = InnoDB;

CREATE UNIQUE INDEX `msg_id_UNIQUE` ON `fdugroup`.`messages` (`msg_id` ASC);


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `fdugroup`.`category`
-- -----------------------------------------------------
START TRANSACTION;
USE `fdugroup`;
INSERT INTO `fdugroup`.`category` (`cat_id`, `cat_name`, `cat_pid`) VALUES (1, '兴趣', 0);
INSERT INTO `fdugroup`.`category` (`cat_id`, `cat_name`, `cat_pid`) VALUES (2, '生活', 0);
INSERT INTO `fdugroup`.`category` (`cat_id`, `cat_name`, `cat_pid`) VALUES (3, '购物', 0);
INSERT INTO `fdugroup`.`category` (`cat_id`, `cat_name`, `cat_pid`) VALUES (4, '社会', 0);
INSERT INTO `fdugroup`.`category` (`cat_id`, `cat_name`, `cat_pid`) VALUES (5, '艺术', 0);
INSERT INTO `fdugroup`.`category` (`cat_id`, `cat_name`, `cat_pid`) VALUES (6, '学术', 0);
INSERT INTO `fdugroup`.`category` (`cat_id`, `cat_name`, `cat_pid`) VALUES (7, '情感', 0);
INSERT INTO `fdugroup`.`category` (`cat_id`, `cat_name`, `cat_pid`) VALUES (8, '闲聊', 0);
INSERT INTO `fdugroup`.`category` (`cat_id`, `cat_name`, `cat_pid`) VALUES (9, '旅行', 1);
INSERT INTO `fdugroup`.`category` (`cat_id`, `cat_name`, `cat_pid`) VALUES (10, '摄影', 1);
INSERT INTO `fdugroup`.`category` (`cat_id`, `cat_name`, `cat_pid`) VALUES (11, '影视', 1);
INSERT INTO `fdugroup`.`category` (`cat_id`, `cat_name`, `cat_pid`) VALUES (12, '音乐', 1);

COMMIT;


-- -----------------------------------------------------
-- Data for table `fdugroup`.`user_role`
-- -----------------------------------------------------
START TRANSACTION;
USE `fdugroup`;
INSERT INTO `fdugroup`.`user_role` (`rol_id`, `rol_name`) VALUES (1, 'administrator');
INSERT INTO `fdugroup`.`user_role` (`rol_id`, `rol_name`) VALUES (2, 'authenticated user');
INSERT INTO `fdugroup`.`user_role` (`rol_id`, `rol_name`) VALUES (3, 'anonymous user');

COMMIT;


-- -----------------------------------------------------
-- Data for table `fdugroup`.`users`
-- -----------------------------------------------------
START TRANSACTION;
USE `fdugroup`;
INSERT INTO `fdugroup`.`users` (`u_id`, `u_role_id`, `u_name`, `u_mail`, `u_password`, `u_region`, `u_mobile`, `u_qq`, `u_weibo`, `u_register_time`, `u_status`, `u_picture`, `u_intro`, `u_homepage`, `u_credits`, `u_permission`, `u_privacy`) VALUES (1, 1, 'admin', 'admin@fudan.edu.cn', '96e79218965eb72c92a549dd5a330112', 'shanghai', NULL, NULL, NULL, '', 1, 'public/images/users/pic_u_1.jpg', NULL, NULL, 0, NULL, NULL);
INSERT INTO `fdugroup`.`users` (`u_id`, `u_role_id`, `u_name`, `u_mail`, `u_password`, `u_region`, `u_mobile`, `u_qq`, `u_weibo`, `u_register_time`, `u_status`, `u_picture`, `u_intro`, `u_homepage`, `u_credits`, `u_permission`, `u_privacy`) VALUES (2, 2, 'Raysmond', 'jiankunlei@126.com', '96e79218965eb72c92a549dd5a330112', 'shanghai', '18801734441', '913282582', 'http://weibo.com/leijiankun', NULL, 1, '', NULL, 'http://raysmond.com', 0, NULL, NULL);

COMMIT;


-- -----------------------------------------------------
-- Data for table `fdugroup`.`groups`
-- -----------------------------------------------------
START TRANSACTION;
USE `fdugroup`;
INSERT INTO `fdugroup`.`groups` (`gro_id`, `gro_creator`, `cat_id`, `gro_name`, `gro_member_count`, `gro_created_time`, `gro_intro`, `gro_picture`) VALUES (1, 1, 1, 'FDUGroup Developers', 1, NULL, NULL, NULL);

COMMIT;


-- -----------------------------------------------------
-- Data for table `fdugroup`.`group_has_users`
-- -----------------------------------------------------
START TRANSACTION;
USE `fdugroup`;
INSERT INTO `fdugroup`.`group_has_users` (`gro_id`, `u_id`, `join_time`, `status`, `comment`) VALUES (1, 1, NULL, 1, NULL);

COMMIT;


-- -----------------------------------------------------
-- Data for table `fdugroup`.`entity_type`
-- -----------------------------------------------------
START TRANSACTION;
USE `fdugroup`;
INSERT INTO `fdugroup`.`entity_type` (`typ_id`, `typ_name`) VALUES (1, 'topic');
INSERT INTO `fdugroup`.`entity_type` (`typ_id`, `typ_name`) VALUES (2, 'group');

COMMIT;


-- -----------------------------------------------------
-- Data for table `fdugroup`.`message_type`
-- -----------------------------------------------------
START TRANSACTION;
USE `fdugroup`;
INSERT INTO `fdugroup`.`message_type` (`msg_type_id`, `msg_type_name`) VALUES (1, 'system');
INSERT INTO `fdugroup`.`message_type` (`msg_type_id`, `msg_type_name`) VALUES (2, 'private');
INSERT INTO `fdugroup`.`message_type` (`msg_type_id`, `msg_type_name`) VALUES (3, 'group');
INSERT INTO `fdugroup`.`message_type` (`msg_type_id`, `msg_type_name`) VALUES (4, 'user');

COMMIT;


-- -----------------------------------------------------
-- Data for table `fdugroup`.`messages`
-- -----------------------------------------------------
START TRANSACTION;
USE `fdugroup`;
INSERT INTO `fdugroup`.`messages` (`msg_id`, `msg_type_id`, `msg_receiver_id`, `msg_sender_id`, `msg_title`, `msg_content`, `msg_status`, `msg_send_time`) VALUES (1, 4, 1, 2, 'hello', 'a message from user', 1, NULL);
INSERT INTO `fdugroup`.`messages` (`msg_id`, `msg_type_id`, `msg_receiver_id`, `msg_sender_id`, `msg_title`, `msg_content`, `msg_status`, `msg_send_time`) VALUES (2, 1, 1, 0, 'system notification', 'a system notification', 1, NULL);
INSERT INTO `fdugroup`.`messages` (`msg_id`, `msg_type_id`, `msg_receiver_id`, `msg_sender_id`, `msg_title`, `msg_content`, `msg_status`, `msg_send_time`) VALUES (3, 1, 1, 0, 'welcome', 'a welcome message', 2, NULL);

COMMIT;
