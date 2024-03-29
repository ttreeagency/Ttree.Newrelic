<?php
namespace Ttree\NewRelic;


use TYPO3\Flow\Annotations as Flow;

/**
 * NewRelic Service Connector
 *
 * @Flow\Scope("singleton")
 */
class Connector {

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var  \TYPO3\Flow\Log\SystemLoggerInterface $systemLogger
     */
    protected $systemLogger;

    /**
     * @var array
     */
    protected $transactionCache = array();

    /**
     * @param array $settings
     * @return void
     */
    public function injectSettings(array $settings) {
        $this->settings = $settings;
    }

    /**
     * @param \TYPO3\Flow\Log\SystemLoggerInterface $systemLogger
     * @return void
     */
    public function injectSystemLogger(\TYPO3\Flow\Log\SystemLoggerInterface $systemLogger) {
        $this->systemLogger = $systemLogger;
    }

	/**
	 * @param string $applicationName
	 */
	public function setApplicationName($applicationName = NULL) {
		if ($this->isNewRelicExtensionIsLoaded()) {
			newrelic_set_appname($applicationName ?: $this->settings['applicationName']);
		}
	}

    /**
     * @param \Exception $exception
     * @throws \Exception
     */
    public static function logException(\Exception $exception) {
        if (extension_loaded('newrelic')) {
            newrelic_notice_error($exception->getCode() . ' - ' . $exception->getMessage(), $exception);
        }
    }

    /**
     * @param \TYPO3\Flow\Mvc\RequestInterface $request
     * @throws \Exception
     */
    public function logRequest(\TYPO3\Flow\Mvc\RequestInterface $request) {
        if ($request instanceof \TYPO3\Flow\Mvc\ActionRequest) {
            $this->logWebRequest($request);
        } elseif ($request instanceof \TYPO3\Flow\Cli\Request) {
            $this->logCliRequest($request);
        } else {
            $this->systemLogger->log('Request of unknown type', LOG_ERR);
        }
    }

    /**
     * @param \TYPO3\Flow\Mvc\ActionRequest $request
     */
    private function logWebRequest(\TYPO3\Flow\Mvc\ActionRequest $request) {
        $values                           = array();
        $values['{{FullControllerName}}'] = $request->getControllerObjectName();
        $values['{{ControllerName}}']     = $request->getControllerName();
        $values['{{Namespace}}']          = str_replace($values['{{ControllerName}}'], '', $request->getControllerObjectName());
        $values['{{ActionName}}']         = $request->getControllerActionName();
        $values['{{PackageKey}}']         = $request->getControllerPackageKey();
        $values['{{UrlPath}}']            = $request->getHttpRequest()->getUri()->getPath();
        $values['{{Format}}']             = $request->getFormat();

        $transactionName = $this->formatTransactionName($this->getTransactionNameTemplate('Web'), $values);
        $this->handleTransactionName($transactionName);
    }

    /**
     * @param string $transactionNameTemplate
     * @param array $values
     * @return mixed
     */
    private function formatTransactionName($transactionNameTemplate, array $values) {
        foreach ($values as $key => $value) {
            $transactionNameTemplate = str_replace($key, $value, $transactionNameTemplate);
        }

        return $transactionNameTemplate;
    }

    /**
     * @param string $for
     * @return string
     */
    private function getTransactionNameTemplate($for = 'Web') {
        return $this->settings['transactionName']['template'][$for];
    }

    /**
     * @param string $transactionName
     */
    private function handleTransactionName($transactionName) {
        if (!empty($this->transactionCache[$transactionName])) {
            return;
        }
        if ($this->settings['transactionName']['send'] && $this->isNewRelicExtensionIsLoaded()) {
            newrelic_name_transaction($transactionName, LOG_DEBUG);
        }
        if ($this->settings['transactionName']['log']) {
            $this->systemLogger->log($transactionName, LOG_DEBUG);
        }
        $this->transactionCache[$transactionName] = TRUE;
    }

    /**
     * @param \TYPO3\Flow\Cli\Request $request
     */
    private function logCliRequest(\TYPO3\Flow\Cli\Request $request) {
        $values                                          = array();
        $values['{{FullControllerName}}']                = $request->getControllerObjectName();
        $values['{{CommandName}}']                       = $request->getControllerCommandName();
        $values['{{ControllerName}}']                    = \substr($request->getControllerObjectName(), \strrpos($request->getControllerObjectName(), '\\') + 1);
        $values['{{Namespace}}']                         = str_replace($values['{{ControllerName}}'], '', $request->getControllerObjectName());
        $namespaceArray                                  = explode('\\', $values['{{Namespace}}']);
        $values['{{LastNameSpaceElementBeforeCommand}}'] = $namespaceArray[count($namespaceArray) - 3];


        $transactionName = $this->formatTransactionName($this->getTransactionNameTemplate('Cli'), $values);

        $this->handleTransactionName($transactionName);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function isNewRelicExtensionIsLoaded() {
        $loaded = TRUE;
        if (!extension_loaded('newrelic')) {
            if ($this->settings['logOnMissingExtension']) {
                $this->systemLogger->log('newrelic extension missing - please install it', LOG_DEBUG);
            }
            if ($this->settings['throwOnMissingExtension']) {
                throw new \Exception('newrelic extension missing - install it', 1365444902);
            }
            $loaded = FALSE;
        }

        return $loaded;
    }
}