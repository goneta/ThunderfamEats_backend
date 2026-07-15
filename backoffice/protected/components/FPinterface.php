<?php
header("Content-type: text/html; charset=utf-8");
include 'HttpClient.class.php';
define('IP','api.feieyun.cn');
define('PORT',80);
define('PATH','/Api/Open/');

/*
*  INTERFACE FOR feieyun PRINTER
*/
class FPinterface
{ 

    public static function queryPrinter($user='',$stime='',$sig='',$sn='')
    {
        $content = array(			
            'user'=>$user,
            'stime'=>$stime,
            'sig'=>$sig,
            'apiname'=>'Open_queryPrinterStatus',	
            'sn'=>$sn
       );       
       $client = new HttpClient(IP,PORT);
       if(!$client->post(PATH,$content)){
           $error = $client->getError();
           $error = !empty($error)? json_decode($error,true):"Error no response";           
           throw new Exception( isset($error['msg'])?$error['msg']:'Error response'  );
       } else {
          $result = $client->getContent();
          $result = !empty($result)? json_decode($result,true):"Error no response";                            
          $data = isset($result['data'])?$result['data']:'';                    
          $ret = isset($result['ret'])?$result['ret']:'';          
          $msg = isset($result['msg'])?$result['msg']:'Error no response';          
          if($ret==0){
            return $data;
          } else throw new Exception( $msg );
       }       
    }

    public static function addPrinter($user='', $stime='', $sig='', $snlist='' , $sn='')
    {
                        
        try {
            self::deletePrinter($user,$stime,$sig,$sn);
        } catch (Exception $e) {
            //
        }
        
        $content = array(			
            'user'=>$user,
            'stime'=>$stime,
            'sig'=>$sig,
            'apiname'=>'Open_printerAddlist',	
            'printerContent'=>$snlist
       );       
       $client = new HttpClient(IP,PORT);
       if(!$client->post(PATH,$content)){
           $error = $client->getError();
           $error = !empty($error)? json_decode($error,true):"Error no response";           
           throw new Exception( isset($error['msg'])?$error['msg']:'Error response'  );
       } else {        
          $result = $client->getContent();
          $result = !empty($result)? json_decode($result,true):"Error no response";                      
          if(is_array($result) && count($result)>=1){
              $ok = isset($result['data'])?$result['data']['ok']:'';
              $no = isset($result['data'])?$result['data']['no']:'';
              $ret = isset($result['ret'])?$result['ret']:'';          
              $msg = isset($result['msg'])?$result['msg']:'Error no response';                      
              if($ret==0){
                  if(count((array)$ok)>0){
                      return true;
                  } else {
                      throw new Exception( isset($no[0])?$no[0]:'Failed adding printer' );
                  }
              } else throw new Exception( $msg );
          } else throw new Exception( $result );
       }
    }

    public static function updatePrinter($user='',$stime='',$sig='',$sn='',$name='',$phonenum='')
    {
        $content = array(			
            'user'=>$user,
            'stime'=>$stime,
            'sig'=>$sig,
            'apiname'=>'Open_printerEdit',	
            'sn'=>$sn,
            'name'=>$name,
            'phonenum'=>$phonenum
       );              
       $client = new HttpClient(IP,PORT);
       if(!$client->post(PATH,$content)){
           $error = $client->getError();
           $error = !empty($error)? json_decode($error,true):"Error no response";           
           throw new Exception( isset($error['msg'])?$error['msg']:'Error response'  );
       } else {
          $result = $client->getContent();
          $result = !empty($result)? json_decode($result,true):"Error no response";                    
          $data = isset($result['data'])?$result['data']:'';          
          $ret = isset($result['ret'])?$result['ret']:'';          
          $msg = isset($result['msg'])?$result['msg']:'Error no response';          
          if($ret==0){
            return $data;
          } else throw new Exception( $msg );
       }
    }

    public static function deletePrinter($user='', $stime='', $sig='', $snlist='')
    {        
        $content = array(			
            'user'=>$user,
            'stime'=>$stime,
            'sig'=>$sig,
            'apiname'=>'Open_printerDelList',	
            'snlist'=>$snlist
       );       
       $client = new HttpClient(IP,PORT);
       if(!$client->post(PATH,$content)){
            $error = $client->getError();
            $error = !empty($error)? json_decode($error,true):"Error no response";           
            throw new Exception( isset($error['msg'])?$error['msg']:'Error response'  );
        } else {
            $result = $client->getContent();
            $result = !empty($result)? json_decode($result,true):"Error no response";                      
            if(is_array($result) && count($result)>=1){
                $ok = isset($result['data'])?$result['data']['ok']:'';
                $no = isset($result['data'])?$result['data']['no']:'';
                $ret = isset($result['ret'])?$result['ret']:'';          
                $msg = isset($result['msg'])?$result['msg']:'Error no response';                      
                if($ret==0){
                    if(count((array)$ok)>0){
                        return true;
                    } else {
                        throw new Exception( isset($no[0])?$no[0]:'Failed deleting printer' );
                    }
                } else throw new Exception( $msg );
            } else throw new Exception( $result );
        }       
    }

