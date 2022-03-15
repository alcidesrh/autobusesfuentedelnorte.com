<?php

namespace Acme\BackendBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Acme\BackendBundle\Form\CalendarioFacturaRutaType;
use Acme\TerminalOmnibusBundle\Entity\CalendarioFacturaRuta;
use Acme\TerminalOmnibusBundle\Entity\CalendarioFacturaFecha;

class CalendarioFacturaRutaCRUDController extends CRUDController {
     
    public function cargarCalendariosFacturaFechaDesdeHiddenHaciaObjeto(CalendarioFacturaRuta $object)
    {
        $listaActual = $object->getListaCalendarioFacturaFecha(); 
        $listaOkNotDeleted = array();
        $listaNewHidden = $object->getListaCalendarioFacturaFechaHidden();
        $listaNewJson = json_decode($listaNewHidden);
        $fechaActual = new \DateTime(); //Cargar de la BD
        $fechaActual = $fechaActual->format('d-m-Y ');
        foreach ($listaNewJson as $json) {
            
            //GARANTIZAR NO CAMBIAR NADA ANTERIOR A LA FECHA DEL SISTEMA
            if($this->compararFechas($fechaActual, $json->fecha) >= 0){
                $item = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:CalendarioFacturaFecha')->find($json->id);
                $listaOkNotDeleted[] = $item;
                continue;
            }
            
            $item = null;            
            $isNew = $this->isNewId($json->id);
            if(!$isNew) {
                $item = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:CalendarioFacturaFecha')->find($json->id);
                $listaOkNotDeleted[] = $item;
                $empresa = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Empresa')->find($json->empresa);
                $item->setEmpresa($empresa);
            }
            else{
                $item = new CalendarioFacturaFecha();
                $empresa = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Empresa')->find($json->empresa);
                $item->setEmpresa($empresa);
            }
            $item->setFecha($json->fecha);
            if($isNew) {
                $object->addListaCalendarioFacturaFecha($item);
            }
        }
        
        $listaDeleted = array();
        foreach ($listaActual as $item) {
            if(!$this->isNewId($item->getId()) && !in_array($item, $listaOkNotDeleted)){                
                $listaDeleted[] = $item;                
            }            
        }
        foreach ($listaDeleted as $item) {
            $listaActual->removeElement($item);
        }
    }
    
    public function compararFechas($primera, $segunda)
    {
        $valoresPrimera = explode ("-", $primera);  
        var_dump($valoresPrimera);
        $valoresSegunda = explode ("-", $segunda); 
        var_dump($valoresSegunda);
        
        $diaPrimera    = intval($valoresPrimera[0]);  
        $mesPrimera  =   intval($valoresPrimera[1]);  
        $anyoPrimera   = intval($valoresPrimera[2]); 

        $diaSegunda   = intval($valoresSegunda[0]);  
        $mesSegunda = intval($valoresSegunda[1]);  
        $anyoSegunda  = intval($valoresSegunda[2]);

        $diasPrimeraJuliano = gregoriantojd($mesPrimera, $diaPrimera, $anyoPrimera);  
        $diasSegundaJuliano = gregoriantojd($mesSegunda, $diaSegunda, $anyoSegunda);     

        if(!checkdate($mesPrimera, $diaPrimera, $anyoPrimera)){
             throw new \RuntimeException("Fecha no válida: " . $primera);
        }elseif(!checkdate($mesSegunda, $diaSegunda, $anyoSegunda)){
           throw new \RuntimeException("Fecha no válida: " . $segunda);
        }else{
          return  $diasPrimeraJuliano - $diasSegundaJuliano;
        } 
   }
    public function isNewId($id)
    {
        return ($id === null || trim($id) === "" || trim($id) === "0");
    }
    
    public function cargarCalendariosFacturaFechaDesdeObjetoHaciaHidden(CalendarioFacturaRuta $object)
    {
        $listaJson = array();
        $lista = $object->getListaCalendarioFacturaFecha();        
        foreach ($lista as $elemento) {            
            $item = new \stdClass();
            $item->id = $elemento->getId();
            $item->fecha = $elemento->getFecha()->format('d-m-Y ');
            if($elemento->getEmpresa() !== null){
                $item->empresa = $elemento->getEmpresa()->getId();
            }
            $listaJson[] =  $item;
        }
        $object->setListaCalendarioFacturaFechaHidden(json_encode($listaJson));
    }
    
    public function editAction($id = null)
    {
        // the key used to lookup the template
        $templateKey = 'edit';

        $id = $this->get('request')->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $this->cargarCalendariosFacturaFechaDesdeObjetoHaciaHidden($object);
        
        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }
        
        $this->admin->setSubject($object);
        
        $form = $this->createForm(new CalendarioFacturaRutaType($this->getDoctrine()), $object); 
        
        /** @var $form \Symfony\Component\Form\Form */
//        $form = $this->admin->getForm();
        $form->setData($object);

        if ($this->getRestMethod() == 'POST') {
            
            $commad = $this->get('request')->request->get("acme_backendbundle_calendario_factura_ruta_type");
            $object->setListaCalendarioFacturaFechaHidden($commad["listaCalendarioFacturaFechaHidden"]);
            $this->cargarCalendariosFacturaFechaDesdeHiddenHaciaObjeto($object);
            
            $form->bind($this->get('request'));
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
//                
                $this->admin->update($object);
//
                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(array(
                        'result'    => 'ok',
                        'objectId'  => $this->admin->getNormalizedIdentifier($object)
                    ));
                }

                $this->addFlash('sonata_flash_success', 'flash_edit_success');

                // redirect to edit mode
                return $this->redirectTo($object);
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest()) {
                    $this->addFlash('sonata_flash_error', 'flash_edit_error');
                }
            } elseif ($this->isPreviewRequested()) {
                // enable the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

       
        $view = $form->createView();
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());
        return $this->render($this->admin->getTemplate($templateKey), array(
            'action' => 'edit',
            'form'   => $view,
            'object' => $object,
        ));
    }
    
    
    
    
    public function createAction()
    {
        // the key used to lookup the template
        $templateKey = 'edit';    
        
        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        $object = $this->admin->getNewInstance();
        
        $this->cargarCalendariosFacturaFechaDesdeObjetoHaciaHidden($object);
              
        $this->admin->setSubject($object);        
        
        $form = $this->createForm(new CalendarioFacturaRutaType($this->getDoctrine()), $object); 
        
        /** @var $form \Symfony\Component\Form\Form */
//        $form = $this->admin->getForm();
        $form->setData($object);

        if ($this->getRestMethod()== 'POST') {            
             
            $commad = $this->get('request')->request->get("acme_backendbundle_calendario_factura_ruta_type");
            $object->setListaCalendarioFacturaFechaHidden($commad["listaCalendarioFacturaFechaHidden"]);
            $this->cargarCalendariosFacturaFechaDesdeHiddenHaciaObjeto($object);
            
            $form->setData($object);
            
            $form->bind($this->get('request'));
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {                
                
                $this->admin->create($object);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(array(
                        'result' => 'ok',
                        'objectId' => $this->admin->getNormalizedIdentifier($object)
                    ));
                }

                $this->addFlash('sonata_flash_success','flash_create_success');
                // redirect to edit mode
                return $this->redirectTo($object);
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest()) {
                    $this->addFlash('sonata_flash_error', 'flash_create_error');
                }
            }
        }

        $view = $form->createView();
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());
        return $this->render($this->admin->getTemplate($templateKey), array(
            'action' => 'create',
            'form'   => $view,
            'object' => $object,
        ));
    }
    
}

?>
