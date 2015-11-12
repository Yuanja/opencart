<?php
class ControllerProductEmailspecials extends Controller {
	
	public function index($setting) {
		$url = '';

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts();
		
		$data['products'] = array();

		if (isset($this->request->get['products'])) {
			$product_request_list = $this->request->get['products'];
		} 
		
		$products = array();
		foreach ($product_request_list as $aproductrequest){
			$product_from_db = $this->model_catalog_product->getProductBySku($aproductrequest);
			if (isset($product_from_db)){
				array_push($products, $product_from_db);
			}
		}
		
		if (!empty($products)) {
			foreach ($products as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
				}

				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
				} else {
					$price = false;
				}

				
				$attribute_groups = $this->model_catalog_product->getProductAttributes($result['product_id']);
				
				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('config_product_description_length')) . '..',
					'price'       => $price,
					'attribute_groups' => $attribute_groups,
					'stock_status'	=> $result['stock_status']
				);
			}
		}

		if ($data['products']) {
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/featured.tpl')) {
				$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/product/featured.tpl', $data));
			} else {
				$this->response->setOutput($this->load->view('default/template/product/featured.tpl', $data));
			}
		}
	}
}