<?php

namespace Puzzle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Puzzle\UserBundle\UserEvents;
use Puzzle\UserBundle\Entity\User;
use Puzzle\UserBundle\Event\UserEvent;
use Puzzle\UserBundle\Form\Type\UserRegisterType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Puzzle\UserBundle\Form\Type\UserResetPasswordType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Puzzle\UserBundle\Util\TokenGenerator;

/**
 * 
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 *
 */
class SecurityController extends Controller
{
    
    /**
     * Login: app
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction(Request $request)
    {
        if ($request->getHost() === $this->getParameter("admin_host") || $request->query->get('scope') === 'admin') {
            return $this->redirect($this->generateUrl('admin_login'));
        }
        
        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $request->getSession();
        
        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(Security::AUTHENTICATION_ERROR)) {
            $error = $session->get(Security::AUTHENTICATION_ERROR);
            $session->remove(Security::AUTHENTICATION_ERROR);
        } else {
            $error = null;
        }
        
        if (!$error instanceof AuthenticationException) {
            $error = null;
        }
        
        $lastUsername = (null === $session) ? '' : $session->get(Security::LAST_USERNAME);
        
        $csrfToken = $this->has('form.csrf_provider')
        ? $this->get('form.csrf_provider')->generateCsrfToken('authenticate')
        : null;
        
        return $this->render('AppBundle:User:login.html.twig', array(
            'last_username'  => $lastUsername,
            'error'          => $error,
            'csrf_token'     => $csrfToken
        ));
    }
    
    
    /**
     * Login: admin
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adminLoginAction(Request $request)
    {
        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $request->getSession();
        
        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(Security::AUTHENTICATION_ERROR)) {
            $error = $session->get(Security::AUTHENTICATION_ERROR);
            $session->remove(Security::AUTHENTICATION_ERROR);
        } else {
            $error = null;
        }
        
        if (!$error instanceof AuthenticationException) {
            $error = null;
        }
        
        $lastUsername = (null === $session) ? '' : $session->get(Security::LAST_USERNAME);
        
        $csrfToken = $this->has('form.csrf_provider')
        ? $this->get('form.csrf_provider')->generateCsrfToken('authenticate')
        : null;
        
        return $this->render('AdminBundle:User:login.html.twig', array(
            'last_username'  => $lastUsername,
            'error'          => $error,
            'csrf_token'     => $csrfToken
        ));
    }
    
    /***
     * Create User
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Request $request){
        $user = new User();
        $form = $this->createForm(UserRegisterType::class, $user, [
            'method' => 'POST',
            'action' => $this->generateUrl('register')
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $data = $request->request->all()['app_user_register'];
            
            $user->addRole(User::ROLE_DEFAULT);
            $user->setEnabled(false);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            
            /** User $user */
            $this->get('event_dispatcher')->dispatch(UserEvents::USER_CREATING, new UserEvent($user, [
                'plainPassword' => $data['plainPassword']['first']
            ]));
            
            if ($this->getParameter('user.registration.confirmation_link') === true) {
                /** User $user */
                $this->get('event_dispatcher')->dispatch(UserEvents::USER_CREATED, new UserEvent($user, [
                    'plainPassword' => $user->getPlainPassword(),
                    'confirmationUrl' => $this->generateUrl('security_user_confirm_registration', ['token' => $user->getConfirmationToken()], UrlGeneratorInterface::ABSOLUTE_URL)
                ]));
            }
            
            if (!$redirectUri = $this->getParameter('user.registration.redirect_uri')) {
                $redirectUri = $this->generateUrl('security_check_user_registration', ['email' => $user->getEmail()]);
            }
            
