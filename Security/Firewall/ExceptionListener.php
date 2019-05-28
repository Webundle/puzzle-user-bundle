<?php
namespace Puzzle\UserBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Firewall\ExceptionListener as BaseExceptionListener;

/**
 * 
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 *
 */
class ExceptionListener extends BaseExceptionListener
{
	protected function setTargetPath(Request $request) {
		if (true === $request->isXmlHttpRequest() || false === $request->isMethod('GET')) {
			return;
		}
		
		return parent::setTargetPath($request);
	}
}
