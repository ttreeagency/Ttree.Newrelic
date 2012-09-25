<?php
namespace NewRelic;

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
class NewRelicLoggerAspect {

  /**
    * @var \NewRelic\Connector
    *
  */
  protected $connector;

  /**
   * Constructor
   *
   * @param \NewRelic\Connector $connector
   */
  public function __construct(\NewRelic\Connector $connector) {
    $this->connector = $connector;
  }


  /**
   * Logs the current request in newrelic
   *
   * @FLOW3\Around("method(TYPO3\FLOW3\MVC\Dispatcher->dispatch())")
   * @param \TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint The current joinpoint
   * @return mixed Result of the advice chain
   */
  public function logRequestInNewRelic(\TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint) {
    $request = $joinPoint->getMethodArgument('request');
    $this->connector->logRequest($request);
  }
}