ALTER TABLE `locational_clearances`
ADD COLUMN `evaluation_data` TEXT(1000) NULL DEFAULT NULL AFTER `building_area`;
