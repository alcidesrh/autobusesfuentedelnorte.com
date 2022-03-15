<?php
namespace Acme\TerminalOmnibusBundle\Services;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageService {
    
   protected $templating = null;
   protected $logger = null;
   
   protected $options;
   protected $images_path = "uploads/images";
   protected $images_temp_path = "uploads/images/temp";
   protected $thumbs_path = "uploads/thumbs";
   protected $thumbs_temp_path = "uploads/thumbs/temp";
   protected $temp_path = "/temp";
   
   public function __construct($templating, $logger = null) { 
        $this->templating = $templating;
        $this->logger = $logger;
        $this->options = array(      
            'max_image_width' => 400,
            'max_image_height' => 400,
            'max_thumbs_width' => 100,
            'max_thumbs_height' => 100,
            'generate_thumbs' => true,
            'resize' => false,
            'scaled' => true,
            'jpeg_quality' => 75,
            'png_quality' => 9,
            'upload_real_path' => true,
            'generate_name_aleatory' => false,
            'new_name' => null
         );   
    }
    
    public function renameImage($oldname, $newname, $options = null){
        
        $pathImageReal = $this->getAbsolutePathImage($oldname, false);        
        if(file_exists($pathImageReal))
        { 
            if(!rename($pathImageReal, $this->getAbsolutePathImage($newname, false)))
                throw new \RuntimeException('No se pudo renombrar la imagen con nombre:('.$pathImageReal.') por ('.$this->getAbsolutePathImage($newname, false).').' );
                
            $pathThumbsReal = $this->getAbsolutePathThumbs($oldname, false);
            if(file_exists($pathThumbsReal))
            {
                if(!rename($pathThumbsReal, $this->getAbsolutePathThumbs($newname, false)))
                    throw new \RuntimeException('No se pudo renombrar el thumbs con nombre:('.$pathThumbsReal.') por ('.$this->getAbsolutePathThumbs($newname, false).').' );        
            }
            return true;
        }
        
        $pathImageTemp = $this->getAbsolutePathImage($oldname);        
        if(file_exists($pathImageTemp))
        {
            if(!rename($pathImageTemp, $this->getAbsolutePathImage($newname)))
                throw new \RuntimeException('No se pudo renombrar la imagen con nombre:('.$pathImageTemp.') por ('.$this->getAbsolutePathImage($newname).').' );
                
            $pathThumbsTemp = $this->getAbsolutePathThumbs($oldname);
            if(file_exists($pathThumbsTemp))
            {
                if(!rename($pathThumbsTemp, $this->getAbsolutePathThumbs($newname)))
                    throw new \RuntimeException('No se pudo renombrar el thumbs con nombre:('.$pathThumbsTemp.') por ('.$this->getAbsolutePathThumbs($newname).').' );
            } 
            return true;
         }         
         
         throw new \RuntimeException('No se pudo renombrar la imagen:('.$oldname.') por ('.$newname.'). porque no existe.' );
    }
    
