<?php
namespace Puzzle\UserBundle\Security\Http\EntryPoint;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;


class XmlHttpRequestEntryPoint implements AuthenticationEntryPointInterface
{
	/**
	 * @var UrlGeneratorInterface $urlGenerator
	 */
	protected $urlGenerator;
	
	public function __construct(UrlGeneratorInterface $urlGenerator) {
		$this->urlGenerator = $urlGenerator;
	}
	
	public function start(Request $request, AuthenticationException $authException = null) {
		if (true === $request->isXmlHttpRequest()) {
			return new JsonResponse(JsonResponse::$statusTexts[401], 401);
		}
		
		return new RedirectResponse($this->urlGenerator->generate('login'));
	}
}
