<?php namespace App\Controllers;

use App\Models\SaleModel;
use App\Models\SaleItemModel;
use App\Models\ProductModel;
use App\Models\QrCodeModel;
use App\Models\CustomerModel;
use App\Models\GodownModel;
use App\Models\ShopModel;
use App\Libraries\ExcelExporter;
use App\Models\StockInventoryModel;

class Sales extends BaseController
{
	private function generateInvoiceNumber(SaleModel $saleModel)
	{
		$prefix = 'INV';
		$padLen = 6;

		$last = $saleModel->select('sale_id')->orderBy('sale_id', 'DESC')->first();
		$nextId = (int) (($last['sale_id'] ?? 0) + 1);
		if ($nextId < 1) {
			$nextId = 1;
		}

		for ($i = 0; $i < 50; $i++) {
			$inv = $prefix . str_pad((string) $nextId, $padLen, '0', STR_PAD_LEFT);
			if (!$saleModel->findByInvoiceNumber($inv)) {
				return $inv;
			}
			$nextId++;
		}

		return $prefix . date('YmdHis');
	}

	private function hydrateItemsProductText(array $items, ProductModel $productModel)
	{
		$ids = array();
		foreach ($items as $it) {
			$pid = (int) ($it['product_id'] ?? 0);
			if ($pid > 0) {
				$ids[$pid] = true;
			}
		}
		$ids = array_keys($ids);
		if (empty($ids)) {
			return $items;
		}

		$map = array();
		try {
			$products = $productModel->whereIn('product_id', $ids)->findAll();
			if (!empty($products)) {
				foreach ($products as $p) {
					$pid = (int) ($p['product_id'] ?? 0);
					if ($pid > 0) {
						$name = $p['product_name'] ?? '';
						$code = $p['product_code'] ?? '';
						$map[$pid] = trim($name . (!empty($code) ? ' (' . $code . ')' : ''));
					}
				}
			}
		} catch (\Throwable $e) {
			// ignore
		}

		foreach ($items as &$it) {
			$pid = (int) ($it['product_id'] ?? 0);
			if ($pid > 0 && (!isset($it['product_text']) || $it['product_text'] === '')) {
				$it['product_text'] = $map[$pid] ?? ('Product #' . $pid);
			}
		}
		unset($it);

		return $items;
	}

	private function hydrateCustomerText($customerId, CustomerModel $customerModel)
	{
		$customerId = (int) $customerId;
		if ($customerId <= 0) {
			return '';
		}
		$c = $customerModel->findByID($customerId);
		if (!$c) {
			return 'Customer #' . $customerId;
		}
		$name = $c['full_name'] ?? '';
		$phone = $c['phone'] ?? '';
		$email = $c['email'] ?? '';
		$meta = !empty($phone) ? $phone : $email;
		return trim($name . (!empty($meta) ? ' (' . $meta . ')' : ''));
	}

	private function getPaymentStatusOptions()
	{
		return array(
			'pending' => 'Pending',
			'paid' => 'Paid',
			'partial' => 'Partial',
			'unpaid' => 'Unpaid',
			'0' => 'Pending',
			'1' => 'Paid',
		);
	}

	private function getPaymentMethodOptions()
	{
		return array(
			'cash' => 'Cash',
			'card' => 'Card',
			'upi' => 'UPI',
			'bank' => 'Bank Transfer',
			'other' => 'Other',
		);
	}

	public function index()
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$saleModel = new SaleModel();
		$recordsTotal = intval($this->getParam('recordstotal', 0));
		$recordsFiltered = intval($this->getParam('recordsfiltered', 0));
		$params = array();

		if ($recordsTotal === 0) {
			$params['count'] = true;
			$recordsTotal = $saleModel->search($params);
			unset($params['count']);
		}

