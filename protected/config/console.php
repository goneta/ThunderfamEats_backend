<?php
define('IS_FRONTEND',true);
$backend = dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.BACKOFFICE_FOLDER.DIRECTORY_SEPARATOR."protected";
$backend_webroot = dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.BACKOFFICE_FOLDER.DIRECTORY_SEPARATOR;
$upload_dir = dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'upload';
$home_dir = dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR;
Yii::setPathOfAlias('backend',$backend);
Yii::setPathOfAlias('backend_webroot',$backend_webroot);

$modules_dir = dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'protected/modules';

require_once $backend."/components/RedisHelper.php";

return array(
    //'basePath' => dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'name' => 'My Console Application',

    'aliases' => array(
       'upload_dir' => $upload_dir,   
       'modules_dir'=> $modules_dir,
       'home_dir' => $home_dir,
    ),
    
   'import'=>array(
		'application.models.*',
		'application.components.*',		
		'application.components.jobs.*', 
		'application.controllers.*',		
		'application.vendor.*',
		'application.extensions.*',
		'application.extensions.EHttpClient.*',
		'backend.components.*',		
		'backend.vendor.*',
		'backend.models.*',
		'ext.YiiMailer.YiiMailer'
	),

    'modules'=>array(
        'cod'=>array(),
        'ocr'=>array(),
        'paypal'=>array(),
        'stripe'=>array(),
        'razorpay'=>array(),
        'mercadopago'=>array(),
		'bank'=>array(),
		'jad'=>array(),
		'voguepay'=>array(),
		'paymongo'=>array(),
		'flutterwave'=>array(),
		'paystack'=>array(),
		'scanpay'=>array(),
		'billplz'=>array(),
		'toyyibpay'=>array(),
		'sofort'=>array(),
		'sofort_ideal'=>array(),
		'payhere'=>array(),
		'iyzipay'=>array(),
		'qmoney'=>array(),
		'braintree'=>array(),
		'payumoney'=>array(),
		'stripe_connect'=>array(),
		'everypay'=>array(),
		'clover'=>array(),
		'viva'=>array(),
		'revolut'=>array(),
		'paytabs'=>array(),
		'kotak'=>array(),
		'vivawallet'=>array(),
		'tap'=>array(),
		'knet'=>array(),
		'stripehosted'=>array(),
		'paymongo'=>array(),
		'dpo'=>array(),
		'paydelivery'=>array(),
		'myfatoorah'=>array(),
		'digital_wallet'=>array(),
		'paygreen'=>array(),
		'cmi'=>array(),
		'redsys'=>array(),
		'bizum'=>array(),
		'dojo'=>array(),
		'epayco'=>array(),
		'klarna'=>array(),
		'swish'=>array(),
		'mtn'=>array(),
		'mbway'=>array(),
		'sibscard'=>array(),
		'squareup'=>array(),
     ),

    'commandMap' => array(
        'processjobs' => array(
            'class' => 'application.commands.ProcessJobsCommand',
        ),
        // Add other custom commands here
    ),

    'language'=>KMRS_DEFAULT_LANGUAGE,
    

    'timezone'=>"Asia/Manila",
    

    'preload'=>array('log'),

    'components' => array(
        
        'request' => array(
            'hostInfo' => 'http://yourdomain.com',
            'baseUrl' => '',
            'scriptUrl' => '',
        ),

        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'error, warning, info', // Adjust as needed
                    'logFile' => 'application.log', // Specify log file name
                ),
                // Other log routes if configured
            ),
        ),

         // use language file in database
	    'messages'=>array(
            'class'=>'CDbMessageSource',
            'cacheID'=>'cache',
            'cachingDuration'=>1,
            'sourceMessageTable'=>'{{sourcemessage}}',
            'translatedMessageTable'=>'{{message}}'
        ),                  
        'db'=>array(
			'connectionString' => 'mysql:host='.DB_HOST.';dbname='.DB_NAME,
			'emulatePrepare' => true,
			'username' => DB_USER,
			'password' => DB_PASSWORD,
			'charset' => DB_CHARSET,
			'tablePrefix' => DB_PREFIX,
			'schemaCachingDuration'=>60,
			'initSQLs' => array(
                "SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))",
				//"SET SESSION group_concat_max_len=4294967295"
            ),
		),		
        'input'=>array(
		   'class'=>'CmsInput',
		   'cleanPost'=>true,
		   'cleanGet'=>true
		),
    ),
    'params'=>require(dirname(__FILE__).'/params.php'),
);