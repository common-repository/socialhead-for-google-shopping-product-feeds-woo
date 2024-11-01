<?php

namespace Socialshop\Base\Webhooks;
use Socialshop\Base\SsLogger;
use Socialshop\Base\SsProductAttribute;

class SsWebhookAttribute extends SsWebhook{

    protected $key = 'variants.attributes.';
    protected $ssProductAttribute;
    public function __construct()
    {
        parent::__construct();
        add_action('woocommerce_attribute_updated',array($this, 'update'),5, 3);
        add_action('woocommerce_attribute_added',  array($this, 'create'),5, 2);
        add_action('woocommerce_attribute_deleted',array($this, 'delete'),5, 3);

        $this->ssProductAttribute = new SsProductAttribute();
    }

    /**
     * @since  1.0.0
     * @param  string $name
     * @return
     */
    function getMappingValue($name ){
        return $this->key.$name;
    }

    /** Trigger update attribute and send request
     * @since  1.0.0
     * @hooked woocommerce_attribute_updated
     * @param  string $id
     * @param  array $data
     * @param  string $old_slug
     * @return false | void
     */
    function update( $id, $data, $old_slug  ){
        $new_mapping_value = $this->getMappingValue($data['attribute_name']);
        $old_mapping_value = $this->getMappingValue($old_slug);
        $new_exist = $this->ssMapping->getValue($new_mapping_value);
        $this->ssProductAttribute->replaceDB(  $data['attribute_name'] , $data['attribute_label'] , $id );

        if( $new_mapping_value === $old_mapping_value )
            return false;

        if( !empty($new_exist ) )
            $this->create($id,$data);

        if( !empty($old_mapping_value) )
            $this->delete($id,$old_slug);

    }

    /**  Trigger delete attribute and send request
     * @since  1.0.0
     * @hooked woocommerce_attribute_deleted
     * @param  string $attribute_id
     * @param  array $attribute_name
     * @param  string $taxonomy
     * @return false | void
     */
    function delete($attribute_id, $attribute_name, $taxonomy = null ){

        if( empty($attribute_name))
            return false;

        $mapping_value = $this->getMappingValue($attribute_name);
        $exist = $this->ssMapping->getValue($mapping_value);
        $this->ssProductAttribute->delete($attribute_name);

        if( empty($exist)){
            return false;
        }

        $data     = [
            'topic' => 'delete',
            'data'  => [
                'type' => 'attribute',
                'key'    => $mapping_value,
                'value'  => [
                    'id' => $attribute_id,
                    'value' => $attribute_name
                ]
            ] ];
        SsLogger::info('[Webhook][Product_Attribute][Delete] '.json_encode($data));
        $this->ssRequest->post( $data );
    }

    /**  Trigger create attribute and send request
     * @since  1.0.0
     * @hooked woocommerce_attribute_added
     * @param  string $id
     * @param  string $data
     * @return false | void
     */
    function create($id, $data ){
        if( empty($data['attribute_name']) )
            return false;

        $mapping_value = $this->getMappingValue($data['attribute_name']);
        $exist = $this->ssMapping->getValue($mapping_value);
        $this->ssProductAttribute->create($data['attribute_name'],$data['attribute_label'], $id);

        if( empty($exist)){
            return false;
        }

        $data     = ['data'=> [
            'type'   => 'attribute',
            'key'    => $mapping_value,
            'value'  => [
                'id'   => $id,
                'value' => $data
            ],
        ] , 'topic'=>'create' ];
        SsLogger::info('[Webhook][Product_Attribute][Create] '.json_encode($data));
        $this->ssRequest->post( $data );
    }

}