<?php
class PrinterController extends CommonController
{

    public function actionIndex(){
        $this->actionAll();
    }

    public function actionAll()
    {
        $this->pageTitle=t("Printer List");
		$action_name='printers_list';
		$delete_link = Yii::app()->CreateUrl(Yii::app()->controller->id."/delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
		
		if(Yii::app()->params['isMobile']==TRUE){
			$tpl = '//tpl/lazy_list';
		} else $tpl = '//tpl/list';
		
		$this->render($tpl,array(
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/create"),		  
		));	
    }

    public function actioncreate($update=false)
    {
        $this->pageTitle = $update==false? t("Add Printer") : t("Update Printer");
		CommonUtility::setMenuActive('.printers',".printer_all");
        $merchant_id = 0;
        if($update){
            $id = (integer) Yii::app()->input->get('id');	            
			$model = AR_printer::model()->findByPk( $id );						
            if(!$model){				
				$this->render("error");				
				Yii::app()->end();
			}	                        

            $meta = AR_printer_meta::getMeta($model->printer_id,['printer_user','printer_ukey','printer_sn','printer_key']);                                
            $model->printer_user = isset($meta['printer_user'])?$meta['printer_user']['meta_value1']:'';
            $model->printer_ukey = isset($meta['printer_ukey'])?$meta['printer_ukey']['meta_value1']:'';
            $model->printer_sn = isset($meta['printer_sn'])?$meta['printer_sn']['meta_value1']:'';
            $model->printer_key = isset($meta['printer_key'])?$meta['printer_key']['meta_value1']:'';            
        } else {
            $model=new AR_printer;            
        }
        if(isset($_POST['AR_printer'])){                        
            $model->attributes=$_POST['AR_printer'];
            $model->merchant_id = $merchant_id;            

            if($model->printer_model=="feieyun"){
                $model->paper_width = "80"; 
                if($update){
                    $model->scenario = "feieyun_update";
                } else  $model->scenario = "feieyun_add";
            } else {
                if($update){
                    $model->scenario = "wifi_update";
                } else  $model->scenario = "wifi_add";
            }
                        
            if($model->validate()){            
                if($model->save()){
					if(!$update){
					   $this->redirect(array(Yii::app()->controller->id.'/all'));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
            } else {                
                Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString($model->getErrors(),"<br/>") );
            }
        }

        $this->render("//printers/printer_add",array(
		    'model'=>$model,            
            'printer_type_list'=>CommonUtility::printerType(),
            'printer_paperwidth_list'=>CommonUtility::printerPaperList(),
            'printing_type_list'=>CommonUtility::printingTypeList(),
            'printing_character_list'=>CommonUtility::printingCharacterCodeList(),
		    'links'=>array(	
		      t("All printers")=>array(Yii::app()->controller->id.'/all'),		        
              $this->pageTitle
		    ),
		));
    }

    public function actionupdate()
    {
        $this->actioncreate(true);
    }

    public function actiondelete()
    {
        $printer_id = (integer) Yii::app()->input->get('id');	
		$merchant_id = 0;
				
		$model = AR_printer::model()->find('merchant_id=:merchant_id AND printer_id=:printer_id', 
		array(':merchant_id'=>$merchant_id, ':printer_id'=>$printer_id ));
		
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/all'));			
		} else $this->render("//tpl/error");
    }

    public function actionlogs()
    {
        $this->pageTitle=t("Printer logs");
		
        $action_name='print_logs';
		$delete_link = Yii::app()->CreateUrl(Yii::app()->controller->id."/print_delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),$action_name);

		$table_col = array(            
            'id'=>array(
              'label'=>t("ID"),
              'width'=>'8%'
            ),
			'order_id'=>array(
				'label'=>t("Order#"),
				'width'=>'15%'
			),
            'printer_number'=>array(
              'label'=>t("Printer"),
              'width'=>'15%'
            ),			
			'job_id'=>array(
              'label'=>t("Job Id"),
              'width'=>'15%'
            ),
            'status'=>array(
              'label'=>t("Status"),
              'width'=>'15%'
            ),            			
			'date_created'=>array(
				'label'=>t("Date"),
				'width'=>'10%'
			),            
            'ip_address'=>array(
                'label'=>t("Action"),
                'width'=>'10%'
              ),
          );
          $columns = array(            
            array('data'=>'id'),
			array('data'=>'order_id'),
            array('data'=>'printer_number'),			
			array('data'=>'job_id'),
            array('data'=>'status'),            			
			array('data'=>'date_created'),            
            array('data'=>'ip_address','orderable'=>false),
        );		
		
		$this->render("//print/print_logs",[
			'ajax_url'=>Yii::app()->createUrl("/api"),
			'actions'=>"printLogs",
			'table_col'=>$table_col,
            'columns'=>$columns,
            'order_col'=>0,
            'sortby'=>'desc',
			'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/create_reservation"),			
            'clear_logs_url'=>Yii::app()->createUrl(Yii::app()->controller->id."/clear_printlogs")
		]);				
    }

    public function actionclear_printlogs()
	{
		$stmt="DELETE FROM {{printer_logs}}
		WHERE date_created < now() - interval 30 DAY
		";        
		Yii::app()->db->createCommand($stmt)->query();
		$this->redirect(array(Yii::app()->controller->id.'/logs'));	
	}
    
    public function actionprint_delete()
    {
        $id = intval(Yii::app()->input->get('id'));			
				
		$model = AR_printer_logs::model()->find('id=:id', 
		array(':id'=>$id));
		
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/logs'));			
		} else $this->render("//tpl/error");
    }

    public function actionprint_view()
    {
        $this->pageTitle = t("Print preview");
		CommonUtility::setMenuActive('.printers',".printer_logs");

        $id = intval(Yii::app()->input->get('id'));			
				
		$model = AR_printer_logs::model()->find('id=:id', 
		array(':id'=>$id));
		
		if($model){
			$this->render("//print/print_preview",[
                'print_content'=>$model->print_content,
                'links'=>array(	
                    t("Print logs")=>array(Yii::app()->controller->id.'/logs'),		        
                    $this->pageTitle
                ),
            ]);
		} else $this->render("//tpl/error");
    }

}
// end class