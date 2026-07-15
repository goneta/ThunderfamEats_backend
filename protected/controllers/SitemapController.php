<?php
class SitemapController extends CController
{
    public function actionIndex()
    {        
        $model = AR_cache::model()->find();
        $last_mod = $model?Date_Formatter::date($model->date_modified,"yyyy-MM-dd",true):date("Y-m-d");
        $urls = array(
            array('loc' => websiteUrl(), 'lastmod' => $last_mod, 'changefreq' => 'daily', 'priority' => '1.0'),
            array('loc' => Yii::app()->createAbsoluteUrl("/restaurants"), 'lastmod' => $last_mod, 'changefreq' => 'daily', 'priority' => '0.9'),            
            array('loc' => Yii::app()->createAbsoluteUrl("/search"), 'lastmod' => $last_mod, 'changefreq' => 'daily', 'priority' => '0.9'),            
            array('loc' =>Yii::app()->createAbsoluteUrl("/merchant/signup"), 'lastmod' => $last_mod, 'changefreq' => 'never', 'priority' => '0.9'), 
            array('loc' =>Yii::app()->createAbsoluteUrl("/account/login"), 'lastmod' => $last_mod, 'changefreq' => 'never', 'priority' => '0.6'), 
            array('loc' =>Yii::app()->createAbsoluteUrl("/account/signup"), 'lastmod' => $last_mod, 'changefreq' => 'never', 'priority' => '0.9'), 
            array('loc' =>Yii::app()->createAbsoluteUrl("/account/forgot_pass"), 'lastmod' => $last_mod, 'changefreq' => 'never', 'priority' => '0.1'), 
        );

        // CUISINE
        $model_cuisine = AR_cuisine::model()->findAll("status=:status AND slug<>''",[
            ':status'=>"publish"
        ]);
        if($model_cuisine){
            foreach ($model_cuisine as $items) {
                $urls[] = [
                    'loc'=>Yii::app()->createAbsoluteUrl("/cuisine/".$items->slug),
                    'lastmod'=>Date_Formatter::date($items->date_modified,"yyyy-MM-dd"),
                    'changefreq'=>"weekly",
                    'priority'=>"0.7"
                ];
            }
        }

        // CUSTOM PAGE
        $pages = AR_pages::model()->findAll("status=:status AND page_type=:page_type AND owner=:owner AND slug<>''",[
            ':status'=>'publish',
            ':page_type'=>"page",
            ':owner'=>"admin"
        ]);
        if($pages){
            foreach ($pages as $items) {
                $urls[] = [
                    'loc'=>Yii::app()->createAbsoluteUrl("/$items->slug"),
                    'lastmod'=>Date_Formatter::date($items->date_modified,"yyyy-MM-dd"),
                    'changefreq'=>"monthly",
                    'priority'=>"0.4"
                ];
            }
        }

        // MERCHANT
        $merchant = AR_merchant::model()->findAll("status=:status AND is_ready=:is_ready ",[
            ':status'=>"active",
            ':is_ready'=>2
        ]);
        if($merchant){
            foreach ($merchant as $items) {
                $urls[] = [
                    'loc'=>Yii::app()->createAbsoluteUrl("/$items->restaurant_slug"),
                    'lastmod'=>Date_Formatter::date($items->date_modified,"yyyy-MM-dd"),
                    'changefreq'=>"weekly",
                    'priority'=>"1.0"
                ];
            }
        }

        // dump($urls);
        // die();

        header('Content-type: text/xml');
        echo $this->renderPartial('index', array('urls' => $urls), true);
    }
}
// end class