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
    PD.Value AS Population,
    PD.Progress AS PopulationFraction
  FROM
    NC_Planet P
    NATURAL JOIN NC_Starsystem S
    JOIN NC_PlanetData PD ON PD.PLID=P.PLID AND ItemType=1
  WHERE
    P.Owner = p_PID
  ORDER BY
    Population DESC, PopulationFraction DESC;
END;;