    public static function TestTemplate($paper_width='')
    {
        $paperWidth = self::getPaperLenght($paper_width);
        $content = '';
        $content.='<CB>TEST RECEIPT</CB><BR>';
        $content.= self::getLine($paperWidth);
        $content.= "<BR>";

        $content.= self::leftRightData("1 x Cheese burger","3.00",$paperWidth);
        $content.= self::leftRightData("5 x Sauce","100.00",$paperWidth);

        $content.= self::getLine($paperWidth);
        $content.= "<BR>";

        $content.= self::leftRightData("TOTAL AMOUNT","126.00",$paperWidth);
        $content.= self::leftRightData("CASH","200.00",$paperWidth);
        $content.= self::leftRightData("CHANGE","74.00",$paperWidth);
        $content.= "<BR><BR>";

        $content.= self::leftRightData("BANK CARD","****7212",$paperWidth);
        $content.= self::getLine($paperWidth);
        $content.= "<BR>";

        $content.= self::centerData("THANK YOU!",$paperWidth);
        $content.= "<BR>";
        $content.= "<BR>";
        $content.= "<CUT>";
        
        return $content;
    }

    public static function centerData($string='', $paperWidth='')
    {
        $totalPad = $paperWidth - strlen($string);
        $totalPad = $totalPad / 2;
        $RowItems = "";
        $RowItems .= str_pad("",intval($totalPad),"*",STR_PAD_LEFT);
        $RowItems .= $string;
        $RowItems .= str_pad("",intval($totalPad),"*",STR_PAD_RIGHT);
        return $RowItems;
    }

    public static function leftRightData($string1='', $string2='', $paperWidth='')
    {
        $string1 = empty($string1)?"":$string1;
        $string2 = empty($string2)?"":$string2;
        $totalPad = $paperWidth - (  strlen($string1) + strlen($string2));
        $totalPad = $totalPad + strlen($string1);

        $RowItems = "";
        if (strlen($string1) > $paperWidth) {
            $RowItems = $string1;
            $RowItems.= "<BR>";
            $RowItems.= $string2;
        } else {
            $RowItems = str_pad($string1,$totalPad," ",STR_PAD_RIGHT);
            $RowItems.= $string2;
        }
        $RowItems .= "<BR>";
        return $RowItems;
    }

    public static function getLine($paperWidth) {
        $Line = "-";                
        return str_pad($Line,$paperWidth,$Line);
    }

    public static function getPaperLenght($paperWidth='')
    {
        if ($paperWidth == "58") {
            return 32;
        } else return 45;
    }

    public static function Print($user='', $stime='', $sig='', $sn='', $content='', $times=1)
    {
        $content = array(			
            'user'=>$user,
            'stime'=>$stime,
            'sig'=>$sig,
            'apiname'=>'Open_printMsg',	
            'sn'=>$sn,
            'content'=>$content,
            'times'=>$times,
       );            
       $client = new HttpClient(IP,PORT);
       if(!$client->post(PATH,$content)){
           $error = $client->getError();
           $error = !empty($error)? json_decode($error,true):"Error no response";           
           throw new Exception( isset($error['msg'])?$error['msg']:'Error response'  );
       } else {
          $result = $client->getContent();
          $result = !empty($result)? json_decode($result,true):"Error no response";          
          $data = isset($result['data'])?$result['data']:'';          
          $ret = isset($result['ret'])?$result['ret']:'';          
          $msg = isset($result['msg'])?$result['msg']:'Error no response';          
          if($ret==0){
            return $data;
          } else throw new Exception( $msg );
       }
    }

    public static function getPrinterList($merchant_id=0,$printer_model='feieyun')
    {
        $model = AR_printer::model()->findAll("merchant_id=:merchant_id AND printer_model=:printer_model",[
            ':merchant_id'=>$merchant_id,
            ':printer_model'=>$printer_model
        ]);
        if($model){
            $data = [];
            foreach ($model as $items) {
                $data[] = [
                    'printer_id'=>$items->printer_id,
                    'printer_name'=>$items->printer_name,
                    'printer_model'=>$items->printer_model
                ];
            }
            return $data;
        } else throw new Exception( HELPER_NO_RESULTS);
    }

    public static function getPrinterByMerchant($merchant_id=0,$printer_model=['wifi','feieyun'],$platform='merchant')
    {        
        $criteria=new CDbCriteria();        
        $criteria->condition = "merchant_id=:merchant_id AND platform=:platform";
        $criteria->params = [
            ':merchant_id'=>$merchant_id,
            ':platform'=>$platform
        ];
        $criteria->addInCondition("printer_model",$printer_model);            
        if($model = AR_printer::model()->findAll($criteria)){		            
            $data = [];
            foreach ($model as $items) {
                $data[] = [
                    'printer_id'=>$items->printer_id,
                    'printer_name'=>$items->printer_name,
                    'printer_model'=>$items->printer_model
                ];
            }
            return $data;
        } else throw new Exception( HELPER_NO_RESULTS);
    }

    public static function getPrinterAll($merchant_ids=[],$printer_model=[])
    {        
        $criteria=new CDbCriteria();        
        $criteria->addInCondition("merchant_id",$merchant_ids);        
        $criteria->addInCondition("printer_model",$printer_model);        
        if($model = AR_printer::model()->findAll($criteria)){
            $data = [];
            foreach ($model as $items) {
                $data[] = [
                    'printer_id'=>$items->printer_id,
                    'printer_name'=>$items->printer_name,
                    'printer_model'=>$items->printer_model
                ];
            }
            return $data;
        } else throw new Exception( HELPER_NO_RESULTS);
    }

}
// end class