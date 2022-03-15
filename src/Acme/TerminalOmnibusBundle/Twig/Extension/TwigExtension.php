<?php

namespace Acme\TerminalOmnibusBundle\Twig\Extension;

use \Twig_Extension;
use \Symfony\Component\DependencyInjection\ContainerInterface;
use \Twig_Function_Method;
use Symfony\Component\Security\Core\Util\StringUtils;
use Endroid\QrCode\QrCode;
use Acme\BackendBundle\Services\UtilService;

class TwigExtension extends Twig_Extension {
    
    protected $container;
    
    function __construct(ContainerInterface $container) {
        $this->container = $container;
    }
    
    public function getName() {
        return 'twigExtension';
    }    
    
    public function getFunctions() {
        return array(
            'getWebPathImage' => new Twig_Function_Method($this, 'getWebPathImage'),
            'getWebPathThumbs' => new Twig_Function_Method($this, 'getWebPathThumbs'),
            'seoRenderMenuItem' => new Twig_Function_Method($this, 'seoRenderMenuItem'),
            'fixedRouteName' => new Twig_Function_Method($this, 'fixedRouteName'),
            'getParameter' => new Twig_Function_Method($this, 'getParameter', array(
                'is_safe' => array('html')
            )),
            'processText' => new Twig_Function_Method($this, 'processText', array(
//                'is_safe' => array('html')
            )),
            'toHex' => new Twig_Function_Method($this, 'toHex', array(
                'is_safe' => array('html')
            )),
            'renderImg' => new Twig_Function_Method($this, 'renderImg', array(
                'is_safe' => array('html')
            )),
            'renderFigure' => new Twig_Function_Method($this, 'renderFigure', array(
                'is_safe' => array('html')
            )), 
            'customQRcodeImg' => new Twig_Function_Method($this, 'customQRcodeImg', array(
                'is_safe' => array('html')
            )),
            'customDataMatrixcodeImg' => new Twig_Function_Method($this, 'customDataMatrixcodeImg', array(
                'is_safe' => array('html')
            )),
            'customDataMatrixCodeDIV' => new Twig_Function_Method($this, 'customDataMatrixCodeDIV', array(
                'is_safe' => array('html')
            )),
            'custom1DcodeImg' => new Twig_Function_Method($this, 'custom1DcodeImg', array(
                'is_safe' => array('html')
            )),
            'custom1DcodeDIV' => new Twig_Function_Method($this, 'custom1DcodeDIV', array(
                'is_safe' => array('html')
            )),
            'customRenderImg' => new Twig_Function_Method($this, 'customRenderImg', array(
                'is_safe' => array('html')
            )),
            'renderMenu' => new Twig_Function_Method($this, 'renderMenu', array(
                'is_safe' => array('html')
            )),
            'getConexiones' => new Twig_Function_Method($this, 'getConexiones'),
            'getEstaciones' => new Twig_Function_Method($this, 'getEstaciones'),
            'getGalerias' => new Twig_Function_Method($this, 'getGalerias'),
            'getGaleriaById' => new Twig_Function_Method($this, 'getGaleriaById'),
            'getImagenesGaleria' => new Twig_Function_Method($this, 'getImagenesGaleria'),
            'getNH' => new Twig_Function_Method($this, 'getNH'),
            'getNL' => new Twig_Function_Method($this, 'getNL'),
            'getValue' => new Twig_Function_Method($this, 'getValue'),
            'getBarCodeEAN8' => new Twig_Function_Method($this, 'getBarCodeEAN8'),
            'getBarCodeEAN13' => new Twig_Function_Method($this, 'getBarCodeEAN13'),
            'getBarCodeCode128' => new Twig_Function_Method($this, 'getBarCodeCode128'),
            'getBarCodeCode93' => new Twig_Function_Method($this, 'getBarCodeCode93'),
            'getBarCodeUPCA' => new Twig_Function_Method($this, 'getBarCodeUPCA'),
            'convertToHex' => new Twig_Function_Method($this, 'convertToHex')
        );        
    }
    /*
        A través de la variable $entorno puedes acceder a información como la versión de Twig
        ($entorno::VERSION), la codificación de caracteres utilizada ($entorno->getCharset()), o si
        Twig se está ejecutando en modo debug ($entorno->isDebug()).
     */
    public function getFilters()
    {
        /*
         * FILTRO DINAMICO. MACHEA CON mostrar_ul, mostrar_ol, mostrar_xx
         */
        return array(
            'parseCantidad' => new \Twig_Filter_Method($this, 'parseCantidad'),
            'parseImporte' => new \Twig_Filter_Method($this, 'parseImporte'),
            'accesskey' => new \Twig_Filter_Method($this, 'accesskey', array(
                'is_safe' => array('html'),
            )),
            'fixedRouteName' => new \Twig_Filter_Method($this, 'fixedRouteName', array(
                'is_safe' => array('html'),
            )),
            'mostrar_*' => new \Twig_Filter_Method($this, 'mostrar', array(
                'is_safe' => array('html'),
                'pre_escape' => array('html'),
                'needs_environment' => true
            ))
        );
    }
    
