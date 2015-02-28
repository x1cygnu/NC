DELIMITER ;;

USE cygnus_rootnode;;

DROP PROCEDURE IF EXISTS NC_AccountCreate;;
CREATE PROCEDURE NC_AccountCreate(
    loginName CHAR(30),
    publicName CHAR(30),
    password VARCHAR(100))
  LANGUAGE SQL
  MODIFIES SQL DATA
  SQL SECURITY INVOKER
BEGIN
  INSERT INTO NC_Account VALUES(DEFAULT, NULL, loginName, publicName, UNHEX(SHA2(password,256)));
  SELECT LAST_INSERT_ID() AS `Result`;
END;;

DROP PROCEDURE IF EXISTS NC_AccountLogin;;
CREATE PROCEDURE NC_AccountLogin(
    p_loginName CHAR(30),
    p_password VARCHAR(100))
  LANGUAGE SQL
  READS SQL DATA
  SQL SECURITY INVOKER
BEGIN
  SELECT AID, PID, LoginName, PublicName FROM NC_Account WHERE LoginName = p_loginName AND password = UNHEX(SHA2(p_password,256));
END;;

