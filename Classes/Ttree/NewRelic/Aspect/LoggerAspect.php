<?php
namespace Ttree\NewRelic\Aspect;

/*                                                                        *
 * This script belongs to the Flow framework.                             *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */
use TYPO3\Flow\Annotations as Flow;

/**
 * An aspect which centralizes the logging of security relevant actions.
 *
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class LoggerAspect {

	/**
	 * @Flow\Inject
	 * @var \Ttree\NewRelic\Connector
	 *
	 */
	protected $connector;

	/**
	 * @var  \TYPO3\Flow\Log\SystemLoggerInterface $systemLogger
	 */
	protected $systemLogger;

	/**
	 * @param \TYPO3\Flow\Log\SystemLoggerInterface $systemLogger
	 *
	 * @return void
	 */
	public function injectSystemLogger(\TYPO3\Flow\Log\SystemLoggerInterface $systemLogger) {
		$this->systemLogger = $systemLogger;
	}

	/**
	 * Logs the current request in newrelic
	 *
	 * @Flow\Around("method(Ttree.*Controller->processRequest())")
	 * @param \TYPO3\Flow\AOP\JoinPointInterface $joinPoint The current joinpoint
	 * @return mixed Result of the advice chain
	 */
	public function logRequestInNewRelic(\TYPO3\Flow\AOP\JoinPointInterface $joinPoint) {
		$request = $joinPoint->getMethodArgument('request');
		$this->connector->logRequest($request);

		return $joinPoint->getAdviceChain()->proceed($joinPoint);
	}
}