    public function parseCantidad($valor){ 
       return rtrim(rtrim(number_format($valor, 6, ',', ''), '0'), ',');
    }
    
    public function parseImporte($valor, $decimles = 2){ 
       return rtrim(rtrim(number_format(round($valor, $decimles, PHP_ROUND_HALF_UP), $decimles, ',', ''), '0'), ',');
    }
    
    public function getBarCodeEAN8($data, $pref = "|HEX|"){
        $type = "01";
        $data = str_pad(strval($data), 7, "0", STR_PAD_LEFT);
        $data .= UtilService::getEANCheckDigit($data);
        $barcode  = $pref . "x1Bx28x42";
        $barcode .= $this->getNL($data, $type);
        $barcode .= $this->getNH($data, $type);
        $barcode .= "x".$type."x01x00x0Fx00x00";
        $barcode .= $this->convertToHex($data);
        $barcode .= $pref;
        return $barcode;
    }
    
    public function getBarCodeEAN13($data, $pref = "|HEX|"){
        $type = "00";
        $data = str_pad(strval($data), 12, "0", STR_PAD_LEFT);
        $data .= UtilService::getEANCheckDigit($data);
        $barcode  = $pref . "x1Bx28x42";
        $barcode .= $this->getNL($data, $type);
        $barcode .= $this->getNH($data, $type);
        $barcode .= "x".$type."x01x00x0Fx00x00";
        $barcode .= $this->convertToHex($data);
        $barcode .= $pref;
        return $barcode;
    }

    public function getBarCodeCode128($data, $pref = "|HEX|"){
        $type = "06";
        $barcode  = $pref . "x1Bx28x42";
        $barcode .= $this->getNL($data, $type);
        $barcode .= $this->getNH($data, $type);
        $barcode .= "x".$type."x01x00x0Fx00x03x41";
        $barcode .= $this->convertToHex($data);
        $barcode .= $pref;
        return $barcode;
    }
    
    public function getBarCodeCode93($data, $pref = "|HEX|"){
        $type = "05";
        $barcode  = $pref . "x1Bx28x42";
        $barcode .= $this->getNL($data, $type);
        $barcode .= $this->getNH($data, $type);
        $barcode .= "x".$type."x01x00x0Fx00x00";
        $barcode .= $this->convertToHex($data);
        $barcode .= $pref;
        return $barcode;
    }
    
    public function getBarCodeUPCA($data, $pref = "|HEX|"){
        $type = "03";
        $data = str_pad(strval($data), 11, "0", STR_PAD_LEFT);
        $barcode  = $pref . "x1Bx28x42";
        $barcode .= $this->getNL($data, $type);
        $barcode .= $this->getNH($data, $type);
        $barcode .= "x".$type."x01x00x0Fx00x03";
        $barcode .= $this->convertToHex($data);
        $barcode .= $pref;
        return $barcode;
    }
    
