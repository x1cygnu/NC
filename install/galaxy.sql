DELIMITER ;;

USE cygnus_rootnode;;

DROP PROCEDURE IF EXISTS NC_GalaxyGetRange;;
CREATE PROCEDURE NC_GalaxyGetRange(
    p_X SMALLINT,
    p_Y SMALLINT,
    p_Range SMALLINT
    )
  LANGUAGE SQL
  READS SQL DATA
  SQL SECURITY INVOKER
BEGIN
  SELECT SID, X, Y, Name, `Level`, StarType, OpenTime FROM NC_Starsystem
  WHERE X>=p_X-p_Range
  AND   X<=p_X+p_Range
  AND   Y>=p_Y-p_Range
  AND   Y<=p_Y+p_Range;
END;;

