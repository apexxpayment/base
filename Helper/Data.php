<?php
/**
 * Custom payment method in Magento 2
 * @category    Base
 * @package     Apexx_Base
 */
namespace Apexx\Base\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface ;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Serialize\Serializer\Json as SerializeJson;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Header as HttpHeader;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Customer\Model\SessionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use \Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\Url\EncoderInterface;

/**
 * Class Data
 * @package Apexx\Base\Helper
 */
class Data extends AbstractHelper
{
    /**
     * Config paths
     */
    const XML_PATH_BASE_PAYMENT          = 'payment/apexx_section/apexxpayment/credentials';
    const XML_PATH_API_TYPE              = '/api_type';
    const XML_PATH_ATOMIC_API_ENDPOINT   = '/atomic_api_endpoint';
    const XML_PATH_AX2_API_ENDPOINT      = '/ax2_api_endpoint';
    const XML_PATH_MERCHNAT_API_KEY      = '/merchant_api_key';
    const XML_PATH_ACCOUNT_ID            = '/account_id';
    const XML_PATH_ORGANIZATION_ID       = '/organization_id';
    const XML_PATH_RETURN_URL            = '/redirect_url';
    const XML_PATH_PAYMENT_ACTION        = '/payment_action';
    const XML_PATH_DYNAMIC_DESCRIPTOR    = '/dynamic_descriptor';
    const XML_PATH_CURRANCY              = '/currency';
    const XML_PATH_CAPTURE_MODE          = '/capture_mode';
    const XML_PATH_PAYMENT_MODES         = '/payment_modes';
    const XML_PATH_PAYMENT_TYPE          = '/payment_type';
    const XML_PATH_RECURRING_TYPE        = '/recurring_type';
    const XML_PATH_PAYMENT_PTYPE         = '/payment_product_type';
    const XML_PATH_SHOPPER_INTERACTION   = '/shopper_interaction';
    const XML_PATH_BRAND_NAME            = '/brand_name';
    const XML_PATH_CUSTOMER_PAYPAL_ID    = '/customer_paypal_id';
    const ENCRYPT = 1;
    const DECRYPT = 2;
    const XML_PATH_SIGNATURE_KEY         = '/signature_key';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var SerializeJson
     */
    protected $serializeJson;

    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var Curl
     */
    protected $curlClient;

    /**
     * @var HttpHeader
     */
    protected $httpHeader;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchBuilder;

    /**
     * @var SessionFactory
     */
    protected $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var EncoderInterface
     */
    protected $urlEncoder;
    /**
     * @var DecoderInterface
     */
    protected $urlDecoder;

    /**
     * Data constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param JsonFactory $resultJsonFactory
     * @param SerializeJson $serializeJson
     * @param CurlFactory $curlFactory
     * @param Curl $curl
     * @param HttpHeader $httpHeader
     * @param OrderRepository $orderRepository
     * @param TransactionRepositoryInterface $transactionRepository
     * @param SearchCriteriaBuilder $searchBuilder
     * @param FilterBuilder $filterBuilder
     * @param SessionFactory $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        JsonFactory $resultJsonFactory,
        SerializeJson $serializeJson,
        curlFactory $curlFactory,
        Curl $curl,
        HttpHeader $httpHeader,
        OrderRepository $orderRepository,
        TransactionRepositoryInterface $transactionRepository,
        SearchCriteriaBuilder $searchBuilder,
        FilterBuilder $filterBuilder,
        SessionFactory $customerSession,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        EncoderInterface $urlEncoder,
        DecoderInterface $urlDecoder
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor ;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->serializeJson = $serializeJson;
        $this->curlFactory = $curlFactory;
        $this->curlClient = $curl;
        $this->httpHeader = $httpHeader;
        $this->orderRepository  = $orderRepository;
        $this->transactionRepository = $transactionRepository;
        $this->searchBuilder = $searchBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->urlEncoder = $urlEncoder;
        $this->urlDecoder = $urlDecoder;
    }

    /**
     * Get config value at the specified key
     *
     * @param string $key
     * @return mixed
     */
    public function getConfigValue($key)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_BASE_PAYMENT . $key,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getApiEndpoint()
    {
        $apiType = $this->getApiType();
        if ($apiType == 'Atomic') {
            return  $this->getConfigValue(self::XML_PATH_ATOMIC_API_ENDPOINT);
        } else {
            return $this->getConfigValue(self::XML_PATH_AX2_API_ENDPOINT);
        }
    }

