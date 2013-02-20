SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';


-- -----------------------------------------------------
-- Table `amaranth`.`user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `amaranth`.`user` ;

CREATE  TABLE IF NOT EXISTS `amaranth`.`user` (
  `user_id` INT NOT NULL AUTO_INCREMENT ,
  `user_name` VARCHAR(45) NOT NULL ,
  `password` VARCHAR(32) NOT NULL ,
  `email` VARCHAR(45) NOT NULL ,
  `active` ENUM('Y','N') NOT NULL DEFAULT 'Y' ,
  `dte_join` DATETIME NOT NULL ,
  PRIMARY KEY (`user_id`) ,
  UNIQUE INDEX `user_name_UNIQUE` (`user_name` ASC) ,
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `amaranth`.`character`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `amaranth`.`character` ;

CREATE  TABLE IF NOT EXISTS `amaranth`.`character` (
  `char_id` INT NOT NULL AUTO_INCREMENT ,
  `char_name` VARCHAR(45) NOT NULL ,
  `dte_created` DATETIME NOT NULL ,
  PRIMARY KEY (`char_id`) ,
  UNIQUE INDEX `char_name_UNIQUE` (`char_name` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `amaranth`.`user_char_xref`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `amaranth`.`user_char_xref` ;

CREATE  TABLE IF NOT EXISTS `amaranth`.`user_char_xref` (
  `user_id` INT NOT NULL ,
  `char_id` INT NOT NULL ,
  PRIMARY KEY (`user_id`, `char_id`) ,
  INDEX `user_id_idx` (`user_id` ASC) ,
  INDEX `char_id_idx` (`char_id` ASC) ,
  CONSTRAINT `user_id`
    FOREIGN KEY (`user_id` )
    REFERENCES `amaranth`.`user` (`user_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `char_id`
    FOREIGN KEY (`char_id` )
    REFERENCES `amaranth`.`character` (`char_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
