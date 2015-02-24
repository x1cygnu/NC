DELIMITER ;;

USE cygnus_rootnode;;

DROP TRIGGER IF EXISTS NC_ContainerRemove;;
CREATE TRIGGER NC_ContainerRemove BEFORE DELETE ON NC_News
FOR EACH ROW 
  DELETE from NC_Container WHERE Container=OLD.Container;;

DROP TRIGGER IF EXISTS NC_ContainerUpdate;;
CREATE TRIGGER NC_ContainerUpdate BEFORE UPDATE ON NC_News
FOR EACH ROW 
  UPDATE NC_Container SET Container=NEW.Container WHERE Container=OLD.Container;;

DROP PROCEDURE IF EXISTS NC_NewsCreate;
CREATE PROCEDURE NC_NewsCreate(
    p_owner INTEGER UNSIGNED,
    p_type SMALLINT UNSIGNED,
    p_showtime INTEGER UNSIGNED)
  LANGUAGE SQL
  MODIFIES SQL DATA
  SQL SECURITY INVOKER
BEGIN
  INSERT INTO NC_News VALUES(p_owner, p_type, LAST_INSERT_ID(NC_NewContainer()), p_showtime);
  SELECT LAST_INSERT_ID() AS `Return`;
END;;

