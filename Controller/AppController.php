<?php

namespace Puzzle\UserBundle\Controller;

use Puzzle\UserBundle\Entity\User;
use Puzzle\MediaBundle\Util\MediaUtil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Puzzle\MediaBundle\MediaEvents;
use Puzzle\MediaBundle\Event\FileEvent;
use Puzzle\UserBundle\UserEvents;
use Puzzle\UserBundle\Event\UserEvent;
use Puzzle\UserBundle\Form\Type\UserChangeSettingsType;
use Puzzle\UserBundle\Form\Type\UserChangePasswordType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security as AnnotationSecurity;
use Symfony\Component\Security\Core\Security;
use Puzzle\UserBundle\Util\TokenGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;

class AppController extends Controller
{   
    /**
     * @param Request $request
     * @AnnotationSecurity("has_role('ROLE_USER')")
     */
    public function showUserProfileAction(Request $request) {
        return $this->render('AppBundle:User:show_user_profile.html.twig', ['user' => $this->getUser()]);
    }
    
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @AnnotationSecurity("has_role('ROLE_USER')")
     */
    public function updateUserSettingsAction(Request $request) {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $form = $this->createForm(UserChangeSettingsType::class, $currentUser, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_user_update_settings')
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Doctrine\ORM\EntityManager $em */
            $em = $this->get('doctrine.orm.default_entity_manager');
            $em->flush();
            
            $message = $this->get('translator')->trans('app.user.account.update.success', [
                '%user%' => $currentUser
            ], 'app');
            
            if ($request->isXmlHttpRequest() === true) {
                return new JsonResponse($message);
            }
            
            $this->addFlash('success', $message);
            
            return $this->redirect($this->generateUrl('app_user_show_profile'));
        }
        
        return $this->render('AppBundle:User:update_user_settings.html.twig', ['form' => $form->createView()]);
    }
    
    /**
     * @param Request $request
     * @AnnotationSecurity("is_granted('IS_AUTHENTICATED_FULLY') and has_role('ROLE_USER')")
     */
    public function changeUserPasswordAction(Request $request) {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $isPasswordChanged = $currentUser->isPasswordChanged();
        $form = $this->createForm(UserChangePasswordType::class, $currentUser, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_user_change_password')
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all()['user_change_password'];
            $currentPassword = $data['currentPassword'] ?? $request->request->get('currentPassword');
            
            /** @var \Doctrine\ORM\EntityManager $em */
            $em = $this->get('doctrine.orm.default_entity_manager');
            
            // Update password
            if (isset($data['plainPassword']['first']) === true && $data['plainPassword']['first'] !== "") {
                if ($currentPassword != $data['plainPassword']['first']) {
                    /** User $user */
                    $this->get('event_dispatcher')->dispatch(UserEvents::USER_PASSWORD, new UserEvent($currentUser, [
                        'plainPassword' => $data['plainPassword']['first']
                    ]));
                    $currentUser->setPasswordRequestedAt(null);
                    $currentUser->setConfirmationToken(null);
                    $currentUser->setPasswordChanged(true);
                    $em->flush();
                }
            }
            
            if ($uri = $request->getSession()->get('change_password.on_success.redirect_to')) {
                $request->getSession()->remove('change_password.on_success.redirect_to');
            } else {
                $uri = $this->generateUrl('app_user_show_profile');
            }
            
            $message = $this->get('translator')->trans('app.user.account.update_password.success', [
                '%user%' => $currentUser
            ], 'app');
            
            if ($request->isXmlHttpRequest() === true) {
                return new JsonResponse($message);
            }
            
            $this->addFlash('success', $message);
            
            return $this->redirect($uri);
        }
        
        return $this->render('AppBundle:User:change_user_password.html.twig', [
            'form' => $form->createView(),
            'isPasswordChanged' => $isPasswordChanged
        ]);
    }
    
    /***
     * Update user picture
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @AnnotationSecurity("is_granted('IS_AUTHENTICATED_FULLY') and has_role('ROLE_USER')")
     */
    public function updateUserPictureAction(Request $request) {
        $currentUser = $this->getUser();
        if ($request->isMethod('POST') === true) {
            $picture = $request->request->get('picture');
            
            if ($currentUser->getPicture() === null || $currentUser->getPicture() !== $picture) {
                $this->get('event_dispatcher')->dispatch(MediaEvents::COPY_FILE, new FileEvent([
                    'path' => $picture,
                    'context' => MediaUtil::extractContext(User::class),
                    'user' => $currentUser,
                    'closure' => function($filename) use ($currentUser) {
                    $currentUser->setPicture($filename);
                    }
                ]));
            }
            
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            
            $message = $this->get('translator')->trans('app.user.account.update_picture.success', [
                '%user%' => $currentUser
            ], 'app');
            
            if ($request->isXmlHttpRequest() === true) {
                return new JsonResponse($message);
            }
            
            $this->addFlash('success', $message);
            
            return $this->redirectToRoute('app_user_show_profile');
        }
        
        return $this->render("AdminBundle:User:update_user_picture.html.twig", [
            'user' => $currentUser,
        ]);
    }
}
