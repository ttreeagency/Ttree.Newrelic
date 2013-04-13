<?php
namespace Ttree\NewRelic\Error;

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
use TYPO3\Flow\Http\Response;

/**
 * A basic but solid exception handler which catches everything which
 * falls through the other exception handlers and provides useful debugging
 * information.
 *
 * @Flow\Scope("singleton")
 */
class DebugExceptionHandler extends \TYPO3\Flow\Error\DebugExceptionHandler {

    /**
     * @param \Exception $exception
     */
    public function handleException(\Exception $exception) {
        \Ttree\NewRelic\Connector::logException($exception);

        return parent::handleException($exception);
    }

}
?>