    public function getNH($value, $type = "06") {
        $size = 6;
        if($type === "06"){ $size = 7; }
        $value = strval($value);
        $size += strlen($value);
        $temp = intval($size / 256);
        $hexCode = dechex($temp);
        return "x" . strToUpper(substr('0'.$hexCode, -2));
    }
    
    public function getNL($value, $type = "06") {
        $size = 6;
        if($type === "06"){ $size = 7; }
        $value = strval($value);
        $size += strlen($value);
        $temp = $size % 256;
        $hexCode = dechex($temp);
        return "x" . strToUpper(substr('0'.$hexCode, -2));
    }
    
    public function getValue($value) {
        return strval($value);
    }
    
    public function convertToHex($value){
        return $this->strToHex($value);
    }

    public function strToHex($value){
        $hex = '';
        $value = strval($value);
        for ($i=0; $i<strlen($value); $i++){
            $ord = ord($value[$i]);
            $hexCode = dechex($ord);
            $hex .= "x" . strToUpper(substr('0'.$hexCode, -2));
        }
        return $hex;
    }

    public function getImagenesGaleria($id) {
        $estaciones = $this->container->get('doctrine')->getRepository('AcmeTerminalOmnibusBundle:Imagen')->listarImagenesGaleria($id);
        return $estaciones;
    }
    
    public function getGaleriaById($id) {
        $estaciones = $this->container->get('doctrine')->getRepository('AcmeTerminalOmnibusBundle:Galeria')->find($id);
        return $estaciones;
    }
    
    public function getGalerias() {
        $estaciones = $this->container->get('doctrine')->getRepository('AcmeTerminalOmnibusBundle:Galeria')->listarGaleriaActivas();
        return $estaciones;
    }
    
    public function getConexiones() {
        $conexiones = $this->container->get('doctrine')->getRepository('AcmeTerminalOmnibusBundle:Conexiones')->findByActivo(true);
        return $conexiones;
    }
    
    public function getEstaciones() {
        $estaciones = $this->container->get('doctrine')->getRepository('AcmeTerminalOmnibusBundle:Estacion')->getAllDestinosEstacionesActivasPublicidad();
        return $estaciones;
    }
    
    public function getWebPathImage($filename) {
        return $this->container->get('acme_terminal_omnibus_image')->getWebPathImage($filename);               
    }
    
    public function getWebPathThumbs($filename) {
        return $this->container->get('acme_terminal_omnibus_image')->getWebPathThumbs($filename);               
    }
    
    //USO {{ seoRenderTitle(route) }}
    public function seoRenderTitle($routeName = null)
    {    
        try {
            $title = strip_tags($this->container->get('acme_terminal_omnibus_image')->getTitlePage($routeName));
            echo sprintf("<title>%s</title>\n", $title);
        } catch (\RuntimeException $exc) {
            
        }
    }
    
    //USO {{ seoRenderMetadatas(route) }}
    public function seoRenderMetadatas($routeName = null)
    {
         try {
            $metas = $this->container->get('acme_terminal_omnibus_image')->getMetadatasPage($routeName);
            $keywords = null;
            foreach ($metas as $meta) {
                if($meta->getEnable())
                {
                    if(trim($meta->getType()) === "name" && trim($meta->getName()) === "keywords")
                    {
                        if($keywords !== null) $keywords .= ", ";
                        $keywords .= trim($meta->getContent());
                    }
                    else
                    {
                        echo sprintf("<meta %s=\"%s\" content=\"%s\" />\n",
                             $meta->getType(),
                             strip_tags($meta->getName()),
                             strip_tags(trim($meta->getContent()))
                         );  
                    }
                }
            }
            if($keywords !== null)
            {
               echo sprintf("<meta %s=\"%s\" content=\"%s\" />\n", "name", "keywords", strip_tags($keywords)); 
            }
        } catch (\RuntimeException $exc) {
            
        }
    }
    
