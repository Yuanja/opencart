<?php
define("CATEGORY_DELIMETER", "&nbsp;&nbsp;&gt;&nbsp;&nbsp;");
define("WATCH_ATTRIBUTE_GROUP", "Watch attributes");
define("SOURCE_IP", "107.197.220.126");
define("IMAGE_URL_BASE", "catalog/watches");
define("DOWNLOAD_DIR", DIR_IMAGE.IMAGE_URL_BASE);
define("FEED_URL", "https://".SOURCE_IP."/fmi/xml/fmresultset.xml?-db=DEG&-lay=WEB_XML&-find&web_flag__c.op=eq&web_flag__c=1");

class ControllerCatalogRefresh extends Controller {
	
	private $error = array();
	private $categoryIdByNameCache = array();
	private $attributeGroupIdByNameCache = array();
	private $attributeIdByNameCache = array();
	
	//Justin just wants 10 at any time.  Filter is here:
	private	$allowedTopCategoryNames = array(
			"Rolex" => 1,
			"Patek Philippe" => 2,
			"Cartier" => 3,
			"A. Lange & Sohne" => 4,
			"Panerai" => 5,
			"Audemars Piguet" => 6,
			"Piaget" => 7,
			"Breguet" => 8,
			"Hublot" => 9,
			"Omega" => 10,
			"Vacheron Constantin" => 11,
			"Other Brands" => 12000
		);
	
	public function index() {
		$this->turnWarningIntoExceptions();
		
		$this->load->language('catalog/refresh');
		$this->echoFlush("<html>");
		$this->echoFlush("<head>");
		$this->echoFlush("<title>Refreshing catalog</title>");
		$this->echoFlush("</head>");
		$this->echoFlush("<body>");
		
		if (isset($this->request->get['clear'])){
			//Delete all product
			$this->echoFlush("Deleting all products...");
			$this->load->model('catalog/product');
			foreach ($this->model_catalog_product->getProducts(array()) as $product){
				$this->echoFlush("Deleting product: ".$product['product_id']." name: ".$product['name']);
				$this->model_catalog_product->deleteProduct($product['product_id']);
			}
			
			$this->echoFlush("Deleting all catgories...");
			$this->load->model('catalog/category');
			foreach ($this->model_catalog_category->getCategories(array()) as $category){
				$this->echoFlush("Deleting category: ".$category['category_id']." name: ".$category['name']);
				$this->model_catalog_category->deleteCategory($category['category_id']);
			}
			
			$this->echoFlush("Deleting all attributes...");
			$this->load->model('catalog/attribute');
			foreach ($this->model_catalog_attribute->getAttributes(array()) as $attribute){
				$this->echoFlush("Deleting attribte: ".$attribute['attribute_id']." name: ".$attribute['name']);
				$this->model_catalog_attribute->deleteAttribute($attribute['attribute_id']);
			}
		}
		
		$this->readFromFeed();
	}
	
	public function echoFlush($message){
		if (ob_get_level() == 0) ob_start();
		$pst = new DateTimeZone('America/Los_Angeles');
		$nowDT = new DateTime('now', $pst);
		echo "<br>".($nowDT->format('Y-m-d H:i:s')).CATEGORY_DELIMETER.$message."\n";
		ob_flush();
		flush();
		ob_end_flush();
	}
	
	public function readFromFeed() {
		$this->echoFlush("Reading from source...<br>");
		
		//Set timeout to 20min
		$ctx = stream_context_create(array('http'=>
				array(
						'timeout' => 1200,  //1200 Seconds is 20 Minutes
				)
		));
		$this->url_get_contents('/tmp/tmpout.xml', FEED_URL);
		$xml = simplexml_load_file('/tmp/tmpout.xml');
		$recordValueRegArray = $this->getRecordValueRegArray($xml);
		$changedRecordsRegArray = $this->getChangedRecordsArray($recordValueRegArray);
		$this->saveChangedRecords($changedRecordsRegArray);		
		//update featured/recently added
		$this->updateFeaturedProducts();
		$this->echoFlush("End processing!");
	}

	private function url_get_contents($outFile, $url) {
		if (!function_exists('curl_init')){
			die('CURL is not installed!');
		}
		$fp = fopen($outFile, 'w');
		$this->echoFlush("url_get_contents opened: ".$outFile." for writting.");
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30000);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		
		$output = curl_exec($ch);
		curl_close($ch);
		fclose($fp);
		$this->echoFlush("url_get_contents completed.");
	}
	
	private function downloadFeed(){
		$response = NULL;
		if ( $fp = fsockopen("ssl://".SOURCE_IP, 443, $errno, $errstr, 3) ) {
			$data ="-db=DEG&-lay=WEB_XML&-find&web_flag__c.op=eq&web_flag__c=1";
			
			$msg  = 'POST /fmi/xml/fmresultset.xml?-db=DEG&-lay=WEB_XML&-find&web_flag__c.op=eq&web_flag__c=1 HTTP/1.1' . "\r\n";
			$msg .= 'Content-Type: application/x-www-form-urlencoded' . "\r\n";
			$msg .= 'Content-Length: ' . strlen($data) . "\r\n";
			$msg .= 'Host: ' . SOURCE_IP . "\r\n";
			$msg .= 'Connection: close' . "\r\n\r\n";
			$msg .= $data;
			if ( fwrite($fp, $msg) ) {
				while ( !feof($fp) ) {
					
					$response = fgets($fp, 1024);
				}
			}
			fclose($fp);
		
		} else {
			$response = false;
		}
	}
	
	private function updateFeaturedProducts(){
		//Select the top 20 items ordered by product 
		$this->echoFlush("Updating featured products section.");
		$this->load->model('catalog/product');
		$this->model_catalog_product->updateFeaturedPrduct();
	}
	
	
	private function saveChangedRecords($changedRecordsRegArray){
		$this->echoFlush("Handling ".sizeof($changedRecordsRegArray)." number of changed records...<br>");
		
		$this->load->model('catalog/category');
		
		foreach($changedRecordsRegArray as $changedRecordReg){
			$allCategoryIds = $this->getAllUniqueCategoryIds($changedRecordReg);
			$allProductAttributes = $this->ensureAttributesAndGroups($changedRecordReg);
			$allProductImages = $this->ensureImages($changedRecordReg);
			$current_product = $changedRecordReg->get('current_product');
			if($current_product){
				$this->echoFlush("Merging product id: ".$current_product['product_id']);
				//delete
				$this->load->model('catalog/product');
				$this->model_catalog_proudct->deleteProduct($current_product['product_id']);
			}
			$this->insertNewProduct($changedRecordReg, $allCategoryIds, $allProductAttributes, $allProductImages);
		}
	}
	
	private function ensureImages($changedRecordReg){
		//if the save directory doesn't exist, create it.
		if (!file_exists(DOWNLOAD_DIR)){
			mkdir(DOWNLOAD_DIR, 0777);
		}
		$returnImagePathArray = array();
		array_push($returnImagePathArray, $this->downloadImage('web_image_path_1', $changedRecordReg));

		return array_filter($returnImagePathArray);
	}
	
	private function downloadImage($imageElement, $changedRecordReg){
		//get the pics.
		if (!empty($changedRecordReg->get($imageElement))){
			//Figure out the image name
			$image_name = $changedRecordReg->get("web_tag_number")."jpg";
				
			$imageOutUrlPath = IMAGE_URL_BASE."/".$imageName;;
			
			try{
				$image1Url = $changedRecordReg->get($imageElement);
				$imageFilePath = DOWNLOAD_DIR."/".$imageName;
				$this->echoFlush("Downloading images from: ".$image1Url."...");
				
				$this->url_get_contents($imageFilePath, $image1Url);
				
				$this->echoFlush("Success! Image url for product: ".$imageOutUrlPath."...");
			} catch (ErrorException $e){
				$this->echoFlush("FAILED to download images from: ".$image1Url."... .".$e->getTraceAsString());
				$imageOutUrlPath = "no_image.png";
			}
				
			return array('image' => $imageOutUrlPath, 'sort_order' => '0');
		} else {
			return NULL;
		}
	}
	
	private function ensureAttributesAndGroups($changedRecordReg){
		$this->load->model('catalog/attribute_group');
		$this->load->model('catalog/attribute');
		$this->primeAttributeGroupsCache();
		$this->primeAttributeCache();
		
		$productAttributes = array();
		array_push($productAttributes, $this->getAttributeArrayElement("web_designer", "Brand", 10, $changedRecordReg));
		array_push($productAttributes, $this->getAttributeArrayElement("web_watch_model", "Model", 20, $changedRecordReg));
		array_push($productAttributes, $this->getAttributeArrayElement("web_watch_manufacturer_reference_number", "Ref:", 23, $changedRecordReg));
		array_push($productAttributes, $this->getAttributeArrayElement("web_watch_year", "Year", 30, $changedRecordReg));
		//array_push($productAttributes, $this->getAttributeArrayElement("web_serial_number", "Serial Number", 40, $changedRecordReg));
		//array_push($productAttributes, $this->getAttributeArrayElement("web_case_serial_number", "Case Serial Number", 50, $changedRecordReg));
		array_push($productAttributes, $this->getAttributeArrayElement("web_watch_diameter", "Diameter", 51, $changedRecordReg));
		array_push($productAttributes, $this->getAttributeArrayElement("web_watch_movement", "Movement", 55, $changedRecordReg));
		array_push($productAttributes, $this->getAttributeArrayElement("web_watch_complications", "Complications", 60, $changedRecordReg));
		array_push($productAttributes, $this->getAttributeArrayElement("web_watch_case", "Case", 70, $changedRecordReg));
		array_push($productAttributes, $this->getAttributeArrayElement("web_watch_dial", "Dial", 80, $changedRecordReg));
		//array_push($productAttributes, $this->getAttributeArrayElement("web_watch_strap", "Strap", 90, $changedRecordReg));
		//array_push($productAttributes, $this->getAttributeArrayElement("web_watch_buckle", "Buckle", 100, $changedRecordReg));
		array_push($productAttributes, $this->getAttributeArrayElement("web_watch_box_papers", "Box Paper", 110, $changedRecordReg));
		array_push($productAttributes, $this->getAttributeArrayElement("web_watch_condition", "Condition", 120, $changedRecordReg));
		array_push($productAttributes, $this->getAttributeArrayElement("web_price_retail", "Retail Price", 130, $changedRecordReg));
		array_push($productAttributes, $this->getAttributeArrayElement("web_price_sale", "Sale Price", 140, $changedRecordReg));
		
		
		return array_filter($productAttributes);
	}
	
	private function getAttributeArrayElement($feedElementKey, $attributeName, $sortOrder, $changedRecordReg){
		if (!empty($changedRecordReg->get($feedElementKey))){
			$value = $changedRecordReg->get($feedElementKey);
			if ($feedElementKey == "web_price_sale" || $feedElementKey == "web_price_retail"){
				$value = (float)str_replace("$", "", $value);
				if (empty($value) ){
					$value = 0;
				}
			}
			
			return array(
				'attribute_id' => $this->getAttributeId($attributeName, 
																	 $this->attributeGroupIdByNameCache[WATCH_ATTRIBUTE_GROUP],
																	 $sortOrder),
				'product_attribute_description' => array('1' => array("text" => $value ))
			);
		} else {
			return NULL;
		}
	}
	
	private function primeAttributeGroupsCache(){
		if(empty($this->attribute_groupIdByNameCache)){
			$allAttributeGroups = $this->model_catalog_attribute_group->getAttributeGroups(array());
			foreach($allAttributeGroups as $agroup){
				$this->attributeGroupIdByNameCache[(string)$agroup['name']] 
					= (string)$agroup['attribute_group_id'];
			}
		}
		
		//Ensure "watch" attribute group is in
		if (!isset($this->attributeGroupIdByNameCache[WATCH_ATTRIBUTE_GROUP])){
			$newAttributeGroupId = $this->model_catalog_attribute_group->addAttributeGroup(
					array(
							'sort_order' => '1',
							'attribute_group_description'=> array('1' => array('name' => WATCH_ATTRIBUTE_GROUP))
					)
			);
			$this->attributeGroupIdByNameCache[WATCH_ATTRIBUTE_GROUP] = $newAttributeGroupId;
		} 
	}
	
	private function primeAttributeCache(){
		if(empty($this->attributeIdByNameCache)){
			$allAttributes = $this->model_catalog_attribute->getAttributes(array());
			foreach($allAttributes as $anAttribute){
				$this->attributeIdByNameCache[(string)$anAttribute['name']]
				= (string)$anAttribute['attribute_id'];
			}
		}
		
		$groupId = $this->attributeGroupIdByNameCache[WATCH_ATTRIBUTE_GROUP];
	}
	
	private function getAttributeId($attributeName, $attributeGroupId, $sortOrder){
		if (empty($this->attributeIdByNameCache[$attributeName])){
			$newid = $this->model_catalog_attribute->addAttribute(
				array('attribute_group_id' => $attributeGroupId,
						'sort_order' => $sortOrder,
						'attribute_description' => array('1'=>array('name'=>$attributeName))
				)	
			);
			$this->attributeIdByNameCache[$attributeName] = $newid;
			return $newid;
		} else {
			return $this->attributeIdByNameCache[$attributeName];
		}
	}
	
	private function getAllUniqueCategoryIds($changedRecordReg){
		$brand = $changedRecordReg->get('web_designer');
		$model = !empty($changedRecordReg->get('web_watch_model')) ? $changedRecordReg->get('web_watch_model') : "Other Models";

		//Create the make->model cats
		$brandModelCategory = $this->ensureCategories(
				$brand.CATEGORY_DELIMETER.$model);
		
		$allOtherCatg = array();
		//Create the All Other Watches ->make->model
		if (!in_array($brand, $this->allowedTopCategoryNames)){
			$allOtherCatg = $this->ensureCategories("Other Brands".CATEGORY_DELIMETER.$brand.CATEGORY_DELIMETER.$model);
		}

		$allCats = array_merge($brandModelCategory, $allOtherCatg );
		return array_unique($allCats);
		
	}

	private function ensureCategories($categoryString){
		//load the cache
		$this->primeCategoryCache();
		//First convert the canonical form (the form with delimiter) into array elements and strip delimiter.
		$categoryArray = explode(CATEGORY_DELIMETER, $categoryString);
		$returnCategoryIds = array();
		
		$foundCategoryId = NULL;
		$currentCategoryParentId = NULL;
		$currentFullCategoryName = NULL; //String with delimiter.
		
		foreach ($categoryArray as $subCategoryName){
			//Find the bitch
			if (!isset($currentFullCategoryName)){
				$currentFullCategoryName = $subCategoryName;
			} else {
				$currentFullCategoryName = 
					$currentFullCategoryName.CATEGORY_DELIMETER.$subCategoryName;
			}
			
			if (array_key_exists($currentFullCategoryName, 
					$this->categoryIdByNameCache)){
				$foundCategoryId = $this->categoryIdByNameCache[$currentFullCategoryName];
				//Found the bitch, no need to insert to db
				$currentCategoryParentId = $foundCategoryId;
				array_push($returnCategoryIds, $foundCategoryId);
			} else {
				$newCategoryId = $this->makeCategory($subCategoryName, $currentCategoryParentId);
				$this->categoryIdByNameCache[$currentFullCategoryName] = $newCategoryId;
				$currentCategoryParentId = $newCategoryId;
				
				array_push($returnCategoryIds, $newCategoryId);
			}
		}
		return $returnCategoryIds;
	}
	
	private function makeCategory($categoryName, $currentParentCategoryId){
		//Nope, insert the bitch. and return the new id.
		
		//Override for sort_order
		$sortOrder = 100;
		if (!isset($currentParentCategoryId) && array_key_exists($categoryName, $this->allowedTopCategoryNames)){
			$sortOrder = $this->allowedTopCategoryNames[$categoryName];
		}
		
		$data = array(
				'parent_id' => isset($currentParentCategoryId) ? $currentParentCategoryId : NULL,
				'top' => !isset($currentParentCategoryId) ? 1 : 0,
				'column' => '0',
				'sort_order' => $sortOrder,
				'status' => '1'
		);
		//set language hash values.  To be inserted for category_description.
		$descriptionValue = array(
				'name'=>$categoryName,
				'description'=>$categoryName,
				'meta_title'=>$categoryName,
				'meta_description'=>$categoryName,
				'meta_keyword'=>$categoryName
		);
		$descriptionByLanguage = array('1' => $descriptionValue);
		$data['category_description'] = $descriptionByLanguage;
		$data['category_store'] = array('0'); //default store.
				
		//Persist category
		$newCategoryId = $this->model_catalog_category->addCategory($data);
		return $newCategoryId;
	}
	
	private function primeCategoryCache(){
		if (!isset($this->categoryIdByNameCache) || sizeof($this->categoryIdByNameCache) == 0){
			$this->load->model('catalog/category');
			$filter_data = array();
			$categoriesfromdb = $this->model_catalog_category->getCategories($filter_data);
			
			foreach ($categoriesfromdb as $category){
				$tmpName = (string)$category['name'];
				$tmpVal = (string)$category['category_id'];
				$this->categoryIdByNameCache[$tmpName] = $tmpVal;
			}
		}
	}
	
	private function insertNewProduct($changedRecordReg, $categoryIds, $allProductAttributes, $allImagePaths){
		
		//Get manufacture_id, if one doesn't exist add it.
		$manufacture_id = $this->ensureManufacturerId($changedRecordReg->get("web_designer"));
		
		//Take the first one as Image for the product home.  TODO: potentially tweak this more consistent.
		$this->db->query("INSERT INTO " . DB_PREFIX . "product ". 
				"SET model = '" .$this->db->escape($this->getNonNullString($changedRecordReg->get("web_watch_model"))). "', " 
				."sku = '" .$this->db->escape($this->getNonNullString($changedRecordReg->get("web_tag_number"))). "', " 
				."upc = '', "
				."ean = '', "
				."jan = '', " 
				."isbn = '', " 
				."mpn = '', "
				."location = '', " 
				."quantity = '1', "  
				."minimum = '1', " 
				."subtract = '0', " 
				."stock_status_id = '7', " 
				."date_available = NOW(), "  
				."manufacturer_id = '" . (int)$manufacture_id ."', " 
				."shipping = '0', "  
				."price = '" . (float)str_replace("$", "", $changedRecordReg->get('web_price_sale')). "', " 
				."points = '0', "  
				."weight = '0', "   
				."weight_class_id = '1', "  
				."length = '0', " 
				."width = '0', "  
				."height = '0', " 
				."length_class_id = '1', " 
				."status = '1', " 
				."tax_class_id = '0', " 
				."sort_order = '0', " 
				."date_added = NOW()");

		$product_id = $this->db->getLastId();
		
		if (isset($allImagePaths) && !empty($allImagePaths)) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" 
					. $this->db->escape($allImagePaths[0]['image']) . "' "
					. "WHERE product_id = '" . (int)$product_id . "'");
		}
		
		//Insert into store
		$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . $product_id . "', "
				. "store_id = '0' "
				);
		
		//Insert proudct description
		$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . $product_id ."', "
				. "language_id = '1', "
				. "name ='". $this->db->escape($this->getNonNullString($changedRecordReg->get("web_description_short"))) . "', "
				. "description = '".$this->db->escape($this->getNonNullString($changedRecordReg->get("web_description_short")))."', "
				. "tag = '', meta_title = '".$this->db->escape($this->getNonNullString($changedRecordReg->get("web_description_short")))."', meta_description = '', meta_keyword = ''"
				);
		
		//Insert the category information for product
		foreach ($categoryIds as $category_id) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
		}
		
		if (isset($allProductAttributes)) {
			foreach ($allProductAttributes as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', "
								." attribute_id = '" . (int)$product_attribute['attribute_id'] . "', "
								." language_id = '" . (int)$language_id . "', "
								." text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
						
					}
				}
			}
		}
		
		if (isset($allImagePaths)) {
			foreach ($allImagePaths as $product_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', "
						." image = '" . $this->db->escape($product_image['image']) . "',"
						." sort_order = '" . (int)$product_image['sort_order'] . "'");
			}
		}
	}
	
	private function ensureManufacturerId($manufacturer_name){
		if (isset($manufacturer_name)){
			$this->load->model('catalog/manufacturer');
			$filter_data = array(
					'filter_name'	  => $manufacturer_name,
			);
			$manufacturers = $this->model_catalog_manufacturer->getManufacturers($filter_data);
			
			if ($manufacturers){
				//There should only be one and take that one.
				return $manufacturers[0]['manufacturer_id'];
			} else {
				$m_data = array(
					'name' => $manufacturer_name,
					'manufacturer_store' => array('0'),
					'sort_order' => '1'
				);
				$manufacturer_id = $this->model_catalog_manufacturer->addManufacturer($m_data);
				return $manufacturer_id;
			}
		}else{
			return NULL;
		}
	}
	
	private function getNonNullString($in_string){
		if (!isset($in_string)){
			return "";
		} else {
			return (string)$in_string;
		}
	}

	private function getChangedRecordsArray($recordValueRegArray){
		$this->echoFlush("Detecting changes...");
		
		$changedRecord = array();
		$index = 0;
		$this->load->model('catalog/product');
		
		foreach ($recordValueRegArray as $recordReg){
			//This is the identifier in FM that won't change between the load.
			$web_item_number = $recordReg->get('web_tag_number');
			if ($web_item_number){
				$filter_data = array(
						'filter_web_item_number'	  => $web_item_number,
				);
				$products = $this->model_catalog_product->getProducts($filter_data);
				
				if (!$products){
					$this->echoFlush("NEW web_tag_number: ".$web_item_number." : ".$recordReg->get("web_description_short"));
					$changedRecord[$index] = $recordReg;
					$index += 1;
				} elseif ($products && $this->hasChanged($products[0], $recordReg)){
					$this->echoFlush("CHANGED web_tag_number: ".$web_item_number." : ".$recordReg->get("web_description_short"));
					$changedRecord[$index] = $recordReg;
					$recordReg->set('current_product', $products[0] );
					$index += 1;
				} else {
					$this->echoFlush("NO CHANGES DETECTED web_tag_number: ".$web_item_number." : ".$recordReg->get("web_description_short"));
				}
			}
		}
		return $changedRecord;
	}
	
	private function hasChanged($product, $recordReg){
		if (!$product){
			return true;
		}
		
		$product_id = $product['product_id'];
		$product_descs = $this->model_catalog_product->getProductDescriptions($product_id);
		if ($product_descs){
			$product_desc = $product_descs[1];
			if (strcmp($product_desc['name'], $recordReg->get('web_description_short'))){
				return true;
			}
		}
		
		$product_model = $product['model'];
		$product_price = $product['price'];
		
		//TODO: deeper comparison
		if ((float)str_replace("$", "", $recordReg->get('web_price_sale')) != $product_price){
			return true;
		}
		
		if (strcmp($recordReg->get('web_watch_model'), (string)$product_model)){
			return true;
		}
		
		return false;
	}
	
	private function getRecordValueRegArray($xml){
		$this->echoFlush("Parsing from source...<br>");
		if ($xml){
			$recordIndex = 0;
			$recordValues = null;
			$xmlRecords = $xml->resultset->children();
			foreach ($xmlRecords as $xmlRecord){
				//Get all fields
				$fieldValueReg = new Registry();
				foreach ($xmlRecord->children() as $field){
					$fieldValueReg->set((string)$field['name'], (string)$field->data);
				}
				$recordValues[$recordIndex] = $fieldValueReg;
				$recordIndex += 1;;
			}
			return $recordValues;
		} else {
			return null;
		}
	}
	
	private function turnWarningIntoExceptions(){
		set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
			// error was suppressed with the @-operator
			if (0 === error_reporting()) {
				return false;
			}
		
			throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
		});
	}
}
