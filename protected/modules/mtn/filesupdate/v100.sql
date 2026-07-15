
INSERT INTO `st_addons` (`id`, `addon_name`, `uuid`, `version`, `activated`, `image`, `path`, `purchase_code`, `date_created`, `date_modified`, `ip_address`) VALUES
(null, 'MTN Momo', 'Jn7e/KRWu/CFQUlK7rde9/8wCOwM1RT4j3+uv0qh2aRmcM6/qKbfeqFtUe73OKi4gWpVfW6ESrE=', '1.0.0', 1, 'mtn.png', 'upload/all', '', now(), now(), '127.0.0.1');

INSERT INTO `st_payment_gateway` ( `payment_name`, `payment_code`, `is_online`, `is_payout`, `is_plan`, `logo_type`, `logo_class`, `logo_image`, `path`, `status`, `sequence`, `is_live`, `attr_json`, `attr_json1`, `attr1`, `attr2`, `attr3`, `attr4`, `attr5`, `attr6`, `attr7`, `attr8`, `attr9`, `split`, `capture`, `date_created`, `date_modified`, `ip_address`) VALUES
('MTN Momo', 'mtn', 1, 0, 0, 'image', '', '', '', 'active', 0, 0, '{\n    \"attr1\": {\n        \"label\": \"Subsription key\"\n    }\n}\n', NULL, '7db6295dac334f218873e8bc82f90070', '', '', '', '', '', '', '', NULL, 0, 0, '2024-09-24 02:48:14', '2024-09-25 02:18:54', '127.0.0.1');
COMMIT;