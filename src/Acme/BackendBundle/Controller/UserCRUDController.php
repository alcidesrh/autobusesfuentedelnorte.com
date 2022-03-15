<?php

namespace Acme\BackendBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Acme\BackendBundle\Form\CreateUserType;
use Acme\BackendBundle\Form\EditUserType;
use Acme\BackendBundle\Form\ChangePasswordUserType;
use Acme\BackendBundle\Form\Model\CambiarContrasenaModel;

class UserCRUDController extends CRUDController {
 
    public function changePasswordAction()
    {
        // the key used to lookup the template
        $templateKey = 'changePassword';    
        
        $object = new CambiarContrasenaModel();
//
        $this->admin->setSubject($object);
        
        $form = $this->createForm(new ChangePasswordUserType($this->getDoctrine()), $object); 
        
        /** @var $form \Symfony\Component\Form\Form */
//        $form = $this->admin->getForm();
        $form->setData($object);

        if ($this->getRestMethod()== 'POST') {
            
            $form->bind($this->get('request'));
            $isFormValid = $form->isValid();
            
            $userManager = $this->container->get('fos_user.user_manager');
            $username = $form->get('username')->getData();
            $user = $userManager->findUserByUsernameOrEmail($username);
            if($user === null){
                $isFormValid = false;
                $form->addError(new \Symfony\Component\Form\FormError("El username no existe"));                
            }   
            
            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                
                $user->setPlainPassword($form->get('plainPassword')->getData());
                $user->setDateLastUdate(new \DateTime());
                $credentialsExpireAt = new \DateTime();
                $daysCredentialsExpire = $this->container->getParameter("days_credentials_expire");
                if(!$daysCredentialsExpire){ $daysCredentialsExpire = 90; }
                $credentialsExpireAt->modify("+" . $daysCredentialsExpire . " day");
                $user->setCredentialsExpireAt($credentialsExpireAt);
                $user->setCredentialsExpired(false);
                $userManager->updateUser($user);
                
                return $this->renderJson(array(
                        'result' => 'ok'
                ));

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
        return $this->render('AcmeBackendBundle:UserAdmin:changePassword.html.twig', array(
            'action' => 'changePassword',
            'form'   => $view,
            'object' => $object,
        ));
    }
    
    public function editAction($id = null)
    {
        // the key used to lookup the template
        $templateKey = 'edit';

        $id = $this->get('request')->get($this->admin->getIdParameter());
                
        $object = $this->admin->getObject($id);

        $this->admin->setSubject($object);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }
        
        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }
        
        $this->admin->setSubject($object);

        $form = $this->createForm(new EditUserType($this->getDoctrine()), $object); 
        
        /** @var $form \Symfony\Component\Form\Form */
//        $form = $this->admin->getForm();
        $form->setData($object);

        if ($this->getRestMethod() == 'POST') {
            
            $form->bind($this->get('request'));
            $isFormValid = $form->isValid();
            
            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
//                
//                $this->admin->update($object);
                $userManager = $this->container->get('fos_user.user_manager');
                $object->setDateLastUdate(new \DateTime());
                if($object->getExpired() === true){
                    $object->setExpiresAt(new \DateTime());
                }else{
                    $object->setExpiresAt(null);
                }
                $userManager->updateUser($object);
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
        $object->setEnabled(true);
        
        $this->admin->setSubject($object);
        
        $form = $this->createForm(new CreateUserType($this->getDoctrine()), $object); 
        
        /** @var $form \Symfony\Component\Form\Form */
//        $form = $this->admin->getForm();
        $form->setData($object);

        if ($this->getRestMethod()== 'POST') {
            
            $form->bind($this->get('request'));
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                
//                $this->admin->create($object);
                $userManager = $this->container->get('fos_user.user_manager');
                $dateCreate = new \DateTime();
                $object->setDateLastUdate($dateCreate);
                $object->setDateCreate($dateCreate);
                $credentialsExpireAt = new \DateTime();
                $daysCredentialsExpire = $this->container->getParameter("days_credentials_expire");
                if(!$daysCredentialsExpire){ $daysCredentialsExpire = 90; }
                $credentialsExpireAt->modify("+" . $daysCredentialsExpire . " day");
                $object->setCredentialsExpireAt($credentialsExpireAt);
                $object->setIntentosFallidos(0);
//                var_dump($object);
                $userManager->updateUser($object);
                
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
