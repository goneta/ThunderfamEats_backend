<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/CposImage.php';
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\EscposImage;

class CposThermalPrinter  extends CposImage
{

    public static $printer;
    public static $data;
    public static $character_code;
    public static $print_type;
    public static $page_width;
    public static $page_height;
    public static $font_size;
    public static $line_height;    
    public static $font_path;    
    public static $printer_ip;
    public static $port;
    public static $is_debug;

    public static function setDebug($data=false)
    {
        self::$is_debug = $data;
    }

    public static function getDebug($data=false)
    {
        return self::$is_debug;
    }

    public static function setSettings($settings=[])
    {
                          
         self::$printer_ip = isset($settings['ip'])?$settings['ip']:'';
         self::$port = isset($settings['port'])?$settings['port']:'';
         self::$print_type = isset($settings['print_type'])?$settings['print_type']:'';         
         self::$character_code = isset($settings['character_code'])? trim($settings['character_code']) :'en';         
         self::$page_width = isset($settings['page_width'])?$settings['page_width']:'';         
         self::$font_size = isset($settings['font_size'])?$settings['font_size']:'';         
         self::$line_height = isset($settings['line_height'])?$settings['line_height']:'';         
         
         self::setFont(self::$character_code);                
    }

    private static function setFont($character_code='',$font_type='regular')
    {         
         switch ($character_code) {
            case 'jp':
            case "kr":
                if($font_type=="bold") {
                    self::$font_path = __DIR__ . '/fonts/NotoSerifKR-Bold.ttf';
                } else {
                    self::$font_path = __DIR__ . '/fonts/NotoSerifKR-Regular.ttf';
                }                
                break;
            
            case "ar":        
                if($font_type=="bold") {
                    self::$font_path = __DIR__ . '/fonts/NotoNaskhArabic-Bold.ttf';     
                } else {
                    self::$font_path = __DIR__ . '/fonts/NotoNaskhArabic-Regular.ttf';     
                }                
                break;

            default:                
                if($font_type=="bold") {                    
                    self::$font_path = __DIR__ . '/fonts/OpenSans-Bold.ttf';
                } else {                    
                    self::$font_path = __DIR__ . '/fonts/OpenSans-Regular.ttf';
                }                
                break;
         }
    }

    public static function Print($data = [])
    {
        self::$data = $data;                
        if(self::$print_type=="image"){
            return CposThermalPrinter::PrintImage();
        } else if (self::$print_type=="raw") {
            return CposThermalPrinter::PrintRaw();
        }
    }
    
    public static function PrintImage()
    {
        $job_id = isset(self::$data['job_id'])?self::$data['job_id']:'';
        $header = isset(self::$data['header'])?self::$data['header']:'';
        $body = isset(self::$data['body'])?self::$data['body']:'';        
        $footer = isset(self::$data['footer'])?self::$data['footer']:'';
        
        $total_items = count($header) + count($body)  + count($footer);
        $total_items = $total_items + 3;        
        self::$page_height = $total_items*self::$line_height;

        $preparedText = '';        
        
        $preparedText .=self::prepareText($header);
        $preparedText .=self::prepareText($body);
        $preparedText .=self::prepareText($footer);
      
        
        $y = 20;        
        $image = imagecreatetruecolor(self::$page_width, self::$page_height);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefilledrectangle($image, 0, 0, self::$page_width, self::$page_height, $white);        

        foreach (explode("\n", $preparedText) as $line) {
            if (strpos($line, "CENTERED_TEXT: ") === 0) {
                $text = substr($line, strlen("CENTERED_TEXT: "));
                $y = parent::CenterText($image, self::$font_size, self::$font_path, $text, self::$page_width, $y, $black, self::$line_height);
            } elseif (strpos($line, "LEFT_ALIGNED_TEXT:") === 0) {
                $text = substr($line, strlen("LEFT_ALIGNED_TEXT: "));
                $y = parent::LeftAlignedText($image, self::$font_size, self::$font_path, $text, self::$page_width, $y, $black, self::$line_height);            
            } elseif (strpos($line, "COLUMN_TEXT: ") === 0) {
                $text = substr($line, strlen("COLUMN_TEXT: "));
                list($leftText, $rightText) = explode("\t", $text);                                                
                $y = parent::LeftRightText($image, self::$font_size, self::$font_path, $leftText, $rightText, $y, $black, self::$line_height, self::$page_width);
            } elseif (trim($line) === "DOTTED_LINE") {                
                $y = parent::addDottedLineOnImage($image, $y, self::$page_width, $black, self::$line_height);
            } elseif (trim($line) === "LINE_BREAK") {
                $y += 12;
            } elseif (trim($line) === "LINE_BREAK_BIG") {
                $y += self::$line_height*2;
            } elseif (strpos($line, "SET_FONT: ") === 0) {
                $text = substr($line, strlen("SET_FONT: "));                
                self::setFont(self::$character_code,$text);
            }
        }
                
        // Save the image to a file
        $path_image = __DIR__ ."/jobs";        
        if(!file_exists($path_image)){                    
            if (!mkdir($path_image, 0777, true)) {
                throw new Exception("Failed to create directory: $path_image");
            }
        }
        
        $filename = $job_id.'.png';
        $imageFile = $path_image."/$filename";        
        imagepng($image, $imageFile);

        // Destroy the image to free up memory
        imagedestroy($image);

        $debug = self::getDebug();
        if($debug){            
            return true;
        }

        try {
                        
            $connector = new NetworkPrintConnector(self::$printer_ip,self::$port);
            $printer = new Printer($connector);

            $tux = EscposImage::load($imageFile, false);

            $printer -> bitImage($tux);    
            $printer -> feed();
            $printer->cut();
            $printer->close();
                        
            if(file_exists($imageFile)){
                unlink($imageFile);
            } 
            return true;

        } catch (Exception $e) {
            echo "Couldn't print to this printer: " . $e->getMessage() . "\n";
        }
    }

