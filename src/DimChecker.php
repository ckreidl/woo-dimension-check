<?
/**
 * wooDimensionCheck is a small utility I wrote to search a WooCommerce database via its REST API
 * to identify products that are lacking weight, height, length, or width. Missing values for those
 * items can break shipping calculators.
 * 
 * @package ckreidl/woo-dimension-check
 * @author Chris Kreidl
 * @license GPL 3.0
 * @license https://opensource.org/licenses/GPL-3.0
 * @version 0.1
 */

namespace ckreidl\WooDimensionCheck;

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

/**
 * A class to poll WooCommerce via REST API to identify products lacking a weight or any of the
 * three physical dimensions
 * 
 * @package ckreidl/woo-dimension-check
 * @author Chris Kreidl
 * @version $Revision 0.1 $
 * @access public
 * @see https://github.com/ckreidl/woo-dimension-check
 */
class DimChecker {
    /** 
     * @var array $missing[] - an array of arrays of products 
     */
    public $missing = [];

    /**
     * Create a new DimChecker
     * 
     * @param string $url The URL to your store
     * @param string $key WooCommerce REST API consumer key
     * @param string $secret WooCommerce REST API secret key
     * 
     * @return void
     */
    function __construct($url, $key, $secret) {
        $woocommerce = new Client($url, $key, $secret, [ 'version' => 'wc/v3' ]);

        try{
            // WooCommerce has a hard-limit of 100 items per request, so we need to scrape them
            // a page at a time.
             
            $i = 1;
            
            while($products = $woocommerce->get('products', ['per_page' => 100, 'page' => $i])) {
                foreach($products as $product) {
                    // WooCommerce doesn't return variations as a normal product, so we need to
                    // identify if the parent product has any and, if so, get them.
                    if(count($product->variations)) {
                        $variations = $woocommerce->get('products/' . $product->id . '/variations', ['per_page' => 100]);
                        foreach($variations as $variation) {
                            $this->check($variation);
                        }
                    }
                    $this->check($product);
                }
                $i++;
            }    
        } catch (HttpClientException $e) {
            return $e;
        }
    }

    /**
     * Fetch products missing a weight parameter
     * 
     * @return array 
     */
    public function weight() {
        return $this->missing['weight'];
    }

    /**
     * Fetch products missing a width parameter
     * 
     * @return array 
     */
    public function width() {
        return $this->missing['width'];
    }

     /**
     * Fetch products missing a height parameter
     * 
     * @return array 
     */
    public function height() {
        return $this->missing['height'];
    }

    /**
     * Fetch products missing a length parameter
     * 
     * @return array 
     */
    public function length() {
        return $this->missing['length'];
    }

    /**
     * Checks a product to determine if it has weight, height, width, and length attributes. If not,
     * add them to the appropriate array.
     * 
     * @param Product $product
     * 
     * @return void
     */
    private function check($product) {
        if($product->weight == '') {
            $this->missing['weight'][] = $product;
        }

        if($product->dimensions->height == '') {
            $this->missing['height'][] = $product;
        }

        if($product->dimensions->width == '') {
            $this->missing['width'][] = $product;
        }

        if($product->dimensions->length == '') {
            $this->missing['length'][] = $product;
        }
    }
}
?>
