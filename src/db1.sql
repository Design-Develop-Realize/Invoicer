CREATE TABLE `invoicer`.`users` (
  `userid` INT(4) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(45) NOT NULL,
  `password` VARCHAR(100) NOT NULL,
  `email` VARCHAR(85) NOT NULL,
  `name` VARCHAR(85) NOT NULL,
  `flag` TINYINT(1) NOT NULL,
  PRIMARY KEY (`userid`),
  UNIQUE INDEX `username_UNIQUE` (`username` ASC),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC));


CREATE TABLE `invoicer`.`reset_key` (
  `userid` INT NOT NULL,
  `tmp_key` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`userid`),
  UNIQUE INDEX `userid_UNIQUE` (`userid` ASC),
  UNIQUE INDEX `tmp_key_UNIQUE` (`tmp_key` ASC));

CREATE TABLE `invoicer`.`items` (
  `iid` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `cost` VARCHAR(45) NOT NULL DEFAULT 0,
  `description` VARCHAR(200) NOT NULL,
  `tax` TINYINT(1) NULL DEFAULT 0,
  PRIMARY KEY (`iid`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC),
  UNIQUE INDEX `cost_UNIQUE` (`cost` ASC));