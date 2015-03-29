DELIMITER ;;

USE cygnus_rootnode;;

DROP PROCEDURE IF EXISTS NC_ShipsAvailable;;
CREATE PROCEDURE NC_ShipsAvailable(
    p_PLID INTEGER UNSIGNED
  )
  LANGUAGE SQL
  READS SQL DATA
  SQL SECURITY INVOKER
BEGIN
  SELECT
    Sh.ItemType AS ItemType,
    Sh.Cost AS Cost,
    Sh.Attack AS Attack,
    Sh.Defense AS Defense
  FROM
    NC_Config_Fleet Sh;
END;;

