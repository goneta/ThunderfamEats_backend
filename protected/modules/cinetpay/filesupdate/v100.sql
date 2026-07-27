-- CinetPay ("Mobile Money") payment gateway — installation SQL.
-- Run once against the deployment database (table prefix: adjust st_ to DB_PREFIX).
-- Credentials are intentionally EMPTY: set API Key / Site ID / Secret Key from
-- the back-office (Payment Gateway > Mobile Money > update) — never in git.

INSERT INTO `st_addons` (`addon_name`, `uuid`, `version`, `activated`, `image`, `path`, `purchase_code`, `date_created`, `date_modified`, `ip_address`) VALUES
('CinetPay', 'cinetpay-mobile-money-v100', '1.0.0', 1, '', 'upload/all', '', now(), now(), '127.0.0.1');

INSERT INTO `st_payment_gateway`
(`payment_name`, `payment_code`, `is_online`, `is_payout`, `is_plan`, `logo_type`, `logo_class`, `logo_image`, `path`, `status`, `sequence`, `is_live`, `attr_json`, `attr_json1`, `attr1`, `attr2`, `attr3`, `attr4`, `attr5`, `attr6`, `attr7`, `attr8`, `attr9`, `split`, `capture`, `date_created`, `date_modified`, `ip_address`) VALUES
('Mobile Money', 'cinetpay', 1, 0, 0, 'icon', 'zmdi zmdi-smartphone', '', '', 'inactive', 0, 0,
'{\n	"attr1": {\n		"label": "API Key"\n	},\n	"attr2": {\n		"label": "Site ID"\n	},\n	"attr3": {\n		"label": "Secret Key (notify HMAC)"\n	},\n	"attr5": {\n		"label": "Channels (MOBILE_MONEY or ALL)"\n	},\n	"attr6": {\n		"label": "Currency override (optional, e.g. XOF)"\n	}\n}',
NULL, '', '', '',
'{\n	"notify_url": "{site_url}/cinetpay/api/notify",\n	"return_url": "{site_url}/cinetpay/api/verifypayment"\n}',
'MOBILE_MONEY', '', '', '', NULL, 0, 0, now(), now(), '127.0.0.1');
COMMIT;
