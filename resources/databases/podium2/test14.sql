DROP TABLE IF EXISTS `test14`;
CREATE TABLE `test14`
(
    `Id` INT(8) UNSIGNED AUTO_INCREMENT COMMENT '管理ID',
    `CreateDate` DATETIME COMMENT 'レコード登録日',
    `UpdateDate` DATETIME COMMENT 'レコード更新日',
    `DeleteDate` DATETIME COMMENT 'レコード無効日',
    `DeleteFlag` TINYINT(1) UNSIGNED DEFAULT 0 COMMENT 'レコード無効フラグ(0:有効, 1:無効)',
    PRIMARY KEY (`Id`)
) ENGINE=InnoDB COMMENT '情報14';
