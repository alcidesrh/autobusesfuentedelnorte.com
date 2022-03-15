<?php

namespace Acme\BackendBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;

class ItinerarioEspecialCRUDController extends CRUDController {
 
    
    public function editAction($id = null)
    {
        // the key used to lookup the template
        $templateKey = 'edit';

        $id = $this->get('request')->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
       

        if ($this->getRestMethod() == 'POST') {
            
            $form->setData($object);
            $form->bind($this->get('request'));

            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                
                $this->get('acme_backend_salida')->procesarSalidaPorItinerarioEspecial($object, array(
                    'user' => $this->getUser()
                ));
                
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
        }else{
            $form->setData($object);
        }
        
        $view = $form->createView();

        // set the theme for the current Admin Form
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

        $this->admin->setSubject($object);

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        
        if ($this->getRestMethod()== 'POST') {

            $form->setData($object);
            $form->bind($this->get('request'));
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {

                $this->admin->create($object);
                
                $this->get('acme_backend_salida')->procesarSalidaPorItinerarioEspecial($object, array(
                    'user' => $this->getUser()
                ));
                
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
            } elseif ($this->isPreviewRequested()) {
                // pick the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }else{
            $form->setData($object);
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        return $this->render($this->admin->getTemplate($templateKey), array(
            'action' => 'create',
            'form'   => $view,
            'object' => $object,
        ));
    }
}

?>
