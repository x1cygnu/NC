DELIMITER ;;

USE cygnus_rootnode;;

DROP PROCEDURE IF EXISTS NC_SystemGet;;
CREATE PROCEDURE NC_SystemGet(
    p_SID INTEGER UNSIGNED
    )
  LANGUAGE SQL
  READS SQL DATA
  SQL SECURITY INVOKER
BEGIN
  SELECT Pl.PLID AS PLID, Pl.Orbit AS Orbit, 
    A.PID AS PID, A.PublicName AS Name,
    Pl.Pop AS Pop,
    PT.Name AS TypeName
    FROM NC_Planet Pl
    LEFT JOIN NC_Account A ON Pl.Owner = A.PID
    JOIN NC_PlanetType PT ON Pl.PlType = PT.PlType
    WHERE Pl.SID=p_SID
    ORDER BY Pl.Orbit ASC;
END;;