    public function uploadImage(UploadedFile $file = null, $options = null){
        
        $result = "";
        if (null !== $file)
        {
            if(isset($options)) {
                $options = array_merge($this->options, $options);
            }else{
                $options = $this->options;
            } 
        
            $optionsImage = array_merge($options, array(      
                'max_width' => $options['max_image_width'],
                'max_height' => $options['max_image_width'],
            ));
            
            $filename = "";
            if($options['new_name'] !== null){
                $filename = $options['new_name'];
            }else if($options['generate_name_aleatory']){
                $filename = sha1(uniqid(mt_rand(), true));
            }else{
                $filename = $file->getClientOriginalName();
            }
            $filename .= '.'.$file->guessExtension();
            $result = $filename;  
            
            if($options['upload_real_path'])
            {
                $pathImage = $file->move($this->getUploadRootDirImage(false), $filename); 
                if($options['resize'])
                    $this->resize($pathImage, $optionsImage);            
                if($options['generate_thumbs'])
                {
                    $optionsThumbs = array_merge($options, array(      
                        'max_width' => $options['max_thumbs_width'],
                        'max_height' => $options['max_thumbs_width'],
                    ));
                    $pathThumbs =  $this->getAbsolutePathThumbs($filename, false);
                    copy($pathImage, $pathThumbs);
                    if($options['resize'])
                        $this->resize($pathThumbs, $optionsThumbs);
                }
            }
            else
            {
                $pathImage = $file->move($this->getUploadRootDirImage(), $filename); 
                if($options['resize'])
                    $this->resize($pathImage, $optionsImage);            
                if($options['generate_thumbs'])
                {
                    $optionsThumbs = array_merge($options, array(      
                        'max_width' => $options['max_thumbs_width'],
                        'max_height' => $options['max_thumbs_width'],
                    ));
                    $pathThumbs =  $this->getAbsolutePathThumbs($filename);
                    copy($pathImage, $pathThumbs);
                    if($options['resize'])
                        $this->resize($pathThumbs, $optionsThumbs);
                }
            }
        }
        return $result;
    }
    
    public function updateImage($initialName, UploadedFile $file = null, $options = null){
        $this->deleteImage($initialName, $options);
        return $this->uploadImage($file, $options);
    }
    
    public function deleteImage($name, $options = null){
        
        if(isset($options)) {
            $options = array_merge($this->options, $options);                
        }else{
            $options = $this->options;    
        } 
            
        if(substr_count($name, "/") != 0)
        {
            $lastSeparator = strrpos($name, "/"); 
            $name = substr($name, $lastSeparator);    
        }
        
        $pathImageReal = $this->getAbsolutePathImage($name, false);        
        if(file_exists($pathImageReal))
        { 
            if(!unlink($pathImageReal))
                throw new \RuntimeException('No se pudo eliminar la imagen:('.$pathImageReal.').');
            $pathThumbsReal = $this->getAbsolutePathThumbs($name, false);
            if(file_exists($pathThumbsReal))
                if(!unlink($pathThumbsReal))
                    throw new \RuntimeException('No se pudo eliminar el thumbs:('.$pathThumbsReal.').');  
            return true;
        }
        
        $pathImageTemp = $this->getAbsolutePathImage($name);        
        if(file_exists($pathImageTemp))
        {   
            if(!unlink($pathImageTemp))
                throw new \RuntimeException('No se pudo eliminar la imagen:('.$pathImageTemp.').');
            $pathThumbsTemp = $this->getAbsolutePathThumbs($name);
            if(file_exists($pathThumbsTemp))
                if(!unlink($pathThumbsTemp))
                    throw new \RuntimeException('No se pudo eliminar el thumbs:('.$pathThumbsTemp.').'); 
            return true;
        }
        
        throw new \RuntimeException('No se pudo eliminar la imagen:('.$name.') porque no existe.');
    }
    
    public function moveImageDirTempToDirReal($name){
        if(substr_count($name, "/") != 0)
        {
            $lastSeparator = strrpos($name, "/"); 
            $name = substr($name, $lastSeparator);    
        }
        
        $pathImageTemp = $this->getAbsolutePathImage($name);
        if(file_exists($pathImageTemp)){
            if(copy($pathImageTemp, $this->getAbsolutePathImage($name, false))){
                unlink($pathImageTemp);
            }
        }
        
        $pathThumbsTemp = $this->getAbsolutePathThumbs($name);
        if(file_exists($pathThumbsTemp)){
            if(copy($pathThumbsTemp, $this->getAbsolutePathThumbs($name, false))){
                unlink($pathThumbsTemp);
            }
        }    
        return $name;
    }
    
    public function getWebPathImage($filename)
    {   
        $path = "";
        if(file_exists($this->getAbsolutePathImage($filename, true)))
            $path = $this->getUploadDirImage(true).'/'.$filename;
        else if(file_exists($this->getAbsolutePathImage($filename, false)))
            $path = $this->getUploadDirImage(false).'/'.$filename;
        else
            return $path;
        return $this->templating->render('AcmeBackendBundle:Util:assetPath.html.twig', array('path' => $path));
    }
    