    //USO <html{{ seoRenderHtmlAttributes(route) }}>
    public function seoRenderHtmlAttributes($routeName = null, $renderTagHtml = false)
    {
        try {
            $htmlAttributes = $this->container->get('acme_terminal_omnibus_image')->getHtmlAttributesPage($routeName);
            if($renderTagHtml)
               echo sprintf("<html");
            foreach ($htmlAttributes as $name => $value){
                if(substr_count($value, "%") > 0)
                    $value = $this->getVariable ($value);               
               echo sprintf(" %s=\"%s\"", $name, $value);        
            }
            if($renderTagHtml)
               echo sprintf(">\n");
        } catch (\RuntimeException $exc) {
           if($renderTagHtml)
               echo sprintf("<html>\n");
        }
    }
    
    private function getVariable($name){
        if(substr_count($name, "%") == 0)
            return $name; 
        $name = str_ireplace("%", "",  $name);
        $value = "";
        switch ($name) {
            case "lang":
                $locate = $this->container->get('request')->getLocale();
                $values = explode("_", $locate);
                if(count($values) > 0)
                    $value = $values[0];             
                break;
            default:
                break;
        } 
        return $value;
    }
    
    //USO <head{{ seoRenderHeadAttributes(route) }}>
    public function seoRenderHeadAttributes($routeName = null, $renderTagHead = false)
    {
        try {
            $headAttributes = $this->container->get('acme_terminal_omnibus_image')->getHeadAttributesPage($routeName);
            if($renderTagHead)
                echo sprintf("<head");
            foreach ($headAttributes as $name => $value)
                echo sprintf(" %s=\"%s\"", $name, $value);  
            if($renderTagHead)
               echo sprintf(">\n");
        } catch (\RuntimeException $exc) {
            if($renderTagHead)
               echo sprintf("<head>\n");
        }
    }
    
    //USO {{ seoRenderLink(route) }}
    public function seoRenderLink($routeName = null)
    {
        try {
            
            $links = $this->container->get('acme_terminal_omnibus_image')->getLinksPage($routeName);
            foreach ($links as $link) {
                if($link->getEnable())
                {
                    $str = "";
                    if($link->getAsync()) 
                        $str .= "async ";

                    if($link->getRel() !== null && trim($link->getRel()) !== "") 
                        $str .= "rel=\"" . $link->getRel() . "\" ";

                    if($link->getHref() !== null && trim($link->getHref()) !== "") 
                        $str .= "href=\"" . $link->getHref() . "\" ";

                    if($link->getTitle() !== null && trim($link->getTitle()) !== "") 
                        $str .= "title=\"" . $link->getTitle() . "\" ";

                    if($link->getMedia() !== null && trim($link->getMedia()) !== "") 
                        $str .= "media=\"" . $link->getMedia() . "\" ";

                    if($link->getType() !== null && trim($link->getType()) !== "") 
                        $str .= "type=\"" . $link->getType() . "\" ";

                    if($link->getHreflang() !== null && trim($link->getHreflang()) !== "") 
                        $str .= "hreflang=\"" . $link->getHreflang() . "\" ";

                    if($link->getSizes() !== null && trim($link->getSizes()) !== "") 
                        $str .= "sizes=\"" . $link->getSizes() . "\" ";

                    if(trim($str) !== null)
                        echo sprintf("<link " . $str ." />\n");
                }
            }
        } catch (\RuntimeException $exc) {
            
        }
    }
     
    public function seoRenderAllTags($routeName = null)
    {
        try {
            $this->container->get('acme_terminal_omnibus_image')->getAllSeoTagsPage($routeName);
            $this->seoRenderHtmlAttributes($routeName, true);
            $this->seoRenderHeadAttributes($routeName, true);
            $this->seoRenderTitle($routeName);
            $this->seoRenderMetadatas($routeName);
            $this->seoRenderLink($routeName);
        } catch (\RuntimeException $exc) {
            
        }
    }
    
