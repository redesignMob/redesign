<?php

class Redesign_Quickorder_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function childrenAction() {
        $category_id = $this->getRequest()->getParam('id',0);
        $category = Mage::getModel('catalog/category')->load($category_id);
        $children_categories = Mage::getModel('catalog/category')->getResource()->getAllChildren($category);
        
        if(count($children_categories) && $children_categories != array($category_id)) {
            $data = array();
            foreach($children_categories as $cid) {
                if($cid != $category_id) {
                    $data[] = Mage::getModel('catalog/category')->load($cid);
                }
            }
            $template = 'quickorder/categories.phtml';
        } else {
            $data = $category->getProductCollection()
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('visibility',Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
                        ->addAttributeToFilter('type_id',  Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);
            $template = 'quickorder/products.phtml';
        }
        
        echo $this->getLayout()->createBlock('quickorder/products')
                        ->setQuickorderData($data)
                        ->setTemplate($template)
                        ->toHtml();
    }
    
    public function addAction() {
        try{
            $product_id = $this->getRequest()->getParam('product_id',0);
            $qty = 1;
            
            $product = Mage::getModel('catalog/product')->load($product_id);

            $session = Mage::getSingleton('core/session', array('name'=>'frontend'));
            $cart = Mage::helper('checkout/cart')->getCart();

            $cart->addProduct($product, $qty);

            $session->setLastAddedProductId($product->getId());
            $session->setCartWasUpdated(true);

            $cart->save();

            $result = "success";
            echo $result;

        } catch (Exception $e) {
            $result = $e->getMessage();
            echo $result;
        }
    }
}