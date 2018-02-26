<?php
namespace Domingos\Pinho\Controller\Index;
use Magento\Framework\App\Action\Context;
class Prices extends \Magento\Framework\App\Action\Action
{
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }
    public function execute()
    {

        //$tableName = $resource->getTableName('product'); //gives table name with prefix
        echo '<pre>';
        //echo 'sa';
        //Select Data from table


        //$sql = "Select * FROM catalog_product_entity;";// . $tableName;
        // gives associated array, table fields as key in array.
        //print_r($tableName);
        //echo '<br>';
        $this->_findNextProducts();
        //echo '<br>';
        //print_r($result);
        die();
        //Delete Data from table
        //$sql = "Delete FROM " . $tableName." Where emp_id = 10";
        //$connection->query($sql);
        //Insert Data into table
        //$sql = "Insert Into " . $tableName . " (emp_id, emp_name, emp_code, emp_salary) Values ('','XYZ','ABD20','50000')";
        //$connection->query($sql);
        //Update Data into table
        //$sql = "Update " . $tableName . "Set emp_salary = 20000 where emp_id = 12";
        //$connection->query($sql);

        $file = BP.'/app/code/Domingos/Pinho/prices.json';
        echo file_get_contents($file);
    }
}