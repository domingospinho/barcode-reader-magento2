<?php
namespace Domingos\Pinho\Block;
use \Magento\Catalog\Model\Product\Type;
use Magento\Framework\App\RequestInterface;
use \Magento\Catalog\Block\Product\Context;
use \Magento\Catalog\Model\CategoryManagement;
use \Magento\Catalog\Api\ProductRepositoryInterface;
//use \Magento\Framework\View\Element\Template\Context;
use \Magento\Catalog\Api\Data\ProductInterfaceFactory;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Catalog\Model\Product\Attribute\Source\Status;
use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
class Helloworld extends \Magento\Framework\View\Element\Template
{
    /**
     * Request instance
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    protected $productFactory;
    protected $productRepository;
    protected $categoryManagement;
    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $_cartHelper;
    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        RequestInterface $request,
        Context $context,
        CategoryManagement $categoryManagement,
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        CollectionFactory $collectionFactory,
        array $data = [])
    {
        $this->request = $request;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->_cartHelper = $context->getCartHelper();
        $this->categoryManagement = $categoryManagement;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }
    public function getBarCode()
    {
        return $this->request->getParam('barcode');
    }
    public function getFormat()
    {
        return $this->request->getParam('format');
    }
    public function getType()
    {
        return $this->request->getParam('type');
    }
    /**
     * Retrieve form action
     *
     * @return string
     */
    public function getFormAction()
    {
        return '/helloworld/index/createpost';
    }
    /**
     * Retrieve cache action
     *
     * @return string
     */
    public function getCacheAction()
    {
        return sprintf(
            '/helloworld/index/cache?barcode=%s&format=%s&type=%s',
            $this->getBarCode(),
            $this->getFormat(),
            $this->getType()
        );
    }
    public function getNearProductAction($barcode)
    {
        return sprintf(
            '/helloworld/index/index?barcode=%s&format=%s&type=%s',
            $barcode, $this->getFormat(), $this->getType()
        );
    }
    public function getProduct()
    {
        $product = null;
        try
        {
            $sku = $this->getBarCode();
            $product = $this->productRepository->get($sku);
        }
        catch (NoSuchEntityException $e)
        {
        }
        return $product;
    }
    public function getNearProducts()
    {
        $found = false;
        $collection = array();
        $sku = (string)$this->getBarCode();
        for ($i = strlen($sku) - 1; $i > 3 && !$found; $i--) {
            $s = substr($sku, 0, $i);
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addAttributeToSelect('*');
            $collection->addFieldToFilter('sku', ["like" => $s.'%']);
            if ($collection->count() > 0) $found = true;
        }
        //$collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        //$collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        return $collection;
    }
    public function findNearProducts()//$sku = 2658081001508)  //  2658081001 782
    {
        $found = false;
        $result = array();
        $sku = (string)$this->getBarCode();
        //var_dump((string)$sku);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        //echo strlen($sku);
        //die();
        for ($i = strlen($sku) - 1; $i > 3 && !$found; $i--) {
            $s = substr($sku, 0, $i);
            $query = <<<SQL
                SELECT
                    sku
                FROM catalog_product_entity
                WHERE sku LIKE '$s%';
SQL;
            $result = $connection->fetchAll($query);
            if (!empty($result)) $found = true;
        }
        return $result;
    }
    public function getPrices($sku)
    {
        $file = BP.'/app/code/Domingos/Pinho/prices.json';
        $collection = json_decode(file_get_contents($file),true);
        if (empty($collection[$sku]))
            return null;
        if (empty($collection[$sku]['prices']))
            return null;
        return array_reverse($collection[$sku]['prices'], true);
    }
    /**
     * Retrieve url for add product to cart
     * Will return product view page URL if product has required options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = [])
    {
        if (!$product->getTypeInstance()->isPossibleBuyFromList($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = [];
            }
            $additional['_query']['options'] = 'cart';

            return $this->getProductUrl($product, $additional);
        }
        return $this->_cartHelper->getAddUrl($product, $additional);
    }

    /**
     * Retrieves url for form submitting.
     *
     * Some objects can use setSubmitRouteData() to set route and params for form submitting,
     * otherwise default url will be used
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     */
    public function getSubmitUrl($product, $additional = [])
    {
        $submitRouteData = $this->getData('submit_route_data');
        if ($submitRouteData) {
            $route = $submitRouteData['route'];
            $params = isset($submitRouteData['params']) ? $submitRouteData['params'] : [];
            $submitUrl = $this->getUrl($route, array_merge($params, $additional));
        } else {
            $submitUrl = $this->getAddToCartUrl($product, $additional);
        }
        return $submitUrl;
    }
    /**
     * Get Validation Rules for Quantity field
     *
     * @return array
     */
    public function getQuantityValidators()
    {
        $validators = [];
        $validators['required-number'] = true;
        return $validators;
    }
    public function getCategories()
    {
        $result = null;
        try {
            $result = $this->categoryManagement->getTree(2);
        } catch (\Exception $e) {
            $result = $e->getMessage();
        }
        return $result;
    }
}