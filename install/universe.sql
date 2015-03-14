DELIMITER ;;

USE cygnus_rootnode;;

DROP PROCEDURE IF EXISTS NC_UniverseReset;;
CREATE PROCEDURE NC_UniverseReset(
  )
  LANGUAGE SQL
  MODIFIES SQL DATA
  SQL SECURITY INVOKER
BEGIN
  DELETE FROM NC_Player;
  DELETE FROM NC_Starsystem;
  UPDATE NC_Config_StarsystemNames SET RingLevel=0;
  UPDATE NC_Config SEt RingLevel=0;
END;;

