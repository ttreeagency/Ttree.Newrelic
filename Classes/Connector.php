<?php
namespace NewRelic;


use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * An aspect which centralizes the logging of security relevant actions.
 *
 * @FLOW3\Scope("singleton")
 */
class Connector {

    /**
     * @var array
     */
    protected $settings;

    /**
     * Inject the settings
     *
     * @param array $settings
     *
     * @return void
     */
    public function injectSettings(array $settings) {
        $this->settings = $settings;
    }

    /**
     * @var  \TYPO3\FLOW3\Log\SystemLoggerInterface $systemLogger
     */
    protected $systemLogger;

    /**
     * @param \TYPO3\FLOW3\Log\SystemLoggerInterface $systemLogger
     *
     * @return void
     */
    public function injectSystemLogger(\TYPO3\FLOW3\Log\SystemLoggerInterface $systemLogger) {
        $this->systemLogger = $systemLogger;
    }

    public function logRequest(\TYPO3\FLOW3\MVC\RequestInterface $request)  {
        if(!extension_loaded('newrelic')) {
            if($this->settings['logOnMissingExtension']) {
                $this->systemLogger->log('newrelic extension missing - please install it');
            }
            if($this->settings['throwOnMissingExtension']) {
                throw new \Exception('newrelic extension missing - install it');
            }
        }
        if($request instanceof \TYPO3\FLOW3\MVC\Web\Request ) {
            return $this->logWebRequest($request);
        }
        if($request instanceof \TYPO3\FLOW3\MVC\CLI\Request ) {
            return $this->logCliRequest($request);
        }
        $this->systemLogger->log('Request of unknown type');
    }

    private function logWebRequest(\TYPO3\FLOW3\MVC\Web\Request $request) {
        $values = array();
        $values['{{FullControllerName}}'] = $request->getControllerObjectName();
        $values['{{ControllerName}}'] = $request->getControllerName();
        $values['{{Namespace}}'] = str_replace($values['{{ControllerName}}'], '', $request->getControllerObjectName());
        $values['{{ActionName}}'] = $request->getControllerActionName();
        $values['{{PackageKey}}'] = $request->getControllerPackageKey();
        $values['{{UrlPath}}'] = $request->getRequestUri()->getPath();
        $values['{{Format}}'] = $request->getFormat();

        $transactionName  =$this->formatTransactionName($this->getTransactionNameTemplate('Web'), $values);
        $this->handleTransactionName($transactionName);
    }

    private function logCliRequest(\TYPO3\FLOW3\MVC\CLI\Request $request) {
        $values = array();
        $values['{{FullControllerName}}'] = $request->getControllerObjectName();
        $values['{{CommandName}}'] = $request->getControllerCommandName();
        $values['{{ControllerName}}'] = \substr($request->getControllerObjectName(), \strrpos($request->getControllerObjectName(), '\\')+1 );
        $values['{{Namespace}}'] = str_replace($values['{{ControllerName}}'], '', $request->getControllerObjectName());
        $namespaceArray = explode('\\', $values['{{Namespace}}']);
        $values['{{LastNameSpaceElementBeforeCommand}}'] = $namespaceArray[count($namespaceArray)-3];


        $transactionName  = $this->formatTransactionName($this->getTransactionNameTemplate('Cli'), $values);

        $this->handleTransactionName($transactionName);
    }


    private function getTransactionNameTemplate($for='Web') {
        return $this->settings['transactionName']['template'][$for];
    }

    private function formatTransactionName($transactionNameTemplate, $values) {
        foreach($values as $key=>$value) {
            $transactionNameTemplate = str_replace($key, $value, $transactionNameTemplate);
        }
        return $transactionNameTemplate;
    }


    private function handleTransactionName($transactionName) {
        if($this->settings['transactionName']['log']) {
            $this->systemLogger->log($transactionName);
        }

        if($this->settings['transactionName']['send'] && extension_loaded('newrelic') ) {
            newrelic_name_transaction($transactionName);
        }
    }
}