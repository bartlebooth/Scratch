CREATE TABLE `users` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `firstName` VARCHAR(63) NOT NULL,
    `lastName` VARCHAR(63) NOT NULL,
    `username` VARCHAR(63) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NULL,
    `platformMaskId` INT NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX (`username`)
) ENGINE=InnoDB;

CREATE TABLE `groups` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(63) NOT NULL,
    `type` ENUM('Platform', 'Workspace') NOT NULL,
    `platform_mask_id` INT NOT NULL, /* REF */
    /* workspace_id INT NULL REF */
    PRIMARY KEY (`id`),
    UNIQUE INDEX (`username`)
) ENGINE=InnoDB;

CREATE TABLE `masks` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `mask` INT UNSIGNED NOT NULL,
    `target` ENUM('Platform', 'Workspace', 'Resource', 'Group') NOT NULL,
    `translation_key` VARCHAR(255) NOT NULL,
    /* tgt_workspace_id INT NULL REF */
    /* tgt_resource_id INT NULL REF */
    /* tgt_group_id INT NULL REF */
    PRIMARY KEY (`id`),
    UNIQUE INDEX (`mask`, `target`, `translation_key`)
) ENGINE=InnoDB;