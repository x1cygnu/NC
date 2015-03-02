DELIMITER ;;

USE cygnus_rootnode;;

DROP PROCEDURE IF EXISTS NC_StarsystemCreate;;
CREATE PROCEDURE NC_StarsystemCreate(
    p_ring TINYINT,
    p_X SMALLINT,
    p_Y SMALLINT,
    p_MaxPlanets TINYINT UNSIGNED,
    p_OpenTime INTEGER UNSIGNED
    )
  LANGUAGE SQL
  MODIFIES SQL DATA
  SQL SECURITY INVOKER
BEGIN
  DECLARE StarName VARCHAR(40);
  SELECT Name INTO StarName FROM NC_Config_StarsystemName
    WHERE RingLevel<p_ring
    ORDER BY RAND()
    LIMIT 1;
  UPDATE NC_Config_StarsystemName 
    SET RingLevel=p_ring+10;
    WHERE Name=StarName;
  INSERT INTO NC_Starsystem (X, Y, Name, MaxPlanets, OpenTime)
    VALUES (p_X, p_Y, StarName, p_MaxPlanets, p_OpenTime);
  SELECT LAST_INSERT_ID() AS Result;
END;;


DROP PROCEDURE IF EXISTS NC_GetRing;;
CREATE PROCEDURE NC_GetRing()
  LANGUAGE SQL
  READS SQL DATA
  SQL SECURITY INVOKER
BEGIN
  SELECT RingLevel AS Result FROM NC_Config;
END;;

DROP PROCEDURE IF EXISTS NC_GetRing;;
CREATE PROCEDURE NC_SetRing(
    NewRing TINYINT
    )
  LANGUAGE SQL
  MODIFIES SQL DATA
  SQL SECURITY INVOKER
BEGIN
  UPDATE NC_Config SET RingLevel=NewRing;
END;;

