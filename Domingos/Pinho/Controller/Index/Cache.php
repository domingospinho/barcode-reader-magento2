<?php
namespace Domingos\Pinho\Controller\Index;
use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\App\Cache\TypeListInterface;
class Cache extends Action
{
    /**
     * Request instance
     *
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var TypeListInterface
     */
    protected $_cacheTypeList;
    /**
     * Constructor
     *
     * @param Context $context
     * @param RequestInterface $request
     * @param TypeListInterface $cacheTypeList
     */
    public function __construct(
        Context $context,
        RequestInterface $request,
        TypeListInterface $cacheTypeList
    )
    {
        $this->request = $request;
        parent::__construct($context);
        $this->_cacheTypeList = $cacheTypeList;
    }
    public function execute()
    {
        $types = array(
            'config',
            'layout',
            'block_html',
            'collections',
            'reflection',
            'db_ddl',
            'eav',
            'customer_notification',
            'config_integration',
            'config_integration_api',
            'full_page',
            'translate',
            'config_webservice',
        );
        foreach ($types as $index => $type) {
            $this->_cacheTypeList->cleanType($type);
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(
            'helloworld/index/index',
            [
                'barcode' => $this->getBarCode(),
                'format' => $this->getFormat(),
                'type' => $this->getType()
            ]
        );
        return $resultRedirect;
    }
    private function getBarCode()
    {
        return $this->request->getParam('barcode');
    }
    private function getFormat()
    {
        return $this->request->getParam('format');
    }
    private function getType()
    {
        return $this->request->getParam('type');
    }
}