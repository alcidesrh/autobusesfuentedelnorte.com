<?php

namespace Acme\BackendBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Acme\BackendBundle\Form\TipoBusType;
use Acme\TerminalOmnibusBundle\Entity\AsientoBus;
use Acme\TerminalOmnibusBundle\Entity\TipoBus;
use Acme\TerminalOmnibusBundle\Entity\SenalBus;

class TipoBusCRUDController extends CRUDController {

    public function cargarListaAsientoDesdeHiddenHaciaObjeto(TipoBus $object)
    {             
        $listaActual = $object->getListaAsiento(); 
        $listaOkNotDeleted = array();
        $listaNewHidden = $object->getListaAsientoHidden();
        $listaNewJson = json_decode($listaNewHidden);   
        $nivel2 = false;
        foreach ($listaNewJson as $json) {
            $item = null;            
            $isNew = $this->isNewId($json->id);
            if(!$isNew) {
                $item = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:AsientoBus')->find($json->id);
                $listaOkNotDeleted[] = $item;
            }
            else{
                $item = new AsientoBus();
            }
            $item->setNivel2( $json->nivel2);
            $item->setNumero(intval($json->numero));
            $clase = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:ClaseAsiento')->find($json->clase);
            $item->setClase($clase);
            $item->setCoordenadaX(intval($json->coordenadaX));
            $item->setCoordenadaY(intval($json->coordenadaY));
            if($isNew) {
                $object->addListaAsiento($item);
            }
            if(!$nivel2 && $item->getNivel2() === true){
                $nivel2 = true;
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
        return array("nivel2" => $nivel2);
    }
    
    public function isNewId($id)
    {
        return ($id === null || trim($id) === "" || trim($id) === "0");
    }
    
    public function cargarListaAsientoDesdeObjetoHaciaHidden(TipoBus $object)
    {
        $listaJson = array();
        $lista = $object->getListaAsiento();
        foreach ($lista as $elemento) {
            $item = new \stdClass();
            $item->id = $elemento->getId();
            $item->nivel2 = $elemento->getNivel2();
            $item->numero = $elemento->getNumero();
            $item->clase = $elemento->getClase()->getId();
            $item->coordenadaX = $elemento->getCoordenadaX();
            $item->coordenadaY = $elemento->getCoordenadaY();
            $listaJson[] =  $item;
        }
        $object->setListaAsientoHidden(json_encode($listaJson));
    }
    
    public function cargarListaSenalDesdeHiddenHaciaObjeto(TipoBus $object)
    {
        $listaActual = $object->getListaSenal();    
        $listaOkNotDeleted = array();
        $listaNewHidden = $object->getListaSenalHidden();
        $listaNewJson = json_decode($listaNewHidden);  
        $nivel2 = false;
        foreach ($listaNewJson as $json) {
            $item = null;
            $isNew = $this->isNewId($json->id);
            if(!$isNew) {
                $item = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:SenalBus')->find($json->id); 
                $listaOkNotDeleted[] = $item;
            }
            else{
                $item = new SenalBus();
            }
            $item->setNivel2( $json->nivel2);
            $tipoSenal = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:TipoSenal')->find($json->tipo);
            $item->setTipo($tipoSenal);
            $item->setCoordenadaX(intval($json->coordenadaX));
            $item->setCoordenadaY(intval($json->coordenadaY));
            if($isNew) {
                $object->addListaSenal($item);
            }
            if(!$nivel2 && $item->getNivel2() === true){
                $nivel2 = true;
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
        
        return array("nivel2" => $nivel2);
    }
    
    public function cargarListaSenalDesdeObjetoHaciaHidden(TipoBus $object)
    {
        $listaJson = array();
        $lista = $object->getListaSenal();
        foreach ($lista as $elemento) {
            $item = new \stdClass();
            $item->id = $elemento->getId();
            $item->nivel2 = $elemento->getNivel2();
            $item->tipo = $elemento->getTipo()->getId();
            $item->coordenadaX = $elemento->getCoordenadaX();
            $item->coordenadaY = $elemento->getCoordenadaY();
            $listaJson[] =  $item;
        }
        $object->setListaSenalHidden(json_encode($listaJson));
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

        $this->cargarListaAsientoDesdeObjetoHaciaHidden($object); 
        $this->cargarListaSenalDesdeObjetoHaciaHidden($object);
        
        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }
        
        $this->admin->setSubject($object);
        
        $form = $this->createForm(new TipoBusType(), $object); 
        
        /** @var $form \Symfony\Component\Form\Form */
//        $form = $this->admin->getForm();
        $form->setData($object);

        if ($this->getRestMethod() == 'POST') {
            
            $commad = $this->get('request')->request->get("acme_backendbundle_tipobus_type");
            $object->setListaAsientoHidden($commad["listaAsientoHidden"]);
            $object->setListaSenalHidden($commad["listaSenalHidden"]);

            $result1 = $this->cargarListaAsientoDesdeHiddenHaciaObjeto($object);
            $result2 = $this->cargarListaSenalDesdeHiddenHaciaObjeto($object);
            if($result1["nivel2"] === true || $result2["nivel2"] === true ){
                $object->setNivel2(true);
            }else{
                $object->setNivel2(false);
            }
            
            $form->bind($this->get('request'));
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
//                
                $this->admin->update($object);
                
                if($object instanceof \Acme\BackendBundle\Entity\IJobSync){
                    if($object->isValidToSync()){
                        $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                        $jobSync->setNivel($object->getNivelSync());
                        $jobSync->setType($object->getTypeSync());
                        $jobSync->setUsuarioCreacion($this->getUser());
                        $jobSync->setData($object->getDataArrayToSync());
                        $this->get('acme_job_sync')->createJobSync($jobSync);
                    }
                }
                
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
        
        $this->cargarListaAsientoDesdeObjetoHaciaHidden($object); 
        $this->cargarListaSenalDesdeObjetoHaciaHidden($object); 
              
        $this->admin->setSubject($object);        
        
        $form = $this->createForm(new TipoBusType(), $object); 
        
        /** @var $form \Symfony\Component\Form\Form */
//        $form = $this->admin->getForm();
        $form->setData($object);

        if ($this->getRestMethod()== 'POST') {            
             
            $commad = $this->get('request')->request->get("acme_backendbundle_tipobus_type");
            $object->setListaAsientoHidden($commad["listaAsientoHidden"]);
            $object->setListaSenalHidden($commad["listaSenalHidden"]);

            $result1 = $this->cargarListaAsientoDesdeHiddenHaciaObjeto($object);
            $result2 = $this->cargarListaSenalDesdeHiddenHaciaObjeto($object);
            if($result1["nivel2"] === true || $result2["nivel2"] === true ){
                $object->setNivel2(true);
            }else{
                $object->setNivel2(false);
            }
            
            $form->bind($this->get('request'));
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {                
                
                $this->admin->create($object);

                if($object instanceof \Acme\BackendBundle\Entity\IJobSync){
                    if($object->isValidToSync()){
                        $jobSync = new \Acme\BackendBundle\Entity\JobSync();
                        $jobSync->setNivel($object->getNivelSync());
                        $jobSync->setType($object->getTypeSync());
                        $jobSync->setUsuarioCreacion($this->getUser());
                        $jobSync->setData($object->getDataArrayToSync());
                        $this->get('acme_job_sync')->createJobSync($jobSync);
                    }
                }
                
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
