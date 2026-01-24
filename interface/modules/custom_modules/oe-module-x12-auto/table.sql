CREATE TABLE IF NOT EXISTS `x12_file_process`(
    `id` int(11) PRIMARY KEY NOT NULL,
    `last_datetime_run` datetime default NULL,
    `last_datetime_file_modified` datetime default NULL,
    `payer_id` VARCHAR(50),
    `provider_id` VARCHAR(50), -- or sender_id in case of 271
    `st_control_number` VARCHAR(20)
);

-- making the assumption that file_names do not repeat
CREATE TABLE IF NOT EXISTS `x12_downloaded_files`(
    `x12_partner_id` INT(11) PRIMARY KEY NOT NULL, 
    `file_name` VARCHAR(100) PRIMARY KEY NOT NULL,
    `internal_file_id` INT(11) AUTO_INCREMENT, --used in next table as a key
    `file_process_status` VARCHAR(20),
    `retrieved_date_time` DATETIME,
    `processed_date_time` DATETIME,
    `x12_file_type` CHAR(5) DEFAULT "",
    `sub_set` INT(11) PRIMARY KEY NOT NULL, --in case of multiple ST types
    `error_count` INT(11) default 0
);

-- log error(s) that occur when processing a file
-- or bad data within a file
CREATE TABLE IF NOT EXISTS `x12_processed_files_error_log`(
    `internal_file_id` INT(11) PRIMARY KEY NOT NULL,
    `int_error_id` INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `error_line_number` INT(11),
    `error_message` VARCHAR(250)
);

