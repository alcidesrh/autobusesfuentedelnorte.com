<?php

namespace Acme\TerminalOmnibusBundle\Util;

class Util {
    
    /*
        Se utiliza para obtener un string seguro para las url, Ej. en minuscula, sin espacios, etc.
    */
    static public function getSlug($cadena, $separador = '-')
    {
        // Código copiado de http://cubiq.org/the-perfect-php-clean-url-generator
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $cadena);
        $slug = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $slug);
        $slug = strtolower(trim($slug, $separador));
        $slug = preg_replace("/[\/_|+ -]+/", $separador, $slug);
        return $slug;
    }
    
    /*
        Se utiliza para concatenar nombre mas la descripcion de una entidad, para listar en un combobox.
        $name: nombre del item
        $description: descripcion del item
        $isMaxLength: Si se quiere que la concatenacion tenga una longitud maxima. Por defecto es true.
        $maxLength: Maxima longitud de la concatenacion. Por defecto es 100. 
    */
    static public function getNameAndDescription($name, $description = "", $isMaxLength = true, $maxLength = 100)
    {
        $text = $name . " - " . $description;
        if($isMaxLength === false)
            return $text;
        
        if(strlen($text) < $maxLength)  return $text;
        else{
            //echo '$text='.$text.'<BR>';
            //echo 'substr($text, $max)='.substr($text, $maxLength).'<BR>';
            //echo 'strpos(substr($text, $max), " ")='.strpos(substr($text, $maxLength), " ").'<BR>';
            $posNextSpace = strpos(substr($text, $maxLength), " ");
            if($posNextSpace === false) //No hay mas espacios, final de la oracion.
                return $text;
            else
                return substr($text, 0, $maxLength + $posNextSpace) . " ...";
        }
    }
    
}

?>
