DELIMITER ;;

USE cygnus_rootnode;;

DROP PROCEDURE IF EXISTS NC_RaceGet;;
CREATE PROCEDURE NC_RaceGet(
    p_pid INTEGER UNSIGNED,
    p_type TINYINT UNSIGNED
    )
  LANGUAGE SQL
  READS SQL DATA
  SQL SECURITY INVOKER
BEGIN
  SELECT Value AS `Result` FROM NC_PlayerRace
  WHERE PID=p_pid AND Attribute=p_type;
END;;

DROP PROCEDURE IF EXISTS NC_RaceSet;;
CREATE PROCEDURE NC_NewsSetItem(
    p_pid INTEGER UNSIGNED,
    p_type TINYINT UNSIGNED,
    p_value TINYINT
    )
  LANGUAGE SQL
  MODIFIES SQL DATA
  SQL SECURITY INVOKER
BEGIN
  INSERT INTO NC_PlayerRace VALUES(p_pid, p_type, p_value)
  ON DUPLICATE KEY UPDATE Value=p_value;
END;;

