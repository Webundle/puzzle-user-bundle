<?php
namespace Puzzle\UserBundle\Security\Http\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 *
 * @author Baidai Cedrick Oka <cedric.baidai@veone.net>
 *
 */
class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
	public function __construct(HttpKernel $httpKernel, HttpUtils $httpUtils, array $options = []) {
	    parent::__construct($httpKernel, $httpUtils, $options);	
	}
	
	public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
	    return parent::onAuthenticationFailure($request, $exception);
	}
}

