<?php

namespace Puzzle\UserBundle\Controller;

use Puzzle\UserBundle\Entity\User;
use Puzzle\UserBundle\Entity\Group;
use Puzzle\UserBundle\Form\Type\UserCreateType;
use Puzzle\UserBundle\Form\Type\UserUpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Puzzle\UserBundle\Form\Type\GroupCreateType;
use Puzzle\UserBundle\Form\Type\GroupUpdateType;
use Puzzle\UserBundle\UserEvents;
use Puzzle\UserBundle\Event\UserEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Puzzle\UserBundle\Form\Type\UserChangeSettingsType;
use Puzzle\UserBundle\Form\Type\UserChangePasswordType;

class AdminController extends Controller
{
    /***
     * List users
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listUsersAction(Request $request) {
    	return $this->render("AdminBundle:User:list_users.html.twig", array(
    	    'users' => $this->getDoctrine()->getRepository(User::class)->findBy(['visible' => true])
    	));
    }
    
    /***
     * Show user
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showUserAction(Request $request, User $user) {
        $roles = [];
        $translator = $this->get('translator');
        
        foreach ($user->getRoles() as $role) {
            if ('ROLE_ACCOUNT' === $role) {
                $roles['ROLE_ACCOUNT'] =  $translator->trans('user.account.role', [], 'user');
            }else {
                $module = str_replace('role_', '', strtolower($role));
                $roles[] =  $translator->trans($module.'.role', [], $module);
            }
        }
        
        return $this->render("AdminBundle:User:show_user.html.twig", array(
            'user' => $user,
            'roles' => $roles
        ));
    }
    
    /***
     * Show user
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showCurrentUserProfileAction(Request $request) {
        $currentUser = $this->getUser();
        $roles = [];
        $translator = $this->get('translator');
        
        foreach ($currentUser->getRoles() as $role) {
            if ('ROLE_ACCOUNT' === $role) {
                $roles['ROLE_ACCOUNT'] =  $translator->trans('user.account.role', [], 'user');
            }else {
                $module = str_replace('role_', '', strtolower($role));
                $roles[] =  $translator->trans($module.'.role', [], $module);
            }
        }
        
        return $this->render("AdminBundle:User:show_user.html.twig", array(
            'user' => $currentUser,
            'roles' => $roles
        ));
    }
    
    /***
     * Create user
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createUserAction(Request $request){
        $user = new User();
        
        $moduleAvailables = explode(',', $this->getParameter('admin')['modules_available']);
        $translator = $this->get('translator');
        $roles = [];
        
        foreach ($moduleAvailables as $moduleAvailable) {
            $roles[$translator->trans($moduleAvailable.'.role', [], $moduleAvailable)] =  strtoupper('role_'.$moduleAvailable);
        }
        $roles[$translator->trans('user.account.role', [], 'user')] =  'ROLE_ACCOUNT';
        
        $form = $this->createForm(UserCreateType::class, $user, [
            'method' => 'POST',
            'action' => $this->generateUrl('puzzle_admin_user_create'),
            'roles' => $roles
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $data = $request->request->all()['puzzle_admin_user_create'];
            
            if (! empty($data['credentialsExpiresAt'])) {
                $user->setCredentialsExpiresAt(new \DateTime($data['credentialsExpiresAt']));
            }
            
            if (! empty($data['accountExpiresAt'])) {
                $user->setAccountExpiresAt(new \DateTime($data['accountExpiresAt']));
            }
            
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
                    'plainPassword' => $data['plainPassword']['first'],
                    'confirmationUrl' => $this->generateUrl('security_user_confirm_registration', ['token' => $user->getConfirmationToken()], UrlGeneratorInterface::ABSOLUTE_URL)
                ]));
            }
            
            $em->flush();
            
            $this->addFlash('success', $this->get('translator')->trans('user.account.create.success', [
                '%user%' => $user->getFullName()
            ], 'user'));
            return $this->redirectToRoute('puzzle_admin_user_show', ['id' => $user->getId()]);
        }
        
        return $this->render("AdminBundle:User:create_user.html.twig", [
            'form' => $form->createView(),
        ]);
    }
    
    /***
     * Update: user
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateUserAction(Request $request, $id) {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $em->find(User::class, $id);
        
        $moduleAvailables = explode(',', $this->getParameter('admin')['modules_available']);
        $translator = $this->get('translator');
        $roles = [];
        
        foreach ($moduleAvailables as $moduleAvailable) {
            $roles[$translator->trans($moduleAvailable.'.role', [], $moduleAvailable)] =  strtoupper('role_'.$moduleAvailable);
        }
        $roles[$translator->trans('user.account.role', [], 'user')] =  'ROLE_ACCOUNT';
        
        $form = $this->createForm(UserUpdateType::class, $user, [
            'method' => 'POST',
            'action' => $this->generateUrl('puzzle_admin_user_update', ['id' => $user->getId()]),
            'roles' => $roles
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $data = $request->request->all()['puzzle_admin_user_update'];
            
            // Update Security account
            if (! empty($data['credentialsExpiresAt'])) {
                $user->setCredentialsExpiresAt(new \DateTime($data['credentialsExpiresAt']));
            }else {
                $user->setCredentialsExpiresAt(null);
            }
            
            if (! empty($data['accountExpiresAt'])) {
                $user->setAccountExpiresAt(new \DateTime($data['accountExpiresAt']));
            }else {
                $user->setAccountExpiresAt(null);
            }
            
            $em->flush();
            
            // Update password
            if (isset($data['plainPassword']['first']) === true && $data['plainPassword']['first'] !== "") {
                /** User $user */
                $this->get('event_dispatcher')->dispatch(UserEvents::USER_UPDATING, new UserEvent($user, [
                    'plainPassword' => $data['plainPassword']['first']
                ]));
            }
            
            $em->flush();
            
            $this->addFlash('success', $this->get('translator')->trans('user.account.update.success', [
                '%user%' => $user->getFullName()
            ], 'user'));
            return $this->redirectToRoute('puzzle_admin_user_show', ['id' => $id]);
        }
        
        return $this->render("AdminBundle:User:update_user.html.twig", [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
    
    
    /***
     * Update: user settings
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateCurrentUserSettingsAction(Request $request) {
        /** @var User $user */
        $user = $this->getUser();
        
        $form = $this->createForm(UserChangeSettingsType::class, $user, [
            'method' => 'POST',
            'action' => $this->generateUrl('puzzle_admin_user_account_update_settings', ['id' => $user->getId()])
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            /** @var \Doctrine\ORM\EntityManager $em */
            $em = $this->get('doctrine.orm.entity_manager');
            $em->flush();
            
            $this->addFlash('success', $this->get('translator')->trans('user.account.update.success', ['%user%' => $user->getFullName()], 'user'));
            return $this->redirectToRoute('puzzle_admin_user_account_show_profile');
        }
        
        return $this->render("AdminBundle:User:update_user_settings.html.twig", [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
    
    /***
     * Update: user settings
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateCurrentUserPasswordAction(Request $request) {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $this->getUser();
        
        $form = $this->createForm(UserChangePasswordType::class, $user, [
            'method' => 'POST',
            'action' => $this->generateUrl('puzzle_admin_user_account_update_password', ['id' => $user->getId()])
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $data = $request->request->all()['user_change_password'];
            
            // Update password
            if (isset($data['plainPassword']['first']) === true && $data['plainPassword']['first'] !== "") {
                /** User $user */
                $this->get('event_dispatcher')->dispatch(UserEvents::USER_UPDATING, new UserEvent($user, [
                    'plainPassword' => $data['plainPassword']['first']
                ]));
            }
            
            $em->flush();
            
            $this->addFlash('success', $this->get('translator')->trans('user.account.update.success', ['%user%' => $user->getFullName()], 'user'));
            return $this->redirectToRoute('puzzle_admin_user_account_update_password');
        }
        
        return $this->render("AdminBundle:User:update_user_password.html.twig", [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
    
    public function enableUserAction(Request $request, $id) {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $em->find(User::class, $id);
        $user->setEnabled(true);
        
        $em->flush();
        
//         /** User $user */
        $this->get('event_dispatcher')->dispatch(UserEvents::USER_ENABLED, new UserEvent($user));
        
        $message = $this->get('translator')->trans('user.account.enable.success', [
            '%user%' => $user->getUsername()
        ], 'user');
        
        if ($request->isXmlHttpRequest() === true) {
            return new JsonResponse($message);
        }
        
        $this->addFlash('success', $message);
        return $this->redirectToRoute('puzzle_admin_user_show', ['id' => $id]);
    }
    
    public function disableUserAction(Request $request, $id) {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $em->find(User::class, $id);
        $user->setEnabled(false);
        
        $em->flush();
        
        /** User $user */
        $this->get('event_dispatcher')->dispatch(UserEvents::USER_DISABLED, new UserEvent($user));
        
        $message = $this->get('translator')->trans('user.account.disable.success', [
            '%user%' => $user->getUsername()
        ], 'user');
        
        if ($request->isXmlHttpRequest() === true) {
            return new JsonResponse($message);
        }
        
        $this->addFlash('success', $message);
        return $this->redirectToRoute('puzzle_admin_user_show', ['id' => $id]);
    }
    
    public function lockUserAction(Request $request, $id) {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $em->find(User::class, $id);
        $user->setLocked(true);
        
        $em->flush();
        
        /** User $user */
        $this->get('event_dispatcher')->dispatch(UserEvents::USER_LOCKED, new UserEvent($user));
        
        $message = $this->get('translator')->trans('user.account.lock.success', [
            '%user%' => $user->getUsername()
        ], 'user');
        
        if ($request->isXmlHttpRequest() === true) {
            return new JsonResponse($message);
        }
        
        $this->addFlash('success', $message);
        return $this->redirectToRoute('puzzle_admin_user_show', ['id' => $id]);
    }
    
    public function unlockUserAction(Request $request, $id) {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $em->find(User::class, $id);
        $user->setLocked(false);
        
        $em->flush();
        
        /** User $user */
        $this->get('event_dispatcher')->dispatch(UserEvents::USER_ENABLED, new UserEvent($user));
        
        $message = $this->get('translator')->trans('user.account.lock.success', [
            '%user%' => $user->getUsername()
        ], 'user');
        
        if ($request->isXmlHttpRequest() === true) {
            return new JsonResponse($message);
        }
        
        $this->addFlash('success', $message);
        return $this->redirectToRoute('puzzle_admin_user_show', ['id' => $id]);
    }
    
    /**
     * Delete a user
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteUserAction(Request $request, User $id) {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $em->find(User::class, $id);
        
        $message = $this->get('translator')->trans('user.account.delete.success', ['%user%' => (string) $user], 'user');
        
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();
        
        if ($request->isXmlHttpRequest() === true) {
            return new JsonResponse($message);
        }
        
        $this->addFlash('success', $message);
        return $this->redirectToRoute('puzzle_admin_user_list');
    }
    
    
    /***
     * Show groups
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listGroupsAction(Request $request) {
        return $this->render("AdminBundle:User:list_groups.html.twig", array(
            'groups' => $this->getDoctrine()->getRepository(Group::class)->findBy([], ['name' => 'ASC']),
        ));
    }
    
    /***
     * Show group
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showGroupAction(Request $request, Group $group) {
        return $this->render("AdminBundle:User:show_group.html.twig", array(
            'group' => $group,
            'users' => $group->getUsers()
        ));
    }
    
    /***
     * Create group
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createGroupAction(Request $request){
        $group = new Group();
        $form = $this->createForm(GroupCreateType::class, $group, [
            'method' => 'POST',
            'action' => $this->generateUrl('puzzle_admin_user_group_create')
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($group);
            $em->flush();
            
            $this->addFlash('success', $this->get('translator')->trans('user.group.create.success', ['%groupName%' => $group->getName()], 'user'));
            return $this->redirectToRoute('puzzle_admin_user_group_show', ['id' => $group->getId()]);
        }
        
        return $this->render("AdminBundle:User:create_group.html.twig", [
            'form' => $form->createView(),
        ]);
    }
    
    /***
     * Update group
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateGroupAction(Request $request, $id){
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $group = $em->find(Group::class, $id);
        
        $form = $this->createForm(GroupUpdateType::class, $group, [
            'method' => 'POST',
            'action' => $this->generateUrl('puzzle_admin_user_group_update', ['id' => $group->getId()])
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            
            $this->addFlash('success', $this->get('translator')->trans('user.group.update.success', ['%groupName%' => $group->getName()], 'user'));
            return $this->redirectToRoute('puzzle_admin_user_group_show', ['id' => $group->getId()]);
        }
        
        return $this->render("AdminBundle:User:update_group.html.twig", [
            'group' => $group,
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * Delete a group
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteGroupAction(Request $request, $id) {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $group = $em->find(Group::class, $id);
        
        $message = $this->get('translator')->trans('user.group.delete.success', ['%user%' => $group->getName()], 'user');
        
        $em = $this->getDoctrine()->getManager();
        $em->remove($group);
        $em->flush();
        
        if ($request->isXmlHttpRequest() === true) {
            return new JsonResponse($message);
        }
        
        $this->addFlash('success', $message);
        return $this->redirectToRoute('puzzle_admin_user_group_list');
    }
}
