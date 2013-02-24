SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

DROP SCHEMA IF EXISTS `amaranth` ;
CREATE SCHEMA IF NOT EXISTS `amaranth` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin ;
USE `amaranth` ;

-- -----------------------------------------------------
-- Table `amaranth`.`user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `amaranth`.`user` ;

CREATE  TABLE IF NOT EXISTS `amaranth`.`user` (
  `sak_user` INT NOT NULL AUTO_INCREMENT ,
  `user_name` VARCHAR(45) NOT NULL ,
  `password` VARCHAR(32) NOT NULL ,
  `email` VARCHAR(45) NOT NULL ,
  `active` ENUM('Y','N') NOT NULL DEFAULT 'Y' ,
  `dte_join` DATETIME NOT NULL ,
  PRIMARY KEY (`sak_user`) ,
  UNIQUE INDEX `user_name_UNIQUE` (`user_name` ASC) ,
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `amaranth`.`character`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `amaranth`.`character` ;

CREATE  TABLE IF NOT EXISTS `amaranth`.`character` (
  `sak_char` INT NOT NULL AUTO_INCREMENT ,
  `char_name` VARCHAR(45) NOT NULL ,
  `dte_created` DATETIME NOT NULL ,
  PRIMARY KEY (`sak_char`) ,
  UNIQUE INDEX `char_name_UNIQUE` (`char_name` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `amaranth`.`user_char_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `amaranth`.`user_char_xref` ;

CREATE  TABLE IF NOT EXISTS `amaranth`.`user_char_xref` (
  `sak_user` INT NOT NULL ,
  `sak_char` INT NOT NULL ,
  PRIMARY KEY (`sak_user`, `sak_char`) ,
  INDEX `user_id_idx` (`sak_user` ASC) ,
  INDEX `char_id_idx` (`sak_char` ASC) ,
  CONSTRAINT `user_id`
    FOREIGN KEY (`sak_user` )
    REFERENCES `amaranth`.`user` (`sak_user` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `char_id`
    FOREIGN KEY (`sak_char` )
    REFERENCES `amaranth`.`character` (`sak_char` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `amaranth`.`user_roles`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `amaranth`.`user_roles` ;

CREATE  TABLE IF NOT EXISTS `amaranth`.`user_roles` (
  `sak_role` INT NOT NULL AUTO_INCREMENT ,
  `level` INT NOT NULL ,
  `desc` VARCHAR(45) NULL ,
  PRIMARY KEY (`sak_role`, `level`) ,
  UNIQUE INDEX `sak_role_UNIQUE` (`sak_role` ASC) ,
  UNIQUE INDEX `level_UNIQUE` (`level` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `amaranth`.`user_role_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `amaranth`.`user_role_xref` ;

CREATE  TABLE IF NOT EXISTS `amaranth`.`user_role_xref` (
  `sak_user` INT NOT NULL ,
  `sak_role` INT NOT NULL ,
  PRIMARY KEY (`sak_user`, `sak_role`) ,
  UNIQUE INDEX `sak_role_UNIQUE` (`sak_role` ASC) ,
  UNIQUE INDEX `sak_user_UNIQUE` (`sak_user` ASC) ,
  CONSTRAINT `sak_role`
    FOREIGN KEY (`sak_role` )
    REFERENCES `amaranth`.`user_roles` (`sak_role` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `sak_user`
    FOREIGN KEY (`sak_user` )
    REFERENCES `amaranth`.`user` (`sak_user` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

USE `amaranth` ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `amaranth`.`user`
-- -----------------------------------------------------
START TRANSACTION;
USE `amaranth`;
INSERT INTO `amaranth`.`user` (`sak_user`, `user_name`, `password`, `email`, `active`, `dte_join`) VALUES (1, 'admin', '456b7016a916a4b178dd72b947c152b7', ' ', 'Y', ' ');

COMMIT;

-- -----------------------------------------------------
-- Data for table `amaranth`.`user_roles`
-- -----------------------------------------------------
START TRANSACTION;
USE `amaranth`;
INSERT INTO `amaranth`.`user_roles` (`sak_role`, `level`, `desc`) VALUES (1, 100, 'Owner');
INSERT INTO `amaranth`.`user_roles` (`sak_role`, `level`, `desc`) VALUES (2, 80, 'Admin');
INSERT INTO `amaranth`.`user_roles` (`sak_role`, `level`, `desc`) VALUES (3, 60, 'Moderator');
INSERT INTO `amaranth`.`user_roles` (`sak_role`, `level`, `desc`) VALUES (4, 40, 'User');
INSERT INTO `amaranth`.`user_roles` (`sak_role`, `level`, `desc`) VALUES (5, 20, 'Restricted');
INSERT INTO `amaranth`.`user_roles` (`sak_role`, `level`, `desc`) VALUES (6, 10, 'Banned');

COMMIT;

-- -----------------------------------------------------
-- Data for table `amaranth`.`user_role_xref`
-- -----------------------------------------------------
START TRANSACTION;
USE `amaranth`;
INSERT INTO `amaranth`.`user_role_xref` (`sak_user`, `sak_role`) VALUES (1, 1);

COMMIT;
