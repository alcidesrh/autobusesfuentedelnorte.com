<?php

namespace Acme\TerminalOmnibusBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
*   @Route(path="/static")
*/
class StaticController extends Controller {

     /**
     * @Route(path="/{pagina}.html", name="pages_static-case1")
     */
    public function estaticaAction($pagina)
    {
        $respuesta = $this->render(sprintf('AcmeTerminalOmnibusBundle:Static:%s.html.twig', $pagina));
        $respuesta->setMaxAge(3600); //Cache del servidor
        $respuesta->setVary('Accept-Encoding'); //Cache del servidor
        $respuesta->setExpires(new \DateTime('now + 60 minutes')); //Cache del navegador
        return $respuesta;
    }
    
//    /**
//     * @Route(path="/jsprintsetup.xpi", name="pages_static-jsprintsetup")
//     */
//    public function descargarJsPrintSetupAction(){
//        
//        $filename = $this->getRootDir() . "complementos\\jsprintsetup092fx.xpi";
//                
//        $response = new Response();
//
//        // Set headers
//        $response->headers->set('Cache-Control', 'private');
//        $response->headers->set('Content-type', mime_content_type($filename));
//        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '";');
//        $response->headers->set('Content-length', filesize($filename));
//
//        // Send headers before outputting anything
//        $response->sendHeaders();
//
//        $response->setContent(readfile($filename));
//        
//        return $response;
//    }
    
    protected function getRootDir()
    {
        return __DIR__.'\\..\\..\\..\\..\\web\\';
    }
}

?>
