<?php
/**
 * @package   com_simplerenew
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2014 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Simplerenew\Gateway\Recurly;

use Simplerenew\Exception;
use Simplerenew\Gateway\AbstractGatewayBase;

defined('_JEXEC') or die();

require_once __DIR__ . '/api/autoloader.php';

abstract class AbstractRecurlyBase extends AbstractGatewayBase
{
    /**
     * @var \Recurly_Client
     */
    protected $client = null;

    /**
     * @var string
     */
    protected $currency = 'USD';

    public function __construct(array $config = array())
    {
        parent::__construct($config);

        // Initialise the native Recurly API
        $mode = empty($this->gatewayConfig['mode']) ? 'test' : $this->gatewayConfig['mode'];

        if (!empty($this->gatewayConfig[$mode]['apikey'])) {
            $this->client = new \Recurly_Client($this->gatewayConfig[$mode]['apikey']);
        } else {
            throw new Exception('Recurly API requires an api key');
        }

        if (!empty($this->gatewayConfig['currency'])) {
            $this->currency = $this->gatewayConfig['currency'];
        }
    }

    protected function getCurrency(\Recurly_CurrencyList $amounts, $currency = null)
    {
        $currency = $currency ? : $this->currency;

        if (isset($amounts[$currency])) {
            $amount = $amounts[$currency]->amount_in_cents / 100;
            return $amount;
        }

        return 0;
    }
}
