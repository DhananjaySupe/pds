<?php namespace App\Controllers;

use App\Models\ProductModel;
use App\Libraries\ExcelExporter;

class Products extends BaseController
{
	public function index()
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$productModel = new ProductModel();
		$recordsTotal = intval($this->getParam('recordstotal', 0));
		$recordsFiltered = intval($this->getParam('recordsfiltered', 0));
		$params = array();

		if ($recordsTotal === 0) {
			$params['count'] = true;
			$recordsTotal = $productModel->search($params);
			unset($params['count']);
		}

		if ($this->request->isAJAX()) {
			$draw = intval($this->getParam('draw', 1));
			$start = intval($this->getParam('start', 0));
			$length = intval($this->getParam('length', 50));
			$sorting = $this->getParam('order', array());
			$filter_keywords = trim($this->getParam('keywords', ''));
			$filter_status = trim($this->getParam('status', '')); // maps to products.is_active

			if ($filter_keywords !== '') {
				$params['keywords'] = $filter_keywords;
			}

			if ($filter_status !== '') {
				$params['is_active'] = $filter_status;
			}

			if ($filter_keywords !== '' || $filter_status !== '') {
				$countParams = $params;
				$countParams['count'] = true;
				$recordsFiltered = $productModel->search($countParams);
				unset($countParams);
			} else {
				$recordsFiltered = $recordsTotal;
			}

			$params['limit'] = array('length' => $length, 'offset' => $start);

			if (!empty($sorting) && isset($sorting[0]['column'])) {
				$columnIndex = intval($sorting[0]['column']);
				$columnMap = array(
					0 => 'products.product_name',
					1 => 'products.product_code',
					2 => 'products.category',
					3 => 'products.brand',
					4 => 'products.unit_type',
					5 => 'products.is_active',
					6 => 'products.created_at',
				);
				if (isset($columnMap[$columnIndex])) {
					$params['sort'] = array(
						'column' => $columnMap[$columnIndex],
						'order' => isset($sorting[0]['dir']) && in_array(strtolower($sorting[0]['dir']), array('asc', 'desc')) ? $sorting[0]['dir'] : 'asc',
					);
				}
			}

			$products = $productModel->search($params);
			$data = array();
			if (!empty($products)) {
				foreach ($products as $p) {
					$statusClass = ((string) ($p['is_active'] ?? '0') === '1') ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-muted';
					$createdAt = !empty($p['created_at']) ? date('d M Y', strtotime($p['created_at'])) : 'N/A';
					$productId = $p['product_id'];
					$productName = $p['product_name'] ?? '';
					$productCode = $p['product_code'] ?? '';

					$data[] = array(
						'id' => $productId,
						'name' => '<a href="' . site_url('products/view/' . $productId) . '" class="fw-semibold text-reset">' . esc($productName) . '</a>',
						'code' => !empty($productCode) ? '<span class="badge bg-info-subtle text-info">' . esc($productCode) . '</span>' : '—',
						'category' => !empty($p['category']) ? esc($p['category']) : '—',
						'brand' => !empty($p['brand']) ? esc($p['brand']) : '—',
						'unit_type' => !empty($p['unit_type']) ? esc($p['unit_type']) : '—',
						'status' => '<span class="badge ' . $statusClass . ' text-uppercase">' . (((string) ($p['is_active'] ?? '0') === '1') ? 'Active' : 'Inactive') . '</span>',
						'created_at' => $createdAt,
						'actions' => '
							<div class="dropdown text-center">
								<button type="button" class="btn btn-sm btn-soft-info btn-icon fs-14" data-bs-toggle="dropdown" aria-expanded="false">
									<i class="ri-more-2-fill"></i>
								</button>
								<ul class="dropdown-menu dropdown-menu-end">
									<li><a class="dropdown-item" href="' . site_url('products/view/' . $productId) . '"><i class="ri-eye-line align-middle me-1"></i> View</a></li>
									<li><a class="dropdown-item" href="' . site_url('products/edit/' . $productId) . '"><i class="ri-edit-line align-middle me-1"></i> Edit</a></li>
									<li><a class="dropdown-item text-danger" data-action="delete" data-id="' . $productId . '" data-name="' . esc($productName) . '" href="javascript:void(0);"><i class="ri-delete-bin-line align-middle me-1"></i> Delete</a></li>
								</ul>
							</div>',
					);
				}
			}

			return $this->response(array(
				'draw' => $draw,
				'recordsTotal' => $recordsTotal,
				'recordsFiltered' => $recordsFiltered,
				'data' => $data,
			));
		}

		$this->setData('filters', array(
			'keywords' => $this->getParam('keywords', ''),
			'status' => $this->getParam('status', ''),
		));
		$this->pageTitle('Products');
		$this->pageJs('assets/js/custom/products.js?v=s' . $this->AppConfig->jsVersion);
		return view('products/index', $this->viewdata);
	}

	public function export()
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$productModel = new ProductModel();
		$params = array();
		$filter_keywords = trim($this->getParam('keywords', ''));
		$filter_status = trim($this->getParam('status', '')); // maps to products.is_active

		if ($filter_keywords !== '') {
			$params['keywords'] = $filter_keywords;
		}

		if ($filter_status !== '') {
			$params['is_active'] = $filter_status;
		}

		$products = $productModel->search($params);
		$statusOptions = $this->getStatusOptions();

		$exporter = new ExcelExporter();
		$sheet = $exporter->spreadsheet->getActiveSheet();

		$headers = array(
			'Product Name',
			'Product Code',
			'Category',
			'Brand',
			'Unit Type',
			'Base Unit',
			'Standard Qty/Unit',
			'Min Stock',
			'Max Stock',
			'Status',
			'Created At',
		);

		$rows = array();
		if (!empty($products)) {
			foreach ($products as $p) {
				$statusKey = strtolower((string) ($p['is_active'] ?? '0'));
				$statusLabel = $statusOptions[$statusKey] ?? (((string) ($p['is_active'] ?? '0') === '1') ? 'Active' : 'Inactive');
				$createdAt = !empty($p['created_at']) ? date('Y-m-d H:i:s', strtotime($p['created_at'])) : '';

				$rows[] = array(
					$p['product_name'] ?? '',
					$p['product_code'] ?? '',
					$p['category'] ?? '',
					$p['brand'] ?? '',
					$p['unit_type'] ?? '',
					$p['base_unit'] ?? '',
					(isset($p['standard_quantity_per_unit']) && $p['standard_quantity_per_unit'] !== null) ? $p['standard_quantity_per_unit'] : '',
					(isset($p['min_stock_level']) && $p['min_stock_level'] !== null) ? $p['min_stock_level'] : '',
					(isset($p['max_stock_level']) && $p['max_stock_level'] !== null) ? $p['max_stock_level'] : '',
					$statusLabel,
					$createdAt,
				);
			}
		}

		$sheet->fromArray($headers, null, 'A1');
		if (!empty($rows)) {
			$sheet->fromArray($rows, null, 'A2');
		}

		// Basic formatting
		$sheet->freezePane('A2');
		$sheet->getStyle('A1:K1')->getFont()->setBold(true);
		foreach (range('A', 'K') as $col) {
			$sheet->getColumnDimension($col)->setAutoSize(true);
		}

		$filename = 'products_' . date('Ymd_His') . '.xlsx';
		$exporter->download($filename);
	}

	/**
	 * Select2 AJAX search endpoint for products
	 * GET /products/search?term=abc
	 */
	public function search()
	{
		if (!$this->isUserLoggedIn()) {
			return $this->response->setJSON(['results' => []]);
		}

		$term = trim((string) $this->getParam('term', $this->getParam('q', '')));
		if (mb_strlen($term) < 3) {
			return $this->response->setJSON(['results' => []]);
		}

		$productModel = new ProductModel();
		$params = array(
			'keywords' => $term,
			'limit' => array('length' => 20, 'offset' => 0),
			'sort' => array('column' => 'products.product_name', 'order' => 'asc'),
		);
		$products = $productModel->search($params);

		$results = array();
		if (!empty($products)) {
			foreach ($products as $p) {
				$name = $p['product_name'] ?? '';
				$code = $p['product_code'] ?? '';
				$text = trim($name . (!empty($code) ? ' (' . $code . ')' : ''));
				$results[] = array(
					'id' => (int) $p['product_id'],
					'text' => $text !== '' ? $text : ('Product #' . (int) $p['product_id']),
				);
			}
		}

		return $this->response->setJSON(['results' => $results]);
	}

	public function new()
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$error = '';
		$post = array();
		$productModel = new ProductModel();

		if ($this->isPost()) {
			$post = esc($this->getPost());
			$isvalidrequest = true;

			// Auto-generate code if missing
			if (!isset($post['product_code']) || trim($post['product_code']) === '') {
				$post['product_code'] = $this->generateProductCode($productModel);
			}

			if ($isvalidrequest && (!isset($post['product_name']) || trim($post['product_name']) === '')) {
				$error = "Please enter product name.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['is_active']) || ($post['is_active'] !== '1' && $post['is_active'] !== '0'))) {
				$error = "Please select status.";
				$isvalidrequest = false;
			}

			// Numeric validations (optional)
			$numericFields = array('standard_quantity_per_unit', 'min_stock_level', 'max_stock_level');
			if ($isvalidrequest) {
				foreach ($numericFields as $nf) {
					if (isset($post[$nf]) && trim((string) $post[$nf]) !== '' && !is_numeric($post[$nf])) {
						$error = "Please enter a valid number for " . str_replace('_', ' ', $nf) . ".";
						$isvalidrequest = false;
						break;
					}
				}
			}
			if ($isvalidrequest) {
				$min = isset($post['min_stock_level']) && $post['min_stock_level'] !== '' ? (float) $post['min_stock_level'] : null;
				$max = isset($post['max_stock_level']) && $post['max_stock_level'] !== '' ? (float) $post['max_stock_level'] : null;
				if ($min !== null && $max !== null && $max < $min) {
					$error = "Max stock level must be greater than or equal to min stock level.";
					$isvalidrequest = false;
				}
			}

			if ($isvalidrequest) {
				$existing = $productModel->findByProductCode(trim($post['product_code']));
				if ($existing) {
					// In case of rare collision (concurrent create), try generating once more
					$post['product_code'] = $this->generateProductCode($productModel);
					$existing2 = $productModel->findByProductCode(trim($post['product_code']));
					if ($existing2) {
						$error = "Product code already exists.";
						$isvalidrequest = false;
					}
				}

				if ($isvalidrequest) {
					$created_at = date('Y-m-d H:i:s');
					$product_id = $productModel->insert(array(
						'product_code' => trim($post['product_code']),
						'product_name' => trim($post['product_name']),
						'description' => trim($post['description'] ?? ''),
						'category' => trim($post['category'] ?? ''),
						'brand' => trim($post['brand'] ?? ''),
						'unit_type' => trim($post['unit_type'] ?? ''),
						'base_unit' => trim($post['base_unit'] ?? ''),
						'standard_quantity_per_unit' => (isset($post['standard_quantity_per_unit']) && $post['standard_quantity_per_unit'] !== '') ? $post['standard_quantity_per_unit'] : null,
						'min_stock_level' => (isset($post['min_stock_level']) && $post['min_stock_level'] !== '') ? $post['min_stock_level'] : null,
						'max_stock_level' => (isset($post['max_stock_level']) && $post['max_stock_level'] !== '') ? $post['max_stock_level'] : null,
						'is_active' => isset($post['is_active']) ? intval($post['is_active']) : 1,
						'created_at' => $created_at,
					));

					if ($product_id) {
						return redirect()->to('products/view/' . $product_id);
					}
					$error = 'Error creating product. Please try again.';
				}
			}
		}

		$formdata = array(
			'mode' => 'new',
			'id' => 0,
			'error' => $error,
			'product_code' => isset($post['product_code']) && $post['product_code'] !== '' ? $post['product_code'] : $this->generateProductCode($productModel),
			'product_name' => isset($post['product_name']) ? $post['product_name'] : '',
			'description' => isset($post['description']) ? $post['description'] : '',
			'category' => isset($post['category']) ? $post['category'] : '',
			'brand' => isset($post['brand']) ? $post['brand'] : '',
			'unit_type' => isset($post['unit_type']) ? $post['unit_type'] : '',
			'base_unit' => isset($post['base_unit']) ? $post['base_unit'] : '',
			'standard_quantity_per_unit' => isset($post['standard_quantity_per_unit']) ? $post['standard_quantity_per_unit'] : '',
			'min_stock_level' => isset($post['min_stock_level']) ? $post['min_stock_level'] : '',
			'max_stock_level' => isset($post['max_stock_level']) ? $post['max_stock_level'] : '',
			'is_active' => isset($post['is_active']) ? $post['is_active'] : '1',
		);

		$this->setData('formdata', $formdata);
		$this->setData('statusOptions', $this->getStatusOptions());
		$this->pageTitle('Add Product');
		return view('products/details', $this->viewdata);
	}

	public function edit($id = null)
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$productModel = new ProductModel();
		$product = $productModel->findByID($id);
		if (!$product) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$error = '';
		$post = array();
		if ($this->isPost()) {
			$post = esc($this->getPost());
			$isvalidrequest = true;

			if (!isset($post['product_code']) || trim($post['product_code']) === '') {
				$error = "Please enter product code.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['product_name']) || trim($post['product_name']) === '')) {
				$error = "Please enter product name.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['is_active']) || ($post['is_active'] !== '1' && $post['is_active'] !== '0'))) {
				$error = "Please select status.";
				$isvalidrequest = false;
			}

			// Numeric validations (optional)
			$numericFields = array('standard_quantity_per_unit', 'min_stock_level', 'max_stock_level');
			if ($isvalidrequest) {
				foreach ($numericFields as $nf) {
					if (isset($post[$nf]) && trim((string) $post[$nf]) !== '' && !is_numeric($post[$nf])) {
						$error = "Please enter a valid number for " . str_replace('_', ' ', $nf) . ".";
						$isvalidrequest = false;
						break;
					}
				}
			}
			if ($isvalidrequest) {
				$min = isset($post['min_stock_level']) && $post['min_stock_level'] !== '' ? (float) $post['min_stock_level'] : null;
				$max = isset($post['max_stock_level']) && $post['max_stock_level'] !== '' ? (float) $post['max_stock_level'] : null;
				if ($min !== null && $max !== null && $max < $min) {
					$error = "Max stock level must be greater than or equal to min stock level.";
					$isvalidrequest = false;
				}
			}

			if ($isvalidrequest) {
				$duplicate = $productModel->findByProductCode(trim($post['product_code']));
				if ($duplicate && (int) ($duplicate['product_id'] ?? 0) !== (int) $id) {
					$error = "Product code already exists.";
					$isvalidrequest = false;
				}
			}

			if ($isvalidrequest) {
				$result = $productModel->update($id, array(
					'product_code' => trim($post['product_code']),
					'product_name' => trim($post['product_name']),
					'description' => trim($post['description'] ?? ''),
					'category' => trim($post['category'] ?? ''),
					'brand' => trim($post['brand'] ?? ''),
					'unit_type' => trim($post['unit_type'] ?? ''),
					'base_unit' => trim($post['base_unit'] ?? ''),
					'standard_quantity_per_unit' => (isset($post['standard_quantity_per_unit']) && $post['standard_quantity_per_unit'] !== '') ? $post['standard_quantity_per_unit'] : null,
					'min_stock_level' => (isset($post['min_stock_level']) && $post['min_stock_level'] !== '') ? $post['min_stock_level'] : null,
					'max_stock_level' => (isset($post['max_stock_level']) && $post['max_stock_level'] !== '') ? $post['max_stock_level'] : null,
					'is_active' => isset($post['is_active']) ? intval($post['is_active']) : ($product['is_active'] ?? 1),
				));

				if ($result) {
					return redirect()->to('products/view/' . $id);
				}
				$error = 'Error updating product. Please try again.';
			}
		}

		$formdata = array(
			'mode' => 'edit',
			'id' => $product['product_id'],
			'error' => $error,
			'product_code' => isset($post['product_code']) ? $post['product_code'] : ($product['product_code'] ?? ''),
			'product_name' => isset($post['product_name']) ? $post['product_name'] : ($product['product_name'] ?? ''),
			'description' => isset($post['description']) ? $post['description'] : ($product['description'] ?? ''),
			'category' => isset($post['category']) ? $post['category'] : ($product['category'] ?? ''),
			'brand' => isset($post['brand']) ? $post['brand'] : ($product['brand'] ?? ''),
			'unit_type' => isset($post['unit_type']) ? $post['unit_type'] : ($product['unit_type'] ?? ''),
			'base_unit' => isset($post['base_unit']) ? $post['base_unit'] : ($product['base_unit'] ?? ''),
			'standard_quantity_per_unit' => isset($post['standard_quantity_per_unit']) ? $post['standard_quantity_per_unit'] : ($product['standard_quantity_per_unit'] ?? ''),
			'min_stock_level' => isset($post['min_stock_level']) ? $post['min_stock_level'] : ($product['min_stock_level'] ?? ''),
			'max_stock_level' => isset($post['max_stock_level']) ? $post['max_stock_level'] : ($product['max_stock_level'] ?? ''),
			'is_active' => isset($post['is_active']) ? $post['is_active'] : (string) ($product['is_active'] ?? '1'),
		);

		$this->setData('formdata', $formdata);
		$this->setData('statusOptions', $this->getStatusOptions());
		$this->pageTitle('Edit Product');
		return view('products/details', $this->viewdata);
	}

	public function view($id = null)
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$productModel = new ProductModel();
		$product = $productModel->findByID($id);
		if (!$product) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$this->setData('product', $product);
		$this->setData('statusOptions', $this->getStatusOptions());
		$this->pageTitle('View Product Details');
		return view('products/view', $this->viewdata);
	}

	public function delete($id = null)
	{
		if (!$this->isUserLoggedIn()) {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
		}

		$productModel = new ProductModel();
		$product = $productModel->findByID($id);
		if (!$product) {
			return $this->response->setJSON(['success' => false, 'message' => 'Product not found!']);
		}

		$productModel->delete($id);
		return $this->response->setJSON(['success' => true, 'message' => 'Product deleted successfully!']);
	}

	private function getStatusOptions()
	{
		return array(
			'1' => 'Active',
			'0' => 'Inactive',
		);
	}

    private function generateProductCode(ProductModel $productModel)
	{
		$prefix = 'PRD';
		$padLen = 6;

		$last = $productModel->select('product_id')->orderBy('product_id', 'DESC')->first();
		$nextId = (int) (($last['product_id'] ?? 0) + 1);
		if ($nextId < 1) {
			$nextId = 1;
		}

		// Find next available code (handles gaps + avoids collisions)
		for ($i = 0; $i < 50; $i++) {
			$code = $prefix . str_pad((string) $nextId, $padLen, '0', STR_PAD_LEFT);
			if (!$productModel->findByProductCode($code)) {
				return $code;
			}
			$nextId++;
		}

		// Fallback: timestamp-based unique code
		return $prefix . date('YmdHis');
	}
}


