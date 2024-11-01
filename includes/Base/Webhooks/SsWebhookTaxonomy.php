<?php

namespace Socialshop\Base\Webhooks;

use Socialshop\Base\SsLogger;

class SsWebhookTaxonomy extends SsWebhook{

    public function __construct( )
    {
        parent::__construct();
        $this->ssRequest->setApiUrl('/update_configuration');
    }

    function register(){
        add_action('edited_term',  array($this, 'update'),5, 3);
        add_action('created_term', array($this, 'create'),5, 3);
        add_action('delete_term',  array($this, 'delete'),5, 5);
    }

    /**  Trigger delete attribute and send request
     * @since  1.0.0
     * @hooked woocommerce_attribute_deleted
     * @param  string $term
     * @param  integer $tt_id
     * @param  string $taxonomy
     * @param  array $deleted_term
     * @param  array $object_ids
     * @return false | void
     */
    function delete( $term, $tt_id, $taxonomy, $deleted_term, $object_ids ){

        $data = [
            'topic' => 'delete',
            'data'=> [
                'type'   => $taxonomy,
                'key'    => $taxonomy,
                'value'  => [
                    'id'        => $deleted_term->term_id,
                    'name'      => $deleted_term->name,
                    'parent_id' => $deleted_term->parent,
                ],
            ] ];
        SsLogger::info('[Webhook][Product_Attribute][Delete] '.json_encode($data));
        $this->ssRequest->post( $data );
    }

    /**  Trigger update attribute and send request
     * @since  1.0.0
     * @hooked woocommerce_attribute_updated
     * @param  string $term_id
     * @param  string $tt_id
     * @param  string $taxonomy
     * @return false | void
     */
    function update( $term_id, $tt_id, $taxonomy  ){

        if( !term_exists($term_id,$taxonomy) ){
            return false;
        }

        $product_cat = get_term($term_id, $taxonomy);

        $data     = [
            'topic' => 'update',
            'data'=> [
                'type'   => $taxonomy,
                'key'    => $taxonomy,
                'value'  => [
                    'id'        => $product_cat->term_id,
                    'name'      => $product_cat->name,
                    'parent_id' => $product_cat->parent,
                ],
            ]];

        SsLogger::info('[Webhook][Product_Attribute][Update] '.json_encode($data));
        $this->ssRequest->post( $data );
    }



    /**  Trigger create attribute and send request
     * @since  1.0.0
     * @hooked woocommerce_attribute_added
     * @param  string $term_id
     * @param  string $tt_id
     * @param  string $taxonomy
     * @return false | void
     */
    function create( $term_id, $tt_id, $taxonomy ){
        if( empty($data['attribute_name']) )
            return false;

        $mapping_value = $this->getMappingValue($data['attribute_name']);
        $exist = $this->ssMapping->getValue($mapping_value);
        if( empty($exist)){
            return false;
        }

        if( !term_exists($term_id,$taxonomy) ){
            return false;
        }

        $product_cat = get_term($term_id, $taxonomy);

        $data = [
            'topic'=>'create',
            'data'=> [
                'type'   => $taxonomy,
                'key'    => $taxonomy,
                'value'  => [
                    'id'        => $product_cat->term_id,
                    'name'      => $product_cat->name,
                    'parent_id' => $product_cat->parent,
                ],
            ]];

        SsLogger::info('[Webhook][Product_Attribute][Create] '.json_encode($data));
        $this->ssRequest->post( $data );
    }

}