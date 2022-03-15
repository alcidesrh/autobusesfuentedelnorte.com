<?php

namespace Acme\BackendBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Acme\TerminalOmnibusBundle\Entity\AutorizacionCortesia;
use Symfony\Component\HttpFoundation\Response;
use Acme\TerminalOmnibusBundle\Entity\EstadoBoleto;
use Acme\TerminalOmnibusBundle\Entity\TipoDocumentoBoleto;

class AutorizacionCortesiaCRUDController extends CRUDController {
 
    public function generatePinAction() {
        return new Response($this->get('acme_backend_util')->generatePin());
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

        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        /** @var $form \Symfony\Component\Form\Form */

        if ($this->getRestMethod() == 'POST') {
            
            $form = $this->admin->getForm();
            $form->setData($object);
            $form->bind($this->get('request'));

            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                
                $fechaUso = $object->getRestriccionFechaUso();
                if(is_string($fechaUso) && $fechaUso !== null && trim($fechaUso) === "" ){
                   $object->setRestriccionFechaUso(null); 
                }
                
                $this->admin->update($object);
                
                if($object->getNotificarCliente() === true){
                    $this->sendEmail($object, 'update');
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
//            var_dump("xxx1");
            $form = $this->admin->getForm();
//            var_dump("xxx2");
            $form->setData($object);
//            var_dump("xxx3");
        }
//        var_dump("xxx4");
        $view = $form->createView();
//        var_dump("xxx5");
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
        
        if ($this->getRestMethod()== 'POST') {
            
            $object->setFechaCreacion(new \DateTime()); //Debe ser la fecha del sistema en DB
            $object->setUsuarioCreacion($this->getUser());
            $form = $this->admin->getForm();
            $form->setData($object);
            $form->bind($this->get('request'));
            $isFormValid = $form->isValid();
            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                
                $fechaUso = $object->getRestriccionFechaUso();
                if(is_string($fechaUso) && $fechaUso !== null && trim($fechaUso) === "" ){
                   $object->setRestriccionFechaUso(null); 
                }
                
                $this->admin->create($object);
                
                if($object->getNotificarCliente() === true){
                    $this->sendEmail($object, 'create');
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
            $form = $this->admin->getForm();
            $ping = $this->get('acme_backend_util')->generatePin();
            $object->setCodigo($ping);
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

    public function sendEmail(AutorizacionCortesia $object, $action)
    {
        $servicio = $object->getServicioEstacion()->getId();
        $estaciones = $this->getDoctrine()->getRepository('AcmeTerminalOmnibusBundle:Estacion')->getAllEstacionesActivas($servicio);
         $message = \Swift_Message::newInstance()
            ->setSubject($this->container->getParameter("email_autorizacion_cortesia_asunto"))
            ->setFrom($this->container->getParameter("mailer_user"))
            ->setTo($object->getRestriccionCliente()->getCorreo())
            ->setBody( $this->renderView('AcmeBackendBundle:Email:autorizacion_cortesia.html.twig', array(
                'object' => $object,
                'estaciones' => $estaciones,
                'action' => $action 
            )));
          $this->get('mailer')->send($message);
    }
}

?>
