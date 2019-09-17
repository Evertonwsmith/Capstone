<?php
require_once 'my_sql.php';
require_once 'product.php';

class general_item
{
    private $product_id, $media_group_id, $quantity;
    private $product;

    public function __construct($product_id, $media_group_id, $quantity)
    {
        $this->product_id = $product_id;
        $this->media_group_id = $media_group_id;
        $this->quantity = $quantity;
        //Create a new product object
        $this->product = new product($product_id);
    }

    /********************
     * Define the getters
     * ******************
     */

    public function get_product_id()
    {
        return $this->product_id;
    }

    public function get_media_group_id()
    {
        return $this->media_group_id;
    }

    public function get_quantity()
    {
        return $this->quantity;
    }

    public function get_product()
    {
        return $this->product;
    }

    /********************
     * Define the setters
     * ******************
     */
    
    public function set_quantity($quantity)
    {
        if (isset($quantity)) 
        {
            $this->quantity = $quantity;
        } else 
        {
            $this->quantity = 0;
        }
    }
}