    public static function prepareText($data=[])
    {
        $preparedText = '';
        if(is_array($data) && count($data)>=1){
            foreach ($data as $items) { 
                $position = isset($items['position'])?$items['position']:'';
                $type = isset($items['type'])?$items['type']:'text';
                if($position=="left"){
                    if($type=="dotted_line"){
                        $preparedText.= parent::addDottedLine();
                    } else if ( $type=="line_break" ){
                        $preparedText .= parent::AddLineBreak();
                    } else if ( $type=="text" ){
                        $preparedText .= parent::prepareLeftText( isset($items['value'])?$items['value']:'' );
                    } else if ( $type=="font" ){                                        
                        $preparedText .=  parent::prepareFont( isset($items['font_type'])?$items['font_type']:'' );
                    } else if ( $type=="line_break_big" ){
                        $preparedText .= parent::AddLineBreakBig();
                    }
                } else if ($position=="left_right_text"){                    
                    $label = isset($items['label'])?$items['label']:'';
                    $value = isset($items['value'])?$items['value']:'';
                    $preparedText.= parent::prepareColumnText(self::$font_size, self::$font_path, $label, $value);                    
                } else if ($position=="center"){
                    if($type=="text"){
                        $preparedText .= parent::prepareCenterText( isset($items['value'])?$items['value']:'' );
                    }                    
                }
            }
        }
        return $preparedText;
    }

    public static function PrintRaw()
    {
        try {
            
            $debug = self::getDebug();
            $job_id = isset(self::$data['job_id'])?self::$data['job_id']:'';
            $header = isset(self::$data['header'])?self::$data['header']:'';
            $body = isset(self::$data['body'])?self::$data['body']:'';        
            $footer = isset(self::$data['footer'])?self::$data['footer']:'';
            
            if($debug){
                $connector = new FilePrintConnector("file.bin");
                self::$printer = new Printer($connector);
            } else {
                $connector = new NetworkPrintConnector(self::$printer_ip,self::$port);
                self::$printer = new Printer($connector);
            }                        
           
            self::prepareRawPrint($header);
            self::prepareRawPrint($body);
            self::prepareRawPrint($footer);
            
            self::$printer->cut();
            self::$printer->close();

            return true;

        } catch (Exception $e) {
            echo "Couldn't print to this printer: " . $e->getMessage() . "\n";
        }
    }

    public static function prepareRawPrint($data=[])
    {
        
        if(is_array($data) && count($data)>=1){
            foreach ($data as $items) {                     
                $position = isset($items['position'])?$items['position']:'';
                $type = isset($items['type'])?$items['type']:'text';
                if($position=="left"){
                    self::$printer->setJustification(Printer::JUSTIFY_LEFT);
                    if($type=="font"){
                        $font_type = isset($items['font_type'])?$items['font_type']:false;
                        $is_bold = $font_type=='bold'?true:false;                        
                        self::$printer->setEmphasis($is_bold);
                    } else if ($type=="line_break"){                        
                        self::$printer->text( "\n");
                    } else if ($type=="dotted_line"){                        
                        parent::printDottedLine(self::$printer,self::$page_width);
                    } else if ($type=="text"){ 
                        self::$printer->text( isset($items['value'])?$items['value']:'' );                        
                        self::$printer->text( "\n");
                    } else if ($type=="line_break_big"){                        
                        self::$printer->text( "\n\n");
                    }
                    self::$printer->setJustification();
                } else if ($position=="center"){
                    self::$printer->setJustification(Printer::JUSTIFY_CENTER);
                    if($type=="text"){                    
                        self::$printer->text( isset($items['value'])?$items['value']:'' );
                        self::$printer->selectPrintMode();                        
                        self::$printer->text( "\n");
                    }
                    self::$printer->setJustification();
                } else if ($position=="left_right_text"){
                    self::$printer->setJustification(Printer::JUSTIFY_LEFT);
                    $label = isset($items['label'])?$items['label']:'';
                    $value = isset($items['value'])?$items['value']:'';
                    parent::printTwoColumns(self::$printer,$label,$value,self::$page_width);
                    self::$printer->setJustification();
                }
            }
        }
    }

}
// end class