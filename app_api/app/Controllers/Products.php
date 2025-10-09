<?php namespace App\Controllers;
	use App\Models\ProductsModel;
	class Products extends BaseController
	{
		public function index()
		{
			if ($this->isGet()) {
				if ($this->AuthenticateApikey()) {
					if ($this->AuthenticateToken()) {
						if($this->CheckPermission('products-view')){
							$productsModel = new ProductsModel();
							$params = array();
							$page = intval($this->getParam('page', 1));
							$length = intval($this->getParam('per_page', 25));
							$totalrecords = intval($this->getParam('totalrecords', 0));
							$keywords = $this->getParam('keywords', '');
							$date = $this->getParam('date', '');
							$start_date = $this->getParam('start_date', '');
							$end_date = $this->getParam('end_date', '');
							$category = $this->getParam('category', '');
							$brand = $this->getParam('brand', '');
							$status = $this->getParam('status', '');
							$min_price = $this->getParam('min_price', '');
							$max_price = $this->getParam('max_price', '');
							$order_by_col = $this->getParam('order_by_col', "");
							$order_by = $this->getParam('order_by', "");

							if($keywords != ''){
								$params['keywords'] = $keywords;
							}
							if($date != ''){
								$params['date'] = $date;
							}
							if($start_date != '' && $end_date != ''){
								$params['start_date'] = $start_date;
								$params['end_date'] = $end_date;
							}
							if($category != ''){
								$params['category'] = $category;
							}
							if($brand != ''){
								$params['brand'] = $brand;
							}
							if($status != ''){
								$params['status'] = $status;
							}
							if($min_price != ''){
								$params['min_price'] = $min_price;
							}
							if($max_price != ''){
								$params['max_price'] = $max_price;
							}

							if($totalrecords == 0 || $page == 1){
								$params['count'] = true;
								$totalrecords = $productsModel->search($params);
								unset($params['count']);
							}
							$paging = paging($page, $totalrecords, $length);
							$params['limit'] = array('length' => $paging['length'], 'offset' => $paging['offset']);

							if($order_by_col != ''){
								$params['sort'] = array('column' => $order_by_col, 'order' => $order_by);
							}

							$products = $productsModel->search($params);
							//echo $productsModel->getLastQuery()->getQuery();exit;

							$remainingrecords = $totalrecords - ($paging['offset'] + count($products));
							$paging['remainingrecords'] = $remainingrecords;
							$this->setSuccess($this->successMessage);
							$this->setOutput(array('paging' => array($paging), 'products' => $products));
						} else {
							$this->setError('You do not have permission to access this module');
						}
					} else {
						$this->setError($this->invalidToken);
					}
				} else {
					$this->setError($this->invalidApiKey);
				}
			} else {
				$this->setError($this->methodNotAllowed);
			}
			return $this->response();
		}

		public function details($id = 0)
		{
			if ($this->isGet()) {
				if ($this->AuthenticateApikey()) {
					if ($this->AuthenticateToken()) {
						if($this->CheckPermission('products-view')){
							if(is_numeric($id)){
								$id = intval($id);
								if ($id > 0) {
									$productsModel = new ProductsModel();
									$product = $productsModel->findByID($id);
									if($product){
										$this->setSuccess();
										$this->setOutput(array('product' => array($product)));
									} else {
										$this->setError($this->noContent);
									}
								} else {
									$this->setError("Please enter product id");
								}
							} else {
								$this->setError("Please enter product id in numeric");
							}
						} else {
							$this->setError('You do not have permission to access this module');
						}
					} else {
						$this->setError($this->invalidToken);
					}
				} else {
					$this->setError($this->invalidApiKey);
				}
			} else {
				$this->setError($this->methodNotAllowed);
			}
			return $this->response();
		}

		public function new()
		{
			if ($this->AuthenticateApikey()) {
				if ($this->AuthenticateToken()) {
					if($this->CheckPermission('products-create')){
						$productsModel = new ProductsModel();

						if($this->isPost()){
							$post = esc($this->getPost());
							$isvalidrequest = true;

							// Required field validation
							if (!isset($post['product_code']) || empty($post['product_code'])) {
								$this->setError("Please enter product code.");
								$isvalidrequest = false;
							} else {
								// Check if product code already exists
								if($isvalidrequest){
									$existingProduct = $productsModel->where('product_code', $post['product_code'])->first();
									if($existingProduct){
										$this->setError("Product code already exists.");
										$isvalidrequest = false;
									}
								}
							}
							if (!isset($post['name']) || empty($post['name'])) {
								$this->setError("Please enter product name.");
								$isvalidrequest = false;
							}
							if (!isset($post['unit_price']) || empty($post['unit_price'])) {
								$this->setError("Please enter unit price.");
								$isvalidrequest = false;
							}

							if($isvalidrequest && empty($this->_output['message'])){
								// Insert new product
								$values = array(
									'product_code' => $post['product_code'],
									'name' => $post['name'],
									'description' => isset($post['description']) ? $post['description'] : '',
									'category' => isset($post['category']) ? $post['category'] : '',
									'brand' => isset($post['brand']) ? $post['brand'] : '',
									'unit_price' => $post['unit_price'],
									'reorder_level' => isset($post['reorder_level']) ? $post['reorder_level'] : 0,
									'status' => isset($post['status']) ? $post['status'] : 'active',
									'created_at' => date("Y-m-d H:i:s")
								);
								$product_id = $productsModel->insert($values);

								if($product_id > 0){
									$this->setOutput($product_id, 'id');
									$this->setSuccess("Product added successfully");
								} else {
									$this->setError("Failed to add product");
								}
							}
						} else {
							$this->setError($this->methodNotAllowed);
						}
					} else {
						$this->setError('You do not have permission to access this module');
					}
				} else {
					$this->setError($this->invalidToken);
				}
			} else {
				$this->setError($this->invalidApiKey);
			}
			return $this->response();
		}

		public function edit($id = 0)
		{
			if ($this->AuthenticateApikey()) {
				if ($this->AuthenticateToken()) {
					if($this->CheckPermission('products-edit')){
						$productsModel = new ProductsModel();

						if($this->isPost() || $this->isPut()){
							$post = esc($this->getPost());
							$id = isset($id) ? intval($id) : 0;
							$isvalidrequest = true;

							if($id <= 0){
								$this->setError("Please provide valid product id");
								$isvalidrequest = false;
							}

							// Update validation
							if($isvalidrequest){
								if (isset($post['product_code']) && !empty($post['product_code'])) {
									// Check if product code already exists for other products
									$existingProduct = $productsModel->where('product_code', $post['product_code'])
																->where('id !=', $id)
																->first();
									if($existingProduct){
										$this->setError("Product code already exists.");
										$isvalidrequest = false;
									}
								}
							}

							if($isvalidrequest && empty($this->_output['message'])){
								// Update existing product
								$product = $productsModel->findByID($id);
								if($product){
									$values = array(
										'product_code' => isset($post['product_code']) ? $post['product_code'] : $product['product_code'],
										'name' => isset($post['name']) ? $post['name'] : $product['name'],
										'description' => isset($post['description']) ? $post['description'] : $product['description'],
										'category' => isset($post['category']) ? $post['category'] : $product['category'],
										'brand' => isset($post['brand']) ? $post['brand'] : $product['brand'],
										'unit_price' => isset($post['unit_price']) ? $post['unit_price'] : $product['unit_price'],
										'reorder_level' => isset($post['reorder_level']) ? $post['reorder_level'] : $product['reorder_level'],
										'status' => isset($post['status']) ? $post['status'] : $product['status']
									);

									$productsModel->update($product['id'], $values);

									$this->setOutput($product['id'], 'id');
									$this->setSuccess('Product updated successfully');
								} else {
									$this->setError("Invalid product record");
								}
							}
						} else {
							$this->setError($this->methodNotAllowed);
						}
					} else {
						$this->setError('You do not have permission to access this module');
					}
				} else {
					$this->setError($this->invalidToken);
				}
			} else {
				$this->setError($this->invalidApiKey);
			}
			return $this->response();
		}

		public function delete($id = 0)
		{
			if ($this->AuthenticateApikey()) {
				if ($this->AuthenticateToken()) {
					if($this->CheckPermission('products-delete')){
						if($this->isDelete()){
							$id = isset($id) ? intval($id) : 0;

							if($id > 0){
								$productsModel = new ProductsModel();
								$product = $productsModel->findByID($id);

								if($product){
									// Soft delete by updating status
									$productsModel->update($id, [
										'status' => 'discontinued'
									]);

									$this->setSuccess("Product deleted successfully");
								} else {
									$this->setError("Product not found");
								}
							} else {
								$this->setError("Please provide valid product id");
							}
						} else {
							$this->setError($this->methodNotAllowed);
						}
					} else {
						$this->setError('You do not have permission to access this module');
					}
				} else {
					$this->setError($this->invalidToken);
				}
			} else {
				$this->setError($this->invalidApiKey);
			}
			return $this->response();
		}
	}