		if ($this->request->isAJAX()) {
			$draw = intval($this->getParam('draw', 1));
			$start = intval($this->getParam('start', 0));
			$length = intval($this->getParam('length', 50));
			$sorting = $this->getParam('order', array());
			$filter_keywords = trim($this->getParam('keywords', ''));
			$filter_payment_status = trim($this->getParam('payment_status', ''));

			if ($filter_keywords !== '') {
				$params['keywords'] = $filter_keywords;
			}
			if ($filter_payment_status !== '') {
				$params['payment_status'] = $filter_payment_status;
			}

			if ($filter_keywords !== '' || $filter_payment_status !== '') {
				$countParams = $params;
				$countParams['count'] = true;
				$recordsFiltered = $saleModel->search($countParams);
				unset($countParams);
			} else {
				$recordsFiltered = $recordsTotal;
			}

			$params['limit'] = array('length' => $length, 'offset' => $start);

			if (!empty($sorting) && isset($sorting[0]['column'])) {
				$columnIndex = intval($sorting[0]['column']);
				$columnMap = array(
					0 => 'sales.invoice_number',
					1 => 'cust_user.full_name',
					2 => 'sales.payment_status',
					3 => 'sales.sale_date',
					4 => 'sales.final_amount',
					5 => 'sales.created_at',
				);
				if (isset($columnMap[$columnIndex])) {
					$params['sort'] = array(
						'column' => $columnMap[$columnIndex],
						'order' => isset($sorting[0]['dir']) && in_array(strtolower($sorting[0]['dir']), array('asc', 'desc')) ? $sorting[0]['dir'] : 'asc',
					);
				}
			}

			$sales = $saleModel->search($params);
			$data = array();
			if (!empty($sales)) {
				foreach ($sales as $s) {
					$saleId = $s['sale_id'];
					$invoice = $s['invoice_number'] ?? '';
					$customer = $s['customer_name'] ?? '—';
					$paymentStatus = $s['payment_status'] ?? '—';
					$saleDate = !empty($s['sale_date']) ? date('d M Y', strtotime($s['sale_date'])) : '—';
					$final = isset($s['final_amount']) && $s['final_amount'] !== null ? number_format((float) $s['final_amount'], 2) : '0.00';
					$createdAt = !empty($s['created_at']) ? date('d M Y', strtotime($s['created_at'])) : '—';

					$data[] = array(
						'id' => $saleId,
						'invoice_number' => '<a href="' . site_url('sales/view/' . $saleId) . '" class="fw-semibold text-reset">' . esc($invoice) . '</a>',
						'customer' => esc($customer),
						'payment_status' => '<span class="badge bg-info-subtle text-info text-uppercase">' . esc((string) $paymentStatus) . '</span>',
						'sale_date' => $saleDate,
						'final_amount' => $final,
						'created_at' => $createdAt,
						'actions' => '
							<div class="dropdown text-center">
								<button type="button" class="btn btn-sm btn-soft-info btn-icon fs-14" data-bs-toggle="dropdown" aria-expanded="false">
									<i class="ri-more-2-fill"></i>
								</button>
								<ul class="dropdown-menu dropdown-menu-end">
									<li><a class="dropdown-item" href="' . site_url('sales/view/' . $saleId) . '"><i class="ri-eye-line align-middle me-1"></i> View</a></li>
									<li><a class="dropdown-item" href="' . site_url('sales/edit/' . $saleId) . '"><i class="ri-edit-line align-middle me-1"></i> Edit</a></li>
									<li><a class="dropdown-item text-danger" data-action="delete" data-id="' . $saleId . '" data-name="' . esc($invoice) . '" href="javascript:void(0);"><i class="ri-delete-bin-line align-middle me-1"></i> Delete</a></li>
								</ul>
							</div>',
					);
				}
			}

			return $this->response(array('draw' => $draw, 'recordsTotal' => $recordsTotal, 'recordsFiltered' => $recordsFiltered, 'data' => $data));
		}

