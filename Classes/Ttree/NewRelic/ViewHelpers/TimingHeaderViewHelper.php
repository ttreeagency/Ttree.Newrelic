<?php
namespace Ttree\NewRelic\ViewHelpers;

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
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * @api
 */
class TimingHeaderViewHelper extends AbstractViewHelper {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * @return string
	 */
	public function render() {
		if($this->settings['transactionName']['send'] && extension_loaded('newrelic') ) {
			$content = newrelic_get_browser_timing_header();
		} else {
			$content = '<!-- Newrelic Transaction Tracking Header is disabled -->';
		}

		return $content;
	}
}