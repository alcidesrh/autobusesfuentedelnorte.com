<?php

namespace Acme\BackendBundle\Services;

use Hackzilla\BarcodeBundle\Utility\Barcode;
use Symfony\Component\HttpFoundation\Response;

class BarCodeService {
   
    protected $container;
    protected $options;
    
    public function __construct($container) { 
        $this->container = $container;
        $this->options = array(      
            "returnResponse" => false
        );
   }
   
   public function barcodeToTextAction($code, $options = null){
       
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $barcode = new Barcode();
        $barcode->setEncoding(Barcode::encoding_128);
        $headers = array(
        );
        
        $text = $barcode->outputText($code);
        if($options["returnResponse"] === true){
            return new Response($text, 200, $headers);
        }else{
           return $text;
        }
   }
   
   public function barcodeToHtmlAction($code, $options = null){
        var_dump("barcodeToHtmlAction-init");
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $barcode = new Barcode($this->container);
        $barcode->setEncoding(Barcode::encoding_128);
        $barcode->setScale(1);
        $headers = array(
        );
        
        $html = $barcode->outputHtml($code);
        if($options["returnResponse"] === true){
            return new Response($html, 200, $headers);
        }else{
           return $html;
        }
   }
   
   public function barcodeToImageAction($code, $options = null){
       
        if(isset($options)) {
            $options = array_merge($this->options, $options);
        }else{
            $options = $this->options;
        }
        
        $barcode = new Barcode();
        $barcode->setMode(Barcode::mode_png);
        $barcode->setEncoding(Barcode::encoding_128);
        $headers = array(
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="'.$code.'.png"'
        );
        
        $image = $barcode->outputImage($code);
        if($options["returnResponse"] === true){
            return new Response($image, 200, $headers);
        }else{
           return $image;
        }
   }
   
   
}
