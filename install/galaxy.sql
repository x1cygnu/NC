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

DROP PROCEDURE IF EXISTS NC_GalaxyGetBackground;;
CREATE PROCEDURE NC_GalaxyGetBackground(
    p_X1 SMALLINT,
    p_Y1 SMALLINT,
    p_X2 SMALLINT,
    p_Y2 SMALLINT
    )
  LANGUAGE SQL
  READS SQL DATA
  SQL SECURITY INVOKER
BEGIN
  SELECT X1, Y1, X2, Y2, FileName, Z, Opacity FROM NC_Config_Background
  WHERE X1<=p_X2
  AND   Y1<=p_Y2
  AND   X2>=p_X1
  AND   Y2>=p_Y1;
END;;

