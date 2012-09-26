<?php
namespace NewRelic\Aspect;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * An aspect which centralizes the logging of security relevant actions.
 *
 * @FLOW3\Scope("singleton")
 * @FLOW3\Aspect
 */
class NewRelicLoggerAspect
{

    /**
     * @FLOW3\Inject
     * @var \NewRelic\Connector
     *
     */
    protected $connector;

    /**
     * @var  \TYPO3\FLOW3\Log\SystemLoggerInterface $systemLogger
     */
    protected $systemLogger;

    /**
     * @param \TYPO3\FLOW3\Log\SystemLoggerInterface $systemLogger
     *
     * @return void
     */
    public function injectSystemLogger(\TYPO3\FLOW3\Log\SystemLoggerInterface $systemLogger)
    {
        $this->systemLogger = $systemLogger;
    }

    /**
     * Logs the current request in newrelic
     *
     * @FLOW3\Around("method(ArnsboMedia.*Controller->processRequest())")
     * @param \TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint The current joinpoint
     * @return mixed Result of the advice chain
     */
    public function logRequestInNewRelic(\TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint) {
        $request = $joinPoint->getMethodArgument('request');
        $this->connector->logRequest($request);
        return $joinPoint->getAdviceChain()->proceed($joinPoint);
    }
}