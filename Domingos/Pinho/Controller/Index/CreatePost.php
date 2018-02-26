<?php
namespace Domingos\Pinho\Controller\Index;
use \Magento\Catalog\Model\Product\Type;
use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;

use \Magento\Framework\View\Result\PageFactory;
use \Magento\Catalog\Api\ProductRepositoryInterface;
use \Magento\Catalog\Api\Data\ProductInterfaceFactory;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Catalog\Model\Product\Attribute\Source\Status;
class CreatePost extends Action
{
    /**
     * Request instance
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    protected $productRepository;

    protected $_resultPageFactory;
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,

        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        //\Magento\Framework\App\Cache\Manager $cacheManager,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList)
    {
        $this->_cacheTypeList = $cacheTypeList;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->_resultPageFactory = $resultPageFactory;

        parent::__construct($context);
    }
    /**
     *
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $post = (object)$this->getRequest()->getPostValue();
        if (empty($post->barcode)) {
            echo 'Invalid SKU.';
            return null;
        }
        $product = $this->getProduct($post->barcode);
        try {
            if (empty($product)) {
                echo 'Product does not exist.';
                /** @var ProductInterface $product */
                $product = $this->productFactory->create();
                $product
                    ->setVisibility(4)
                    ->setAttributeSetId(4)
                    ->setSku($post->barcode)
                    //->setUrlKey('aaaaaaaah')
                    ->setTypeId(Type::TYPE_SIMPLE)
                    ->setStatus(Status::STATUS_ENABLED);
            }
            //$test = '';
            $isQtyDecimal = 0;
            if (!empty($post->use_decimal_quantity)) {
                $isQtyDecimal = 1;
                //$test = 'dfgdfgdfgdfgdf';
            }
            $weight = 1;
            if (!empty($post->weight))
                $weight = $post->weight;
            $product
                ->setTaxClassId(0)
                ->setWeight($weight)
                ->setName($post->name)
                ->setPrice($post->price)
                ->setStockData(array(
                        'qty' => 9999999,
                        'is_in_stock' => 1,
                        'manage_stock' => 1,
                        'use_config_manage_stock' => 1,
                        'is_qty_decimal' => $isQtyDecimal,
                ))
                ->setBarcodeType($post->type)
                ->setBarcodeFormat($post->format)
                ->setCategoryIds($post->categories);
            // Adding Custom option to product
            $options = array(
                array(
                    "sort_order" => 1,
                    "title"      => "Pingas doces",
                    "price_type" => "fixed",
                    "price"      => "10",
                    "type"       => "field",
                    "is_require" => 0
                ),
                array(
                    "sort_order" => 2,
                    "title"      => "Peninsula",
                    "price_type" => "fixed",
                    "price"      => "20",
                    "type"       => "field",
                    "is_require" => 0
                )
            );
            //foreach ($options as $arrayOption) {
            //    $product->setHasOptions(1);
            //    $product->getResource()->save($product);
            //    $option = $objectManager->create('\Magento\Catalog\Model\Product\Option')
            //        ->setProductId($product->getId())
            //        ->setStoreId($product->getStoreId())
            //        ->addData($arrayOption);
            //    $option->save();
            //    $product->addOption($option);
            //}
            $this->productRepository->save($product);
            $this->messageManager->addSuccessMessage('Guardado com sucesso!');
            $this->ff($product);
            $this->_cacheTypeList->cleanType('full_page');
        } catch (\Exception $e) {
            echo $e->getMessage();
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        echo '<pre>';
        print_r($post);
        //get_class($resultRedirect);
        $resultRedirect->setPath(
            'helloworld/index/index',
            [
                'barcode' => $post->barcode,
                'format' => $post->format,
                'type' => $post->type
            ]
        );
        return $resultRedirect;
    }
    private function getProduct($sku)
    {
        $product = null;
        try {
            $product = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
        }
        return $product;
    }
    private function ff($product)
    {
        $collection = array();
        $file = BP.'/app/code/Domingos/Pinho/prices.json';
        if (file_exists($file))
            $collection = json_decode(file_get_contents($file),true);
        $sku = $product->getSku();
        if (empty($collection[$sku]))
            $collection[$sku] = array(
                'prices' => array(),
                'history' => array()
            );
        $price = (float)$product->getPrice();
        $last = end($collection[$sku]['prices']);
        if ($last === false || $last !== $price)
            $collection[$sku]['prices'][date('Y-m-d H:i')] = $price;
        $collection[$sku]['history'][] = sprintf('%s  -  %01.2f',date('Y-m-d H:i:s'), $price);
        file_put_contents($file, json_encode($collection,JSON_PRETTY_PRINT));
    }
}