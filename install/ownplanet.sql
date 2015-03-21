DELIMITER ;;

USE cygnus_rootnode;;

DROP PROCEDURE IF EXISTS NC_OwnedPlanetsGet;;
CREATE PROCEDURE NC_OwnedPlanetsGet(
    p_PID INTEGER UNSIGNED
  )
  LANGUAGE SQL
  READS SQL DATA
  SQL SECURITY INVOKER
BEGIN
  SELECT
    P.PLID AS PLID,
    P.Orbit AS Orbit,
    S.Name AS Name,
    P.Pop AS Pop
  FROM
    NC_Planet P
    NATURAL JOIN NC_Starsystem S
  WHERE
    P.Owner = p_PID
  ORDER BY
    Pop DESC;
END;;

