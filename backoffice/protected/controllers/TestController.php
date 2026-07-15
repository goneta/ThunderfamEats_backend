<?php
require 'dompdf/vendor/autoload.php';
require 'ar-php/vendor/autoload.php';
require 'twig/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;
use ArPHP\I18N\Arabic;

class TestController extends CommonController
{
    public function actionIndex()
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml("
            <!DOCTYPE html>
            <html lang=\"en\" prefix=\"og: http://ogp.me/ns#\">
            <head>
                <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>
                <style>
                    @font-face {
                        font-family: 'Firefly';
                        font-weight: 400;
                        font-style: normal;
                        src: url(\"http://localhost/NotoSansCJKjp-hinted/NotoSansCJKjp-Regular.ttf\") format(\"truetype\");
                    }

                    body {
                        font-weight: 400;
                        font-family: 'Firefly';
                    }
                </style>
            </head>
            <body>
                <div>こんにちは</div>
            </body>
            </html>
            ");

            $dompdf->render();

            $dompdf->stream();
    }
}
// end class