    public function seoRenderFooter($routeName = null)
    {        
        try {
            $footer = strip_tags($this->container->get('acme_terminal_omnibus_image')->getFooterPage($routeName));
            echo sprintf("<p>%s</p>\n", $footer);
        } catch (\RuntimeException $exc) {
            
        }
    }
    
    public function getParameter($clave)
    {        
       return $this->container->getParameter($clave);         
    }
    
    public function toHex($value){
        return "\x".dechex($value);
    }
   
//    public function toHexFilt($text){ 
//        $text = str_replace('#', '\x23', $text);
//        $text = str_replace('$', '\x24', $text);
//        $text = str_replace('á', '\x40', $text);
//        $text = str_replace('Ñ', '\x5C', $text);
//        $text = str_replace('é', '\x5E', $text);
//        $text = str_replace('í', '\x7B', $text);
//        $text = str_replace('ñ', '\x7C', $text);
//        $text = str_replace('ó', '\x7D', $text);
//        $text = str_replace('ú', '\x7E', $text);
//        return $text;
//    }
    
    public function processText($text, $options)
    {        
        if(array_key_exists("text_init", $options)){
            if(is_numeric($options["text_init"])){
                $text =  str_pad('', $options["text_init"]) . $text;
            }else{
                $text =  $options["text_init"] . $text;
            }
        }
        if(array_key_exists("text_end", $options)){
            if(is_numeric($options["text_end"])){
                $text =  $text . str_pad('', $options["text_end"]);
            }else{
                $text =  $text . $options["text_end"];
            }
        }
        if(array_key_exists("text_length", $options)){
            $length1 = strlen($text);
            $length2 = intval($options["text_length"]);
            if($length1 < $length2){
                $text = str_pad($text, $length2); 
            }else if($length1 > $length2){
                if($length2 > 5){
                    $text = substr($text, 0, $length2-3) . "...";     
                }else{
                    $text = substr($text, 0, $length2);
                }
            }
        }
        
        return $text;         
    }
    
    public function renderFigure($clave, $isThumbs = false, $options = array())
    {    
        $options['call_internal'] = true;
        $result = $this->renderImg($clave, $isThumbs, $options);
        $str  = "<figure";
        if(array_key_exists("figure_css", $options)){
            $str .= " class=\"" . $options["figure_css"] . "\" ";
        }
        $str  .= ">" . $result['str'];       
        $str .= " <figcaption";
        if(array_key_exists("figcaption_css", $options)){
            $str .= " class=\"" . $options["figcaption_css"] . "\" ";
        }
        $str  .= ">" . $result['image']['description'] . "</figcaption>";
        $str .= "</figure>";
        return $str;     
    }
    
    public function renderImg($clave, $isThumbs = false, $options = array())
    {   
        $image = array();        
        if(array_key_exists("db", $options) &&  $options['db'] === true){
            $image = $this->getImagenDB($clave);
            if($image === null)
                throw new \RuntimeException('La imagen:' . $clave . ' no existe.');
        }
        elseif (array_key_exists("i18n", $options) &&  $options['i18n'] === true) {
            $image = $this->getImagenI18N($clave);
            if($image === null)
                throw new \RuntimeException('La imagen:' . $clave . ' no existe.');
        }else{
            $image = $this->getImagenI18N($clave);
            if($image === null){
                $image = $this->getImagenDB($clave);
                if($image === null)
                    throw new \RuntimeException('La imagen:' . $clave . ' no existe.');
            } 
        }
        
        $str  = "<img ";                
        if(array_key_exists("img_css", $options)){
            $str .= "class=\"" . $options["img_css"] . "\" ";
        }
        $str .= "src=\"". ($isThumbs ? $this->getWebPathThumbs($image['src']) : $this->getWebPathImage($image['src'])) . "\" ";
        $str .= "alt=\"" . $image['alt'] . "\" ";
        $str .= "title=\"" . $image['title'] . "\" />";
        
        if(array_key_exists("call_internal", $options) && $options['call_internal'] === true){
            $result = array();
            $result['image'] = $image;
            $result['str'] = $str;
            return $result;
        }
        else
            return $str;    
    }
    
    private function getImagenI18N($clave)
    {       
       $claveSrc = 'image.'.$clave.'.src';
       $src = $this->container->get('translator')->trans($claveSrc, array(), 'image');
       if($src == $claveSrc)
            return null;
       $claveAlt = 'image.'.$clave.'.alt';
       $alt = $this->container->get('translator')->trans($claveAlt, array(), 'image');
       if($alt == $claveAlt) $alt = "";
       $claveTitle = 'image.'.$clave.'.title';
       $title = $this->container->get('translator')->trans($claveTitle, array(), 'image');
       if($title == $claveTitle) $title = "";
       $claveDescription = 'image.'.$clave.'.description';
       $description = $this->container->get('translator')->trans('image.'.$clave.'.description', array(), 'image');
       if($description == $claveDescription) $description = ""; 
       $result = array();
       $result['src'] = $src;
       $result['alt'] = $alt;
       $result['title'] = $title;
       $result['description'] = $description;
       return $result;
    }
    
    private function getImagenDB($clave)
    {       
       $imagen = $this->container->get('doctrine')->getRepository('AcmeTerminalOmnibusBundle:Image')->getImageByClave($clave);
       if($imagen === null)
            return null;
       $result = array();
       $result['src'] = $imagen->getFullName();
       $result['alt'] = $imagen->getAlt();
       $result['title'] = $imagen->getTitle();
       $result['description'] = $imagen->getDescription();
       return $result;
    }
    
    public function seoRenderMenuItem($routeName = null)
    {
        //echo sprintf("<meta %s=\"%s\" content=\"%s\" />\n", "name", "keywords", strip_tags($keywords)); 
    }
    
    public function seoRenderMenu($routeName = null)
    {
        
    }
    
    public function accesskey($value, $keyAux = null)
    {
        $existLabel = stripos($value, "<label");
        if( $existLabel !== false)
        {
            $items = explode("<div>", $value);
            $result = array();
            foreach ($items as $item){
                if(trim($item) != "")
                {
                    $key = $this->extraerAccesskey($item);
                    if($key === null){
                        if($keyAux === null)
                            throw new \RuntimeException('Debe especificar una letra para el accesskey para el label:' . $item . '.');
                        else
                            $key = $keyAux;   
                    }else{
                        if($keyAux !== null && StringUtils::equals(strtolower($key), strtolower($keyAux)) === false){
                            throw new \RuntimeException('El accesskey del label ('.$key.') no coincide con el parametro ('.$keyAux.').');
                        }
                    }
                    
                    $contLabelInit = null;
                    $contLabelValue = null;
                    $contLabelEnd = null;
                    $posLabel = stripos($item, "<label");
                    if($posLabel != 0)
                    {
                        $posLabelInit = strpos($item, "<label");
                        $posLabelEnd = strpos($item, "</label>");
                        $contLabelInit = substr($item, 0, $posLabelInit); 
                        $contLabelValue = substr($item, $posLabelInit, $posLabelEnd-$posLabelInit+8);
                        $contLabelEnd = substr($item, $posLabelEnd+8);
                        $item = $contLabelValue;
                    }

                    $posInit = strpos($item, ">");
                    $posEnd = strpos($item, "</");
                    $contInit = substr($item, 0, $posInit+1); 
                    $contValue = substr($item, $posInit+1, $posEnd-$posInit-1);
                    $contEnd = substr($item, $posEnd);
                    $pos = stripos($contValue, $key);
                    if($pos !== false){
                        $keylenght = strlen($key);
                        $contValueInit = substr($contValue, 0, $pos); 
                        $contValueValue = substr($contValue, $pos, $keylenght);
                        $contValueEnd = substr($contValue, $pos+$keylenght);
                        $contValue = $contValueInit . "<u>" . $contValueValue . "</u>" . $contValueEnd;  
                    }else{
                        throw new \RuntimeException('El accesskey ('.$key.') no se puede marcar en el texto del componente ('.$value.')');
                    }
                    $item = $contInit . $contValue . $contEnd; 

                    if($posLabel != 0)
                    {
                        $item = $contLabelInit . $item . $contLabelEnd;
                    }
                }//end if
                $result[] = $item;      
            }//end for
            $value = implode("<div>", $result);                
        }//end if
        else    //only text
        {   
            if($keyAux === null)
                throw new \RuntimeException('Debe especificar una letra como parametro (accesskey) para el texto:' . $value . '.');
                
             $pos = stripos($value, $keyAux);
             if($pos !== false){
                $keylenght = strlen($keyAux);
                $contValueInit = substr($value, 0, $pos); 
                $contValueValue = substr($value, $pos, $keylenght);                    
                $contValueEnd = substr($value, $pos+$keylenght);
                $value = $contValueInit . "<u>" . $contValueValue . "</u>" . $contValueEnd; 
             }else{
                throw new \RuntimeException('El parametro accesskey ('.$keyAux.') no se puede marcar en el texto ('.$value.')');
             }
        }
        return $value;
    }
    
    private function extraerAccesskey($value){
        $pos = strpos($value, "accesskey");
        if($pos === false)
            return null;
        
        $value1 = substr($value, strpos($value, "accesskey"));
        $value2 = substr($value1, strpos($value1, '"') + 1); 
        return substr($value2, 0, strpos($value2, '"')); 
    }
    
    public function fixedRouteName($routeName){ 
        $pos = strpos($routeName, "-");
        if($pos) $routeName = substr($routeName, 0, $pos);
        return $routeName;
    }
    
    // USO: {{ "mi texto" | mostrar_ol("mi parametro") }}
    public function mostrar(\Twig_Environment $entorno, $tipo, $values = null, $parameter = null)
    {
        var_dump($tipo);
        var_dump($values);
        var_dump($parameter);
        var_dump($entorno);
        
//        $codificacion = $entorno->getCharset();
//        switch ($tipo) {
//            case 'ul':
//                echo 'ul...';
//            break;
//            case 'ol':
//                echo 'ol...';
//            break;
//            default :
//                echo 'default...';
//        } 
        
        $html = "";
        if(is_string($values)){
            return "paso>>>".$html;
        }else if(is_array($values)){
            $html = "<".$tipo.">\n";
            foreach ($values as $opcion){
                $html .= " <li>".$opcion."</li>\n";
            }
            $html .= "</".$tipo.">\n";
        }
   
        return $html;
    }
    
    public function renderMenu($nameMenu) {
        return $this->container->get('acme_terminal_omnibus_menu')->renderMenu($nameMenu);               
    }
    
//  $error_correction: 
//  0: Error Correction Level Medium (15%)
//  1: Error Correction Level Low (7%)
//  2: Error Correction Level High (30%)
//  3: Error Correction Level Quartile (25%) 
    
    public function customQRcodeImg($text, $extension = "png", $size = 50, $width = null, $height = null, $error_correction = 2, $security = true) {
        
        $key = $this->container->getParameter("encrypt_password");
        if(!$key){
            throw new \RuntimeException("m1No se pudo obtener la clave de encriptación.");
        }
        
        $qrCode = new QrCode();
        $qrCode->setSize($size);
        $qrCode->setErrorCorrection($error_correction);
        
        if($security){ //Encriptacion
           $text = UtilService::generarBitChequeo($text);
           $text = UtilService::encrypt($key, $text);  
        }
        
        $qrCode->setText($text);
        $qrCode = $qrCode->get($extension);
        $imageBase64 = base64_encode($qrCode);
        
        $mime_type = 'image/'.$extension;
        if ($extension == 'jpg') {
            $mime_type = 'image/jpeg';
        }
        //El tamaño fijo es porque se imprime en una impresora de punto y la impresora la esta encogiendo por el ancho.
        //120px x 60px
        //return '<img style="position:absolute; width: 125px; height: 80px;" src="data:'.$mime_type.';base64,'.$imageBase64.'"/>';
        if($width === null){
            $width = $size . "px";
        }
        if($height === null){
            $height = $size . "px";
        }
        return '<img style="width: '.$width.'; height: '.$height.';" src="data:'.$mime_type.';base64,'.$imageBase64.'"/>';
    }
    
    public function customDataMatrixcodeImg($text, $extension = "png", $size = 5, $width = null, $height = null, $error_correction = 2, $security = true) {
        
        $key = $this->container->getParameter("encrypt_password");
        if(!$key){
            throw new \RuntimeException("m1No se pudo obtener la clave de encriptación.");
        }
        
        if($security){ //Encriptacion
           $text = UtilService::generarBitChequeo($text);
           $text = UtilService::encrypt($key, $text);  
        }
        
        $pathBarCode = $this->getPathBarCode();
        include_once  $pathBarCode . '\barcodes.php';
        $d2 = new \DNS2DBarcode();
        $d2->save_path = $pathBarCode . "\\Image\\" . "imagedatamatrix_";
        $pathImage = $d2->getBarcodePNGPath($text, 'datamatrix', 2, 2);
        $imageBase64 = base64_encode(file_get_contents($pathImage));
        if(file_exists($pathImage)){
            unlink($pathImage);
        }
        $mime_type = 'image/'.$extension;
        if ($extension == 'png') {
            $mime_type = 'image/png';
        }
        
        if($width === null){
            $width = $size . "px";
        }
        if($height === null){
            $height = $size . "px";
        }
        return '<img style="width: '.$width.'; height: '.$height.';" src="data:'.$mime_type.';base64,'.$imageBase64.'"/>'; 
    }
    
    public function customDataMatrixCodeDIV($text) {
        
        $pathBarCode = $this->getPathBarCode();
        include_once  $pathBarCode . '\barcodes.php';
        $d2 = new \DNS2DBarcode();
        $html = $d2->getBarcodeHTML($text, 'datamatrix', 4.8, 3);
        return $html; 
    }
    
    public function custom1DcodeImg($text, $extension = "png", $size = 5, $width = null, $height = null, $error_correction = 2, $security = true) {
        
        $pathBarCode = $this->getPathBarCode();
        include_once  $pathBarCode . '\barcodes.php';
        $d1 = new \DNS1DBarcode();
        $d1->save_path = $pathBarCode . "\\Image\\" . "image1D_";
        $pathImage = $d1->getBarcodePNGPath($text, 'C128', 1, 25);
        $imageBase64 = base64_encode(file_get_contents($pathImage));
        if(file_exists($pathImage)){
            unlink($pathImage);
        }
        $mime_type = 'image/'.$extension;
        if ($extension == 'png') {
            $mime_type = 'image/png';
        }
        return '<img src="data:'.$mime_type.';base64,'.$imageBase64.'"/>'; 
    }
    
    public function custom1DcodeDIV($text, $type = 'C128', $w=1.6, $h=65) {
        
        $pathBarCode = $this->getPathBarCode();
        include_once  $pathBarCode . '\barcodes.php';
        $d1 = new \DNS1DBarcode();
        $html = $d1->getBarcodeHTML($text, $type, $w, $h); //1.6
        return $html; 
    }
    
    private function getPathBarCode() {
        $clase = new \ReflectionClass("Acme\TerminalOmnibusBundle\BarcodesGenerator\CodeBar");
        $fileName = $clase->getFileName();
        $basePath = str_replace("\CodeBar.php", "", $fileName);
        return $basePath;
    }
    
    public function customRenderImg($path) {
        if ($path) {
            $filename = $this->getRootDir() . $path;
            $imgbinary = fread(fopen($filename, "r"), filesize($filename));
            return 'data:image/jpg;base64,'.base64_encode($imgbinary);
        }else{
            return '';
        }
    }
    
    protected function getRootDir()
    {
        return __DIR__.'\\..\\..\\..\\..\\..\\web\\';
    }
}

?>