		$this->setData('filters', array(
			'keywords' => $this->getParam('keywords', ''),
			'payment_status' => $this->getParam('payment_status', ''),
		));
		$this->setData('paymentStatusOptions', $this->getPaymentStatusOptions());
		$this->pageTitle('Sales');
		$this->pageJs('assets/js/custom/sales.js?v=s' . $this->AppConfig->jsVersion);
		return view('sales/index', $this->viewdata);
	}

	public function searchQr()
	{
		if (!$this->isUserLoggedIn()) {
			return $this->response->setJSON(['results' => []]);
		}

		$term = trim((string) $this->getParam('term', $this->getParam('q', '')));
		if (mb_strlen($term) < 3) {
			return $this->response->setJSON(['results' => []]);
		}

		$qrModel = new QrCodeModel();
		$params = array(
			'keywords' => $term,
			'limit' => array('length' => 20, 'offset' => 0),
			'sort' => array('column' => 'qr_codes.qr_code', 'order' => 'asc'),
		);
		$qrs = $qrModel->search($params);

		$results = array();
		if (!empty($qrs)) {
			foreach ($qrs as $q) {
				$code = $q['qr_code'] ?? '';
				$productName = $q['product_name'] ?? '';
				$text = trim($code . (!empty($productName) ? ' - ' . $productName : ''));
				$results[] = array(
					'id' => (int) $q['qr_id'],
					'text' => $text !== '' ? $text : ('QR #' . (int) $q['qr_id']),
				);
			}
		}

		return $this->response->setJSON(['results' => $results]);
	}

	public function qrInfo()
	{
		if (!$this->isUserLoggedIn()) {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
		}

		$qrId = (int) $this->getParam('qr_id', 0);
		$qrCode = trim((string) $this->getParam('qr_code', ''));
		$locationType = trim((string) $this->getParam('location_type', ''));
		$locationId = (int) $this->getParam('location_id', 0);

		$qrModel = new QrCodeModel();
		$qr = null;
		if ($qrId > 0) {
			$qr = $qrModel->findByID($qrId);
		} elseif ($qrCode !== '') {
			$qr = $qrModel->findByQRCode($qrCode);
		}

		if (!$qr) {
			return $this->response->setJSON(['success' => false, 'message' => 'QR code not found']);
		}

		// Check stock availability for this QR at selected location (if provided)
		$stockAvailable = true;
		$availableQuantity = null;
		if ($locationType !== '' && $locationId > 0) {
			$stockModel = new StockInventoryModel();
			$latestStock = $stockModel->findLatestByQRIDLocation($qr['qr_id'], $locationType, $locationId);
			if (!$latestStock || (isset($latestStock['is_available']) && (int)$latestStock['is_available'] === 0) || (isset($latestStock['quantity']) && (float)$latestStock['quantity'] <= 0)) {
				$stockAvailable = false;
				$availableQuantity = isset($latestStock['quantity']) ? (float)$latestStock['quantity'] : 0.0;
			} else {
				$availableQuantity = isset($latestStock['quantity']) ? (float)$latestStock['quantity'] : null;
			}
		}

		$productId = (int) ($qr['product_id'] ?? 0);
		$productName = $qr['product_name'] ?? '';
		$productCode = $qr['product_code'] ?? '';
		$productText = trim($productName . (!empty($productCode) ? ' (' . $productCode . ')' : ''));

		$data = array(
			'qr_id' => (int) $qr['qr_id'],
			'qr_code' => $qr['qr_code'] ?? '',
			'product_id' => $productId ?: null,
			'product_text' => $productText !== '' ? $productText : ($productId ? 'Product #' . $productId : ''),
			'mrp' => isset($qr['mrp']) ? (float) $qr['mrp'] : null,
			'stock_available' => $stockAvailable,
			'stock_quantity' => $availableQuantity,
		);

		return $this->response->setJSON([
			'success' => true,
			'data' => $data,
		]);
	}

	public function export()
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$saleModel = new SaleModel();
		$params = array();
		$filter_keywords = trim($this->getParam('keywords', ''));
		$filter_payment_status = trim($this->getParam('payment_status', ''));

		if ($filter_keywords !== '') {
			$params['keywords'] = $filter_keywords;
		}
		if ($filter_payment_status !== '') {
			$params['payment_status'] = $filter_payment_status;
		}

		$sales = $saleModel->search($params);
		$exporter = new ExcelExporter();
		$sheet = $exporter->spreadsheet->getActiveSheet();

		$headers = array('Invoice', 'Customer', 'Payment Status', 'Sale Date', 'Final Amount', 'Created At');
		$rows = array();
		if (!empty($sales)) {
			foreach ($sales as $s) {
				$rows[] = array(
					$s['invoice_number'] ?? '',
					$s['customer_name'] ?? '',
					$s['payment_status'] ?? '',
					$s['sale_date'] ?? '',
					(isset($s['final_amount']) && $s['final_amount'] !== null) ? $s['final_amount'] : 0,
					$s['created_at'] ?? '',
				);
			}
		}

		$sheet->fromArray($headers, null, 'A1');
		if (!empty($rows)) {
			$sheet->fromArray($rows, null, 'A2');
		}
		$sheet->freezePane('A2');
		$sheet->getStyle('A1:F1')->getFont()->setBold(true);
		foreach (range('A', 'F') as $col) {
			$sheet->getColumnDimension($col)->setAutoSize(true);
		}

		$filename = 'sales_' . date('Ymd_His') . '.xlsx';
		$exporter->download($filename);
	}

	public function new()
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$saleModel = new SaleModel();
		$itemModel = new SaleItemModel();
		$productModel = new ProductModel();
		$customerModel = new CustomerModel();
		$godownModel = new GodownModel();
		$shopModel = new ShopModel();

		$error = '';
		$post = array();
		$itemsForRepopulate = array();
		$customerText = '';

		if ($this->isPost()) {
			$post = esc($this->getPost());
			$isvalidrequest = true;

			if (!isset($post['invoice_number']) || trim($post['invoice_number']) === '') {
				$post['invoice_number'] = $this->generateInvoiceNumber($saleModel);
			}

			if (!isset($post['customer_id']) || (string) $post['customer_id'] === '') {
				$error = "Please select customer.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['location_type']) || !in_array($post['location_type'], array('godown', 'shop'), true))) {
				$error = "Please select location type.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['location_id']) || (string) $post['location_id'] === '')) {
				$error = "Please select location.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['payment_status']) || trim((string) $post['payment_status']) === '')) {
				$error = "Please select payment status.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['sale_date']) || trim((string) $post['sale_date']) === '')) {
				$error = "Please select sale date.";
				$isvalidrequest = false;
			}

			$items = $this->collectItemsFromPost($post);
			$itemsForRepopulate = $this->hydrateItemsProductText($this->mapItemsForForm($items), $productModel);
			if ($isvalidrequest && empty($items)) {
				$error = "Please add at least one item.";
				$isvalidrequest = false;
			}

			if ($isvalidrequest) {
				$dup = $saleModel->findByInvoiceNumber(trim($post['invoice_number']));
				if ($dup) {
					$post['invoice_number'] = $this->generateInvoiceNumber($saleModel);
				}
			}

			if ($isvalidrequest) {
				$totals = $this->calculateTotals($items, $post);
				$created_at = date('Y-m-d H:i:s');

				$saleId = $saleModel->insert(array(
					'invoice_number' => trim($post['invoice_number']),
					'location_type' => $post['location_type'],
					'location_id' => (int) $post['location_id'],
					'customer_id' => (int) $post['customer_id'],
					'total_amount' => $totals['total_amount'],
					'tax_amount' => $totals['tax_amount'],
					'discount_amount' => $totals['discount_amount'],
					'final_amount' => $totals['final_amount'],
					'payment_method' => $post['payment_method'] ?? null,
					'payment_status' => $post['payment_status'],
					'sale_date' => $post['sale_date'],
					'sold_by' => (int) ($this->_user['id'] ?? 0),
					'notes' => trim($post['notes'] ?? ''),
					'created_at' => $created_at,
				));

				if ($saleId) {
					$hasItemTaxPercent = false;
					try {
						$hasItemTaxPercent = $itemModel->db->fieldExists('tax_percent', 'sale_items');
					} catch (\Throwable $e) {
						$hasItemTaxPercent = false;
					}

					foreach ($items as $it) {
						$insert = array(
							'sale_id' => $saleId,
							'qr_id' => $it['qr_id'] ?? null,
							'product_id' => (int) $it['product_id'],
							'quantity' => $it['quantity'],
							'unit_price' => $it['unit_price'],
							'total_price' => $it['total_price'],
							'discount_percent' => $it['discount_percent'],
						);
						if ($hasItemTaxPercent) {
							$insert['tax_percent'] = $it['tax_percent'] ?? 0;
						}
						$itemModel->insert($insert);
					}
					return redirect()->to('sales/view/' . $saleId);
				}
				$error = 'Error creating sale. Please try again.';
			}
		}

		if (!empty($post['customer_id'])) {
			$customerText = $this->hydrateCustomerText($post['customer_id'], $customerModel);
		}

		$formdata = array(
			'mode' => 'new',
			'id' => 0,
			'error' => $error,
			'invoice_number' => isset($post['invoice_number']) && $post['invoice_number'] !== '' ? $post['invoice_number'] : $this->generateInvoiceNumber($saleModel),
			'customer_id' => $post['customer_id'] ?? '',
			'customer_text' => $customerText,
			'location_type' => $post['location_type'] ?? 'shop',
			'location_id' => $post['location_id'] ?? '',
			'payment_method' => $post['payment_method'] ?? 'cash',
			'payment_status' => $post['payment_status'] ?? 'pending',
			'sale_date' => $post['sale_date'] ?? date('Y-m-d'),
			'tax_amount' => $post['tax_amount'] ?? '',
			'discount_amount' => $post['discount_amount'] ?? '',
			'notes' => $post['notes'] ?? '',
			'items' => !empty($itemsForRepopulate) ? $itemsForRepopulate : array(),
		);

		$this->setData('formdata', $formdata);
		$this->setData('paymentStatusOptions', $this->getPaymentStatusOptions());
		$this->setData('paymentMethodOptions', $this->getPaymentMethodOptions());
		$this->setData('godownOptions', $godownModel->getActiveGodowns());
		$this->setData('shopOptions', $shopModel->getActiveShops());
		// Select2 for customer + product
		$this->pageCss('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
		$this->pageJs('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js');
		$this->pageTitle('Add Sale');
		$this->pageJs('assets/js/custom/sales.js?v=s' . $this->AppConfig->jsVersion);
		return view('sales/details', $this->viewdata);
	}

	public function edit($id = null)
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$saleModel = new SaleModel();
		$itemModel = new SaleItemModel();
		$productModel = new ProductModel();
		$customerModel = new CustomerModel();
		$godownModel = new GodownModel();
		$shopModel = new ShopModel();

		$sale = $saleModel->findByID($id);
		if (!$sale) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$error = '';
		$post = array();
		$itemsForRepopulate = array();

		if ($this->isPost()) {
			$post = esc($this->getPost());
			$isvalidrequest = true;

			if (!isset($post['invoice_number']) || trim($post['invoice_number']) === '') {
				$error = "Invoice number is missing.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['customer_id']) || (string) $post['customer_id'] === '')) {
				$error = "Please select customer.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['location_type']) || !in_array($post['location_type'], array('godown', 'shop'), true))) {
				$error = "Please select location type.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['location_id']) || (string) $post['location_id'] === '')) {
				$error = "Please select location.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['payment_status']) || trim((string) $post['payment_status']) === '')) {
				$error = "Please select payment status.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['sale_date']) || trim((string) $post['sale_date']) === '')) {
				$error = "Please select sale date.";
				$isvalidrequest = false;
			}

			$items = $this->collectItemsFromPost($post);
			$itemsForRepopulate = $this->hydrateItemsProductText($this->mapItemsForForm($items), $productModel);
			if ($isvalidrequest && empty($items)) {
				$error = "Please add at least one item.";
				$isvalidrequest = false;
			}

			if ($isvalidrequest) {
				$dup = $saleModel->findByInvoiceNumber(trim($post['invoice_number']));
				if ($dup && (int) ($dup['sale_id'] ?? 0) !== (int) $id) {
					$error = "Invoice number already exists.";
					$isvalidrequest = false;
				}
			}

			if ($isvalidrequest) {
				$totals = $this->calculateTotals($items, $post);
				$result = $saleModel->update($id, array(
					'invoice_number' => trim($post['invoice_number']),
					'location_type' => $post['location_type'],
					'location_id' => (int) $post['location_id'],
					'customer_id' => (int) $post['customer_id'],
					'total_amount' => $totals['total_amount'],
					'tax_amount' => $totals['tax_amount'],
					'discount_amount' => $totals['discount_amount'],
					'final_amount' => $totals['final_amount'],
					'payment_method' => $post['payment_method'] ?? null,
					'payment_status' => $post['payment_status'],
					'sale_date' => $post['sale_date'],
					'notes' => trim($post['notes'] ?? ''),
				));

				if ($result) {
					$itemModel->where('sale_id', (int) $id)->delete();
					$hasItemTaxPercent = false;
					try {
						$hasItemTaxPercent = $itemModel->db->fieldExists('tax_percent', 'sale_items');
					} catch (\Throwable $e) {
						$hasItemTaxPercent = false;
					}
					foreach ($items as $it) {
						$insert = array(
							'sale_id' => (int) $id,
							'qr_id' => $it['qr_id'] ?? null,
							'product_id' => (int) $it['product_id'],
							'quantity' => $it['quantity'],
							'unit_price' => $it['unit_price'],
							'total_price' => $it['total_price'],
							'discount_percent' => $it['discount_percent'],
						);
						if ($hasItemTaxPercent) {
							$insert['tax_percent'] = $it['tax_percent'] ?? 0;
						}
						$itemModel->insert($insert);
					}
					return redirect()->to('sales/view/' . $id);
				}
				$error = 'Error updating sale. Please try again.';
			}
		}

		$existingItems = $itemModel->findBySaleID($id);
		$formdata = array(
			'mode' => 'edit',
			'id' => $sale['sale_id'],
			'error' => $error,
			'invoice_number' => isset($post['invoice_number']) ? $post['invoice_number'] : ($sale['invoice_number'] ?? ''),
			'customer_id' => isset($post['customer_id']) ? $post['customer_id'] : ($sale['customer_id'] ?? ''),
			'customer_text' => $this->hydrateCustomerText(isset($post['customer_id']) ? $post['customer_id'] : ($sale['customer_id'] ?? ''), $customerModel),
			'location_type' => isset($post['location_type']) ? $post['location_type'] : ($sale['location_type'] ?? 'shop'),
			'location_id' => isset($post['location_id']) ? $post['location_id'] : ($sale['location_id'] ?? ''),
			'payment_method' => isset($post['payment_method']) ? $post['payment_method'] : ($sale['payment_method'] ?? 'cash'),
			'payment_status' => isset($post['payment_status']) ? $post['payment_status'] : ($sale['payment_status'] ?? 'pending'),
			'sale_date' => isset($post['sale_date']) ? $post['sale_date'] : ($sale['sale_date'] ?? date('Y-m-d')),
			'tax_amount' => isset($post['tax_amount']) ? $post['tax_amount'] : ($sale['tax_amount'] ?? ''),
			'discount_amount' => isset($post['discount_amount']) ? $post['discount_amount'] : ($sale['discount_amount'] ?? ''),
			'notes' => isset($post['notes']) ? $post['notes'] : ($sale['notes'] ?? ''),
			'items' => !empty($itemsForRepopulate) ? $itemsForRepopulate : $this->mapItemsForForm($existingItems),
		);

		$this->setData('formdata', $formdata);
		$this->setData('paymentStatusOptions', $this->getPaymentStatusOptions());
		$this->setData('paymentMethodOptions', $this->getPaymentMethodOptions());
		$this->setData('godownOptions', $godownModel->getActiveGodowns());
		$this->setData('shopOptions', $shopModel->getActiveShops());
		$this->pageCss('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
		$this->pageJs('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js');
		$this->pageTitle('Edit Sale');
		$this->pageJs('assets/js/custom/sales.js?v=s' . $this->AppConfig->jsVersion);
		return view('sales/details', $this->viewdata);
	}

	public function view($id = null)
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$saleModel = new SaleModel();
		$itemModel = new SaleItemModel();

		$sale = $saleModel->findByID($id);
		if (!$sale) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$items = $itemModel->findBySaleID($id);
		$this->setData('sale', $sale);
		$this->setData('items', $items);
		$this->pageTitle('View Sale');
		return view('sales/view', $this->viewdata);
	}

	public function delete($id = null)
	{
		if (!$this->isUserLoggedIn()) {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
		}

		$saleModel = new SaleModel();
		$itemModel = new SaleItemModel();
		$sale = $saleModel->findByID($id);
		if (!$sale) {
			return $this->response->setJSON(['success' => false, 'message' => 'Sale not found!']);
		}

		$itemModel->where('sale_id', (int) $id)->delete();
		$saleModel->delete($id);
		return $this->response->setJSON(['success' => true, 'message' => 'Sale deleted successfully!']);
	}

	private function collectItemsFromPost($post)
	{
		$items = array();
		$productIds = $post['item_product_id'] ?? array();
		$qrIds = $post['item_qr_id'] ?? array();
		$quantities = $post['item_quantity'] ?? array();
		$unitPrices = $post['item_unit_price'] ?? array();
		$discountPercents = $post['item_discount_percent'] ?? array();
		$taxPercents = $post['item_tax_percent'] ?? array();

		if (!is_array($productIds) || !is_array($quantities) || !is_array($unitPrices) || !is_array($discountPercents) || !is_array($taxPercents)) {
			return $items;
		}

		$count = max(count($productIds), count($qrIds), count($quantities), count($unitPrices), count($discountPercents), count($taxPercents));
		for ($i = 0; $i < $count; $i++) {
			$pid = isset($productIds[$i]) ? (int) $productIds[$i] : 0;
			$qid = isset($qrIds[$i]) && $qrIds[$i] !== '' ? (int) $qrIds[$i] : null;
			$qty = isset($quantities[$i]) && $quantities[$i] !== '' ? (float) $quantities[$i] : 0;
			$price = isset($unitPrices[$i]) && $unitPrices[$i] !== '' ? (float) $unitPrices[$i] : 0;
			$discPct = isset($discountPercents[$i]) && $discountPercents[$i] !== '' ? (float) $discountPercents[$i] : 0;
			$taxPct = isset($taxPercents[$i]) && $taxPercents[$i] !== '' ? (float) $taxPercents[$i] : 0;

			if ($pid <= 0 || $qty <= 0) {
				continue;
			}
			if ($discPct < 0) $discPct = 0;
			if ($discPct > 100) $discPct = 100;
			if ($taxPct < 0) $taxPct = 0;
			if ($taxPct > 100) $taxPct = 100;

			$base = $qty * $price;
			$disc = $base * ($discPct / 100);
			$net = $base - $disc;
			if ($net < 0) $net = 0;
			$tax = $net * ($taxPct / 100);
			$total = $net + $tax;
			if ($total < 0) $total = 0;

			$items[] = array(
				'qr_id' => $qid,
				'product_id' => $pid,
				'quantity' => $qty,
				'unit_price' => $price,
				'discount_percent' => $discPct,
				'tax_percent' => $taxPct,
				'base_total' => $base,
				'discount_value' => $disc,
				'tax_value' => $tax,
				'net_total' => $net,
				'total_price' => $total,
			);
		}

		return $items;
	}

	private function calculateTotals($items, $post)
	{
		$subTotal = 0.0; // net before tax (after item discount)
		$itemTaxTotal = 0.0;
		foreach ($items as $it) {
			$subTotal += (float) ($it['net_total'] ?? 0);
			$itemTaxTotal += (float) ($it['tax_value'] ?? 0);
		}

		$purchaseDiscount = isset($post['discount_amount']) && $post['discount_amount'] !== '' ? (float) $post['discount_amount'] : 0.0;

		$final = $subTotal + $itemTaxTotal - $purchaseDiscount;
		if ($final < 0) $final = 0;

		return array(
			'total_amount' => $subTotal,
			'tax_amount' => $itemTaxTotal,
			'discount_amount' => $purchaseDiscount,
			'final_amount' => $final,
		);
	}

	private function mapItemsForForm($items)
	{
		$out = array();
		if (!empty($items)) {
			foreach ($items as $it) {
				$productName = $it['product_name'] ?? '';
				$productCode = $it['product_code'] ?? '';
				$productText = trim($productName . (!empty($productCode) ? ' (' . $productCode . ')' : ''));
				$qrCode = $it['qr_code'] ?? '';
				$out[] = array(
					'qr_id' => $it['qr_id'] ?? '',
					'qr_code' => $qrCode,
					'qr_text' => $qrCode,
					'product_id' => $it['product_id'] ?? '',
					'product_text' => $productText,
					'quantity' => $it['quantity'] ?? '',
					'unit_price' => $it['unit_price'] ?? '',
					'discount_percent' => $it['discount_percent'] ?? '',
					'tax_percent' => $it['tax_percent'] ?? '',
				);
			}
		}
		return $out;
	}
}


