<?php
namespace Puzzle\UserBundle\Security\Http\Handler;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;

/**
 *
 * @author Baidai Cedrick Oka <cedric.baidai@veone.net>
 *
 */
class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
	/**
	 * @var UrlGeneratorInterface $urlGenerator
	 */
	protected $urlGenerator;
	
	public function __construct(UrlGeneratorInterface $urlGenerator, HttpUtils $httpUtils, array $options = []) {
		parent::__construct($httpUtils, $options);
		
		$this->urlGenerator = $urlGenerator;
	}
	
	public function onAuthenticationSuccess(Request $request, TokenInterface $token) {
		/** @var \ApiBundle\Entity\User $user */
		$user = $token->getUser();
		
// 		if (false === $user->isPasswordChanged()) {
// 			if (null !== ($targetPath = $this->getTargetPath($request->getSession(), $this->providerKey))) {
// 				$request->getSession()->set('change_password.on_success.redirect_to', $targetPath);
// 			}
			
// 			return new RedirectResponse($this->urlGenerator->generate('app_user_change_password'));
// 		}

		if ($redirectTo = $request->query->get('redirect_to')) {
		    return new RedirectResponse($redirectTo);
		}
		
		return parent::onAuthenticationSuccess($request, $token);
	}
}

