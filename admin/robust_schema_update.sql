-- Run this script in phpMyAdmin or your MySQL client on the live database to add all missing columns safely.

-- Add duration and price fields if they somehow don't exist (most likely they do but just in case)
ALTER TABLE `tours` 
ADD COLUMN IF NOT EXISTS `duration_days` INT NOT NULL DEFAULT 1,
ADD COLUMN IF NOT EXISTS `price_from_usd` DECIMAL(10,2) NOT NULL DEFAULT 0.00;

-- Add toggles
ALTER TABLE `tours`
ADD COLUMN IF NOT EXISTS `is_hot_offer` TINYINT(1) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS `is_active_ad` TINYINT(1) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS `is_joining_tour` TINYINT(1) NOT NULL DEFAULT 0;

-- Add adult pricing tiers
ALTER TABLE `tours`
ADD COLUMN IF NOT EXISTS `price_1_pax` DECIMAL(10,2) NULL,
ADD COLUMN IF NOT EXISTS `price_2_pax` DECIMAL(10,2) NULL,
ADD COLUMN IF NOT EXISTS `price_3_pax` DECIMAL(10,2) NULL,
ADD COLUMN IF NOT EXISTS `price_4_pax` DECIMAL(10,2) NULL,
ADD COLUMN IF NOT EXISTS `price_5_pax` DECIMAL(10,2) NULL,
ADD COLUMN IF NOT EXISTS `price_6_pax` DECIMAL(10,2) NULL;

-- Add child pricing tiers
ALTER TABLE `tours`
ADD COLUMN IF NOT EXISTS `price_child_1_pax` DECIMAL(10,2) NULL,
ADD COLUMN IF NOT EXISTS `price_child_2_pax` DECIMAL(10,2) NULL,
ADD COLUMN IF NOT EXISTS `price_child_3_pax` DECIMAL(10,2) NULL,
ADD COLUMN IF NOT EXISTS `price_child_4_pax` DECIMAL(10,2) NULL,
ADD COLUMN IF NOT EXISTS `price_child_5_pax` DECIMAL(10,2) NULL,
ADD COLUMN IF NOT EXISTS `price_child_6_pax` DECIMAL(10,2) NULL;