    public function getWebPathThumbs($filename)
    {
        $path = "";
        if(file_exists($this->getAbsolutePathThumbs($filename, true)))
            $path = $this->getUploadDirThumbs(true).'/'.$filename;
        else if(file_exists($this->getAbsolutePathThumbs($filename, false)))
            $path = $this->getUploadDirThumbs(false).'/'.$filename;
        else
            return $path;
        return $this->templating->render('AcmeBackendBundle:Util:assetPath.html.twig', array('path' => $path));
    }
    
    protected function getAbsolutePathImage($filename, $temp = true)
    {
        return $this->getUploadRootDirImage($temp).$filename;
    }
    
    protected function getUploadRootDirImage($temp = true)
    {
        return $this->getRootDir().$this->getUploadDirImage($temp).'/';
    }
    
    protected function getUploadDirImage($temp = true)
    {
        if($temp)  return $this->images_temp_path;
        else return $this->images_path;
    }
    
    protected function getAbsolutePathThumbs($filename, $temp = true)
    {
        return $this->getUploadRootDirThumbs($temp).$filename;
    }

    protected function getUploadRootDirThumbs($temp = true)
    {
        return $this->getRootDir().$this->getUploadDirThumbs($temp).'/';
    }
    
    protected function getUploadDirThumbs($temp = true)
    {
        if($temp)  return $this->thumbs_temp_path;
        else return $this->thumbs_path;        
    }
    
    protected function getRootDir()
    {
        return __DIR__.'/../../../../web/';
    }
    
    public function resize($file_path, $options)
    {
        list($img_width, $img_height) = @getimagesize($file_path);
        if (!$img_width || !$img_height) {
            return false;
        }
        $width = isset($options['max_width'])?$options['max_width']:$img_width;
        $height = isset($options['max_height'])?$options['max_height']:$img_height;
        $scale = min(
            $width / $img_width,
            $height / $img_height
        );

        $scaled_width = $img_width * $scale;
        $scaled_height = $img_height * $scale;

        if($options['scaled'] == false) {
            $width = $scaled_width;
            $height = $scaled_height;
        }

        $new_img = @imagecreatetruecolor($width, $height);
        $colorTransparent = imagecolorallocatealpha($new_img, 255, 255, 255, 0);
        imagefill($new_img, 0, 0, $colorTransparent);
        switch (strtolower(substr(strrchr($file_path, '.'), 1))) {
            case 'jpg':
            case 'jpeg':
                $src_img = @imagecreatefromjpeg($file_path);
                $write_image = 'imagejpeg';
                $image_quality = isset($options['jpeg_quality']) ?
                    $options['jpeg_quality'] : 75;
                break;
            case 'gif':
                @imagecolortransparent($new_img, 
	                @imagecolorallocate($new_img, 0, 0, 0));
                $src_img = @imagecreatefromgif($file_path);
                $write_image = 'imagegif';
                $image_quality = null;
                break;
            case 'png':
                @imagealphablending($new_img, false);
                @imagesavealpha($new_img, true);
                $src_img = @imagecreatefrompng($file_path);
                $write_image = 'imagepng';
                $image_quality = isset($options['png_quality']) ?
                    $options['png_quality'] : 9;
                break;
            default:
                $src_img = null;
        }

        $dst_x = 0; $dst_y = 0;
        if($options['scaled'] == true) {
        	$dst_x = abs($scaled_width-$width)/2;
        	$dst_y = abs($scaled_height-$height)/2;
        }
        $success = $src_img && @imagecopyresampled(
            $new_img,
            $src_img,
            $dst_x, 
            $dst_y, 
            0, 0,
            $scaled_width,
            $scaled_height,
            $img_width,
            $img_height
        ) && $write_image($new_img, $file_path, $image_quality);

        @imagedestroy($src_img);
        @imagedestroy($new_img);
        return $success;        
    }
}

?>
