DELIMITER ;;

USE cygnus_rootnode;;

DROP PROCEDURE IF EXISTS NC_StarsystemCreate;;
CREATE PROCEDURE NC_StarsystemCreate(
    p_ring TINYINT,
    p_X SMALLINT,
    p_Y SMALLINT,
    p_Type TINYINT UNSIGNED,
    p_MaxPlanets TINYINT UNSIGNED,
    p_OpenTime INTEGER UNSIGNED
    )
  LANGUAGE SQL
  MODIFIES SQL DATA
  SQL SECURITY INVOKER
BEGIN
  DECLARE StarName VARCHAR(40);
  SELECT Name INTO StarName FROM NC_Config_StarsystemNames
    WHERE RingLevel<p_ring
    ORDER BY RAND()
    LIMIT 1;
  UPDATE NC_Config_StarsystemNames
    SET RingLevel=p_ring+10
    WHERE Name=StarName;
  INSERT INTO NC_Starsystem (X, Y, Name, MaxPlanets, OpenTime, StarType)
    VALUES (p_X, p_Y, StarName, p_MaxPlanets, p_OpenTime, p_Type);
  SELECT LAST_INSERT_ID() AS Result;
END;;

DROP PROCEDURE IF EXISTS NC_StarsystemCreateSpecial;;
CREATE PROCEDURE NC_StarsystemCreateSpecial(
    p_X SMALLINT,
    p_Y SMALLINT,
    p_MaxPlanets TINYINT UNSIGNED,
    p_OpenTime INTEGER UNSIGNED,
    p_Name VARCHAR(40),
    p_Type TINYINT UNSIGNED
    )
  LANGUAGE SQL
  MODIFIES SQL DATA
  SQL SECURITY INVOKER
BEGIN
  INSERT INTO NC_Starsystem (X, Y, Name, MaxPlanets, OpenTime, StarType)
    VALUES (p_X, p_Y, p_Name, p_OpenTime, p_MaxPlanets, p_Type);
  SELECT LAST_INSERT_ID() AS Result;
END;;

DROP PROCEDURE IF EXISTS NC_StarsystemFindEmpty;;
CREATE PROCEDURE NC_StarsystemFindEmpty(
    p_time INTEGER UNSIGNED
    )
  LANGUAGE SQL
  MODIFIES SQL DATA
  SQL SECURITY INVOKER
BEGIN
  SELECT S.SID AS SID, S.MaxPlanets AS `Max`
    FROM NC_Starsystem AS S
    NATURAL LEFT JOIN NC_Planet AS P
    WHERE OpenTime<=p_time
    AND StarType=0 -- STAR_NORMAL
    GROUP BY S.SID
    HAVING COUNT(P.PLID)<S.MaxPlanets
    ORDER BY RAND()
    LIMIT 1 FOR UPDATE;
END;;

DROP PROCEDURE IF EXISTS NC_StarsystemSize;;
CREATE PROCEDURE NC_StarsystemSize(
    p_SID INTEGER UNSIGNED
    )
  LANGUAGE SQL
  READS SQL DATA
  SQL SECURITY INVOKER
BEGIN
  SELECT COUNT(*) AS Result
    FROM NC_Planet
    WHERE SID=p_SID;
END;;


DROP PROCEDURE IF EXISTS NC_StarsystemGet;;
CREATE PROCEDURE NC_StarsystemGet(
    p_SID INTEGER UNSIGNED
    )
  LANGUAGE SQL
  READS SQL DATA
  SQL SECURITY INVOKER
BEGIN
  SELECT X, Y, Name, Level
    FROM NC_Starsystem
    WHERE SID=p_SID;
END;;


