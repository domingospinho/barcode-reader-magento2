<?php
namespace Domingos\Pinho\Controller\Index;
use Magento\Framework\App\Action\Context;
class Index extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
    public function __construct(Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    public function execute()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        //echo 'ola';
        //die();
        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;
    }
}