    /**
     * @return string
     */
    public function getMerchantApiKey()
    {
        $merchantApiKey = $this->getConfigValue(self::XML_PATH_MERCHNAT_API_KEY);

        return $this->encryptor->decrypt($merchantApiKey);
    }

    /**
     * @return string
     */
    public function getAccountId()
    {
        $merchantApiKey = $this->getConfigValue(self::XML_PATH_ACCOUNT_ID);

        return $this->encryptor->decrypt($merchantApiKey);
    }

    /**
     * @return string
     */
    public function getOrganizationId()
    {
        $organizationId = $this->getConfigValue(self::XML_PATH_ORGANIZATION_ID);

        return $this->encryptor->decrypt($organizationId);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function getCustomCurl($url, $params)
    {
        try {
            $this->getCurlClient()->setHeaders(
                [
                    'Content-Type' => 'application/json',
                    'X-APIKEY' => $this->getMerchantApiKey(),
                ]
            );
            $this->getCurlClient()->post($url, $params);

            return $response = $this->getCurlClient()->getBody();
        }  catch (NoSuchEntityException $exception) {
            throw new LocalizedException(__($exception->getMessage()));
        }
    }

    /**
     * @return Curl
     */
    public function getCurlClient()
    {
        return $this->curlClient;
    }
    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->httpHeader->getHttpUserAgent();
    }

    /**
     * @return string
     */
    public function getStoreLocale()
    {
        return $this->scopeConfig->getValue(
            'general/locale/code',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getStoreBaseCurrency()
    {
        return $this->scopeConfig->getValue(
            'currency/options/base',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $orderId
     * @return string
     */
    public function getHostedPayTxnId($orderId)
    {
        $txnId = '';
        $this->searchBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('order_id')
                    ->setValue($orderId)
                    ->create(),
            ]
        );

        $this->searchBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('txn_type')
                    ->setValue(TransactionInterface::TYPE_AUTH)
                    ->create(),
            ]
        );

        $searchCriteria = $this->searchBuilder->create();
        $count = $this->transactionRepository->getList($searchCriteria)->getTotalCount();
        if ($count > 0) {
            $transactionList = $this->transactionRepository->getList($searchCriteria);
            foreach ($transactionList->getItems() as $transaction) {
                $txnId = $transaction->getTxnId();
            }
        }

        return $txnId;
    }
    public function getCcTypesList($cardbrand)
    {
        $cardList = ['visa'=>'VI', 'mastercard'=>'MC', 'jcb'=>'JCB', 'diners club international'=>'DN', 'diners'=>'DN', 'american express'=>'AE', 'maestro'=>'MD'];

        if (array_key_exists($cardbrand, $cardList)) {
            return $cardList[$cardbrand];
        } else {
            return "OT";
        }
    }

    /**
     * Get Store code
     *
     * @return string
     */
    public function getStoreCode()
    {
        return $this->storeManager->getStore()->getCode();
    }

    /**
     * Get Store Url
     *
     * @return string
     */
    public function getStoreUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    /**
     * @param $action
     * @param $string
     * @return bool|string
     */
    public function encryptDecrypt($action, $string)
    {
        //sending merchant_id as it is without encryption as per client's requirement
        return $string;

        $output = false;
        $pymentStr = 'hosted';
        if ($action == self::ENCRYPT) {
            $output = $this->urlEncoder->encode($pymentStr.$string);
        } elseif ($action == self::DECRYPT) {
            $output = $this->urlDecoder->decode($string);
        }
        return $output;
    }

    /**
     * @return string
     */
    public function getSignatureKey()
    {
        $signatureKey = $this->getConfigValue(self::XML_PATH_SIGNATURE_KEY);

        return $this->encryptor->decrypt($signatureKey);
    }

    public function signatureEncryptDecrypt($method, $string){
        $secret = $this->getSignatureKey();

        if($method == self::ENCRYPT){
            $enc_key = hash_hmac('sha512', $string, $secret, true);

            return base64_encode($enc_key);
        }
        if($method == self::DECRYPT){
            $enc_key = hash_hmac('sha512', $string, $secret, true);

            return base64_decode($string);
        }
    }

    /**
     * @return mixed
     */
    public function getApiType()
    {
        return $this->getConfigValue(self::XML_PATH_API_TYPE);
    }
}
