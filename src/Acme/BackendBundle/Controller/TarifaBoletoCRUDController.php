<?php

namespace Acme\BackendBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;

class TarifaBoletoCRUDController extends CRUDController {

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
            
            $object->setFechaCreacion(new \DateTime()); //Debe ser la fecha del sistema en DB
            $object->setUsuarioCreacion($this->getUser());
            
            $form->setData($object);
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