            $em->flush();
            return $this->redirect($redirectUri);
        }
        
        return $this->render("AppBundle:User:register.html.twig", [
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * @param Request $request
     * @param string $token
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkUserRegistrationAction(Request $request, $email) {
        return $this->render('AppBundle:User:check_user_registration.html.twig', ['email' => (string) $email]);
    }
    
    /**
     * Confirm user registration
     *
     * @param Request $request
     * @param mixed $token
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmUserRegistrationAction(Request $request, $token) {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.default_entity_manager');
        /** @var \ApiBundle\Repository\UserRepository $er */
        $er = $em->getRepository(User::class);
        
        /** @var User $user */
        if (!$user = $er->findOneBy(['confirmationToken' => $token])) {
            throw $this->createNotFoundException();
        }
        
        $user->setConfirmationToken(null);
        $user->setEnabled(true);
        $em->flush();
        
        if ($request->getHost() === $this->getParameter("admin_host") || $request->query->get('scope') === 'admin') {
            $this->saveTargetPath($request->getSession(), 'admin', $this->generateUrl('admin_user_change_password', [], UrlGeneratorInterface::ABSOLUTE_URL));
            return $this->render('AdminBundle:User:confirm_user_registration.html.twig', ['username' => (string) $user]);
        }
        
        return $this->render('AppBundle:User:confirm_user_registration.html.twig', ['username' => (string) $user]);
    }
    
    /**
     * Resetting reset user password: send email
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function resettingSendEmailAction(Request $request) {
        if ($username = $request->request->get('username')) {
            /** @var \Doctrine\ORM\EntityManager $em */
            $em = $this->get('doctrine.orm.default_entity_manager');
            /** @var \ApiBundle\Repository\UserRepository $er */
            $er = $em->getRepository(User::class);
            /** @var User $user */
            $user = $er->loadUserByUsername($username);
            
            if (null !== $user && false === $user->isPasswordRequestNonExpired($this->getParameter('app.resetting.retry_ttl'))) {
                if (null === $user->getConfirmationToken()) {
                    $user->setConfirmationToken(TokenGenerator::generate(12));
                }
                
                /** @var \Symfony\Component\Translation\TranslatorInterface $translator */
                $translator = $this->get('translator');
                $subject = $translator->trans('app.user.resetting.email.subject', [], 'app');
                $body = $this->renderView('AppBundle:User:user_resetting_email.txt.twig', [
                    'username' => $username,
                    'confirmationUrl' => $this->generateUrl('security_user_resetting_reset', ['token' => $user->getConfirmationToken()], UrlGeneratorInterface::ABSOLUTE_URL)
                ]);
                $this->sendEmail($this->getParameter('app.resetting.address'), $user->getEmail(), $subject, $body);
                
                $user->setPasswordRequestedAt(new \DateTime());
                $em->flush();
            }
        }
        
        return new RedirectResponse($this->generateUrl('security_user_resetting_check_email', ['username' => $username]));
    }
    
    /**
     * Resetting reset user password: check email
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function resettingCheckEmailAction(Request $request) {
        if (!$request->query->get('username')) {
            return new RedirectResponse($this->generateUrl('security_user_resetting_request'));
        }
        
        return $this->render('AppBundle:User:resetting_check_email.html.twig', [
            'tokenLifetime' => ceil($this->getParameter('app.resetting.retry_ttl') / 3600)
        ]);
    }
    
    /**
     * Resetting reset user password: show form
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resettingResetAction(Request $request, $token) {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.default_entity_manager');
        /** @var \ApiBundle\Repository\UserRepository $er */
        $er = $em->getRepository(User::class);
        
        /** @var User $user */
        if (!$user = $er->findOneBy(['confirmationToken' => $token])) {
            throw $this->createNotFoundException();
        }
        
        $form = $this->createForm(UserResetPasswordType::class, $user, [
            'method' => 'POST',
            'action' => $this->generateUrl('security_user_resetting_reset', ['token' => $token])
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPasswordRequestedAt(null);
            $user->setConfirmationToken(null);
            $user->setPasswordChanged(true);
            $user->setEnabled(true);
            $em->flush();
            
            return $this->redirect($this->generateUrl('login'));
        }
        
        return $this->render('AppBundle:User:resetting_reset.html.twig', ['form' => $form->createView()]);
    }
    
    private function sendEmail($from, $to, string $subject, string $body) {
        $message = \Swift_Message::newInstance()
        ->setFrom($from)
        ->setTo($to)
        ->setSubject($subject)
        ->setBody($body);
        
        $this->get('mailer')->send($message);
    }
}
