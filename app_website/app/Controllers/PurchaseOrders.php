<?php namespace App\Controllers;

use App\Models\PurchaseOrderModel;
use App\Models\PurchaseOrderItemModel;
use App\Models\ProductModel;
use App\Models\VendorModel;
use App\Models\GodownModel;
use App\Models\QrCodeModel;
use App\Models\StockInventoryModel;
use App\Models\ExpiryTrackerModel;
use App\Libraries\ExcelExporter;
use App\Libraries\PDF;

class PurchaseOrders extends BaseController
{
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

	private function generatePONumber(PurchaseOrderModel $poModel)
	{
		$prefix = 'PO';
		$padLen = 6;

		$last = $poModel->select('po_id')->orderBy('po_id', 'DESC')->first();
		$nextId = (int) (($last['po_id'] ?? 0) + 1);
		if ($nextId < 1) {
			$nextId = 1;
		}

		for ($i = 0; $i < 50; $i++) {
			$poNumber = $prefix . str_pad((string) $nextId, $padLen, '0', STR_PAD_LEFT);
			if (!$poModel->findByPONumber($poNumber)) {
				return $poNumber;
			}
			$nextId++;
		}

		return $prefix . date('YmdHis');
	}

	private function getStatusOptions(PurchaseOrderModel $poModel)
	{
		// Try DB distinct values first (works for numeric or string statuses)
		$options = array();
		try {
			$rows = $poModel->select('status')->distinct()->orderBy('status', 'ASC')->findAll();
			if (!empty($rows)) {
				foreach ($rows as $r) {
					$val = isset($r['status']) ? (string) $r['status'] : '';
					if ($val === '') {
						continue;
					}
					$options[$val] = ucfirst($val);
				}
			}
		} catch (\Throwable $e) {
			// ignore, use fallback
		}

		if (!empty($options)) {
			return $options;
		}

		// Fallback defaults
		return array(
			'0' => 'Pending',
			'1' => 'Approved',
			'2' => 'Received',
			'3' => 'Cancelled',
			'pending' => 'Pending',
			'approved' => 'Approved',
			'received' => 'Received',
			'cancelled' => 'Cancelled',
		);
	}

	private function normalizeStatusLabel($status, $statusOptions)
	{
		$key = (string) $status;
		if (isset($statusOptions[$key])) {
			return $statusOptions[$key];
		}
		$keyLower = strtolower($key);
		if (isset($statusOptions[$keyLower])) {
			return $statusOptions[$keyLower];
		}
		return $key !== '' ? $key : '—';
	}

	public function index()
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$poModel = new PurchaseOrderModel();
		$recordsTotal = intval($this->getParam('recordstotal', 0));
		$recordsFiltered = intval($this->getParam('recordsfiltered', 0));
		$params = array();

		if ($recordsTotal === 0) {
			$params['count'] = true;
			$recordsTotal = $poModel->search($params);
			unset($params['count']);
		}

		if ($this->request->isAJAX()) {
			$draw = intval($this->getParam('draw', 1));
			$start = intval($this->getParam('start', 0));
			$length = intval($this->getParam('length', 50));
			$sorting = $this->getParam('order', array());
			$filter_keywords = trim($this->getParam('keywords', ''));
			$filter_status = trim($this->getParam('status', ''));

			if ($filter_keywords !== '') {
				$params['keywords'] = $filter_keywords;
			}
			if ($filter_status !== '') {
				$params['status'] = $filter_status;
			}

			if ($filter_keywords !== '' || $filter_status !== '') {
				$countParams = $params;
				$countParams['count'] = true;
				$recordsFiltered = $poModel->search($countParams);
				unset($countParams);
			} else {
				$recordsFiltered = $recordsTotal;
			}

			$params['limit'] = array('length' => $length, 'offset' => $start);

			if (!empty($sorting) && isset($sorting[0]['column'])) {
				$columnIndex = intval($sorting[0]['column']);
				$columnMap = array(
					0 => 'purchase_orders.po_number',
					1 => 'vendors.company_name',
					2 => 'purchase_orders.status',
					3 => 'purchase_orders.order_date',
					4 => 'purchase_orders.expected_delivery_date',
					5 => 'purchase_orders.final_amount',
					6 => 'purchase_orders.created_at',
				);
				if (isset($columnMap[$columnIndex])) {
					$params['sort'] = array(
						'column' => $columnMap[$columnIndex],
						'order' => isset($sorting[0]['dir']) && in_array(strtolower($sorting[0]['dir']), array('asc', 'desc')) ? $sorting[0]['dir'] : 'asc',
					);
				}
			}

			$statusOptions = $this->getStatusOptions($poModel);
			$pos = $poModel->search($params);
			$data = array();
			if (!empty($pos)) {
				foreach ($pos as $po) {
					$poId = $po['po_id'];
					$poNumber = $po['po_number'] ?? '';
					$vendorName = $po['vendor_company_name'] ?? '—';
					$statusLabel = $this->normalizeStatusLabel($po['status'] ?? '', $statusOptions);
					$orderDate = !empty($po['order_date']) ? date('d M Y', strtotime($po['order_date'])) : '—';
					$expectedDate = !empty($po['expected_delivery_date']) ? date('d M Y', strtotime($po['expected_delivery_date'])) : '—';
					$finalAmount = isset($po['final_amount']) && $po['final_amount'] !== null ? number_format((float) $po['final_amount'], 2) : '0.00';
					$createdAt = !empty($po['created_at']) ? date('d M Y', strtotime($po['created_at'])) : '—';

					$data[] = array(
						'id' => $poId,
						'po_number' => '<a href="' . site_url('purchase-orders/view/' . $poId) . '" class="fw-semibold text-reset">' . esc($poNumber) . '</a>',
						'vendor' => esc($vendorName),
						'status' => '<span class="badge bg-info-subtle text-info text-uppercase">' . esc($statusLabel) . '</span>',
						'order_date' => $orderDate,
						'expected_delivery_date' => $expectedDate,
						'final_amount' => $finalAmount,
						'created_at' => $createdAt,
						'actions' => '
							<div class="dropdown text-center">
								<button type="button" class="btn btn-sm btn-soft-info btn-icon fs-14" data-bs-toggle="dropdown" aria-expanded="false">
									<i class="ri-more-2-fill"></i>
								</button>
								<ul class="dropdown-menu dropdown-menu-end">
									<li><a class="dropdown-item" href="' . site_url('purchase-orders/view/' . $poId) . '"><i class="ri-eye-line align-middle me-1"></i> View</a></li>
									<li><a class="dropdown-item" href="' . site_url('purchase-orders/edit/' . $poId) . '"><i class="ri-edit-line align-middle me-1"></i> Edit</a></li>
									<li><a class="dropdown-item text-danger" data-action="delete" data-id="' . $poId . '" data-name="' . esc($poNumber) . '" href="javascript:void(0);"><i class="ri-delete-bin-line align-middle me-1"></i> Delete</a></li>
								</ul>
							</div>',
					);
				}
			}

			return $this->response(array('draw' => $draw, 'recordsTotal' => $recordsTotal, 'recordsFiltered' => $recordsFiltered, 'data' => $data));
		}

		$this->setData('filters', array(
			'keywords' => $this->getParam('keywords', ''),
			'status' => $this->getParam('status', ''),
		));
		$this->setData('statusOptions', $this->getStatusOptions($poModel));
		$this->pageTitle('Purchase Orders');
		$this->pageJs('assets/js/custom/purchase_orders.js?v=s' . $this->AppConfig->jsVersion);
		return view('purchase_orders/index', $this->viewdata);
	}

	public function export()
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$poModel = new PurchaseOrderModel();
		$params = array();
		$filter_keywords = trim($this->getParam('keywords', ''));
		$filter_status = trim($this->getParam('status', ''));

		if ($filter_keywords !== '') {
			$params['keywords'] = $filter_keywords;
		}
		if ($filter_status !== '') {
			$params['status'] = $filter_status;
		}

		$pos = $poModel->search($params);
		$statusOptions = $this->getStatusOptions($poModel);

		$exporter = new ExcelExporter();
		$sheet = $exporter->spreadsheet->getActiveSheet();

		$headers = array('PO Number', 'Vendor', 'Status', 'Order Date', 'Expected Delivery', 'Final Amount', 'Created At');
		$rows = array();

		if (!empty($pos)) {
			foreach ($pos as $po) {
				$statusLabel = $this->normalizeStatusLabel($po['status'] ?? '', $statusOptions);
				$rows[] = array(
					$po['po_number'] ?? '',
					$po['vendor_company_name'] ?? '',
					$statusLabel,
					$po['order_date'] ?? '',
					$po['expected_delivery_date'] ?? '',
					(isset($po['final_amount']) && $po['final_amount'] !== null) ? $po['final_amount'] : 0,
					$po['created_at'] ?? '',
				);
			}
		}

		$sheet->fromArray($headers, null, 'A1');
		if (!empty($rows)) {
			$sheet->fromArray($rows, null, 'A2');
		}
		$sheet->freezePane('A2');
		$sheet->getStyle('A1:G1')->getFont()->setBold(true);
		foreach (range('A', 'G') as $col) {
			$sheet->getColumnDimension($col)->setAutoSize(true);
		}

		$filename = 'purchase_orders_' . date('Ymd_His') . '.xlsx';
		$exporter->download($filename);
	}

	public function new()
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$poModel = new PurchaseOrderModel();
		$itemModel = new PurchaseOrderItemModel();
		$productModel = new ProductModel();
		$vendorModel = new VendorModel();
		$godownModel = new GodownModel();

		$error = '';
		$post = array();
		$itemsForRepopulate = array();

		if ($this->isPost()) {
			$post = esc($this->getPost());
			$isvalidrequest = true;

			if (!isset($post['po_number']) || trim($post['po_number']) === '') {
				$post['po_number'] = $this->generatePONumber($poModel);
			}

			if (!isset($post['vendor_id']) || (string) $post['vendor_id'] === '') {
				$error = "Please select vendor.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['godown_id']) || (string) $post['godown_id'] === '')) {
				$error = "Please select godown.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['status']) || (string) $post['status'] === '')) {
				$error = "Please select status.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['order_date']) || trim((string) $post['order_date']) === '')) {
				$error = "Please select order date.";
				$isvalidrequest = false;
			}

			$items = $this->collectItemsFromPost($post);
			$itemsForRepopulate = $this->hydrateItemsProductText($this->mapItemsForForm($items), $productModel);
			if ($isvalidrequest && empty($items)) {
				$error = "Please add at least one item.";
				$isvalidrequest = false;
			}

			if ($isvalidrequest) {
				$existing = $poModel->findByPONumber(trim($post['po_number']));
				if ($existing) {
					$post['po_number'] = $this->generatePONumber($poModel);
				}
			}

			if ($isvalidrequest) {
				$totals = $this->calculateTotals($items, $post);
				$created_at = date('Y-m-d H:i:s');

				$poId = $poModel->insert(array(
					'po_number' => trim($post['po_number']),
					'godown_id' => (int) $post['godown_id'],
					'vendor_id' => (int) $post['vendor_id'],
					'total_amount' => $totals['total_amount'],
					'tax_amount' => $totals['tax_amount'],
					'discount_amount' => $totals['discount_amount'],
					'final_amount' => $totals['final_amount'],
					'status' => $post['status'],
					'order_date' => $post['order_date'],
					'expected_delivery_date' => $post['expected_delivery_date'] ?? null,
					'actual_delivery_date' => $post['actual_delivery_date'] ?? null,
					'notes' => trim($post['notes'] ?? ''),
					'created_at' => $created_at,
				));

				if ($poId) {
					$hasItemTax = false;
					$hasItemDiscount = false;
					try {
						$hasItemTax = $itemModel->db->fieldExists('tax_amount', 'purchase_order_items');
						$hasItemDiscount = $itemModel->db->fieldExists('discount_amount', 'purchase_order_items');
					} catch (\Throwable $e) {
						$hasItemTax = false;
						$hasItemDiscount = false;
					}

					foreach ($items as $it) {
						$insert = array(
							'po_id' => $poId,
							'product_id' => (int) $it['product_id'],
							'quantity' => $it['quantity'],
							'unit_price' => $it['unit_price'],
							'total_price' => $it['total_price'],
						);
						if ($hasItemTax) {
							$insert['tax_amount'] = $it['tax_amount'] ?? 0;
						}
						if ($hasItemDiscount) {
							$insert['discount_amount'] = $it['discount_amount'] ?? 0;
						}
						$itemModel->insert($insert);
					}

					// Generate QR codes for each product item and quantity
					$qrCodeModel = new QrCodeModel();
					$expiryTrackerModel = new ExpiryTrackerModel();
					$this->generateQRCodesForPurchaseOrder($poId, trim($post['po_number']), (int) $post['vendor_id'], (int) $post['godown_id'], $items, $productModel, $qrCodeModel, $expiryTrackerModel);

					// Update stock inventory for the godown
					$stockInventoryModel = new StockInventoryModel();
					$godownId = (int) $post['godown_id'];
					$orderDate = !empty($post['order_date']) ? $post['order_date'] : date('Y-m-d');

					foreach ($items as $item) {
						$productId = (int) $item['product_id'];
						$quantity = (float) $item['quantity'];

						// Check if stock inventory already exists for this product and location
						$existingStock = $stockInventoryModel->findLatestByProductIDLocation($productId, 'godown', $godownId);

						if ($existingStock) {
							// Update existing stock quantity
							$newQuantity = (float) ($existingStock['quantity'] ?? 0) + $quantity;
							$stockInventoryModel->update($existingStock['stock_id'], array(
								'quantity' => $newQuantity,
								'stock_date' => $orderDate,
								'is_available' => 1,
							));
						} else {
							// Create new stock inventory entry
							$stockInventoryModel->insert(array(
								'product_id' => $productId,
								'location_type' => 'godown',
								'location_id' => $godownId,
								'quantity' => $quantity,
								'stock_date' => $orderDate,
								'is_available' => 1,
							));
						}
					}

					return redirect()->to('purchase-orders/view/' . $poId);
				}
				$error = 'Error creating purchase order. Please try again.';
			}
		}

		$vendorOptions = $vendorModel->search(array());
		usort($vendorOptions, function ($a, $b) {
			return strcmp((string) ($a['company_name'] ?? ''), (string) ($b['company_name'] ?? ''));
		});

		$formdata = array(
			'mode' => 'new',
			'id' => 0,
			'error' => $error,
			'po_number' => isset($post['po_number']) && $post['po_number'] !== '' ? $post['po_number'] : $this->generatePONumber($poModel),
			'vendor_id' => $post['vendor_id'] ?? '',
			'godown_id' => $post['godown_id'] ?? '',
			'status' => $post['status'] ?? '0',
			'order_date' => $post['order_date'] ?? date('Y-m-d'),
			'expected_delivery_date' => $post['expected_delivery_date'] ?? date('Y-m-d'),
			'actual_delivery_date' => $post['actual_delivery_date'] ?? date('Y-m-d'),
			'notes' => $post['notes'] ?? '',
			'tax_amount' => $post['tax_amount'] ?? '',
			'discount_amount' => $post['discount_amount'] ?? '',
			'items' => !empty($itemsForRepopulate) ? $itemsForRepopulate : array(),
		);

		$this->setData('formdata', $formdata);
		$this->setData('statusOptions', $this->getStatusOptions($poModel));
		$this->setData('vendorOptions', $vendorOptions);
		$this->setData('godownOptions', $godownModel->getActiveGodowns());
		// Products are loaded via Select2 AJAX after 3 chars (no preloading)
		$this->pageCss('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
		$this->pageJs('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js');
		$this->pageTitle('Add Purchase Order');
		$this->pageJs('assets/js/custom/purchase_orders.js?v=s' . $this->AppConfig->jsVersion);
		return view('purchase_orders/details', $this->viewdata);
	}

	public function edit($id = null)
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$poModel = new PurchaseOrderModel();
		$itemModel = new PurchaseOrderItemModel();
		$productModel = new ProductModel();
		$vendorModel = new VendorModel();
		$godownModel = new GodownModel();

		$po = $poModel->findByID($id);
		if (!$po) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$error = '';
		$post = array();
		$itemsForRepopulate = array();

		if ($this->isPost()) {
			$post = esc($this->getPost());
			$isvalidrequest = true;

			if (!isset($post['po_number']) || trim($post['po_number']) === '') {
				$error = "PO number is missing.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['vendor_id']) || (string) $post['vendor_id'] === '')) {
				$error = "Please select vendor.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['godown_id']) || (string) $post['godown_id'] === '')) {
				$error = "Please select godown.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['status']) || (string) $post['status'] === '')) {
				$error = "Please select status.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['order_date']) || trim((string) $post['order_date']) === '')) {
				$error = "Please select order date.";
				$isvalidrequest = false;
			}

			$items = $this->collectItemsFromPost($post);
			$itemsForRepopulate = $this->hydrateItemsProductText($this->mapItemsForForm($items), $productModel);
			if ($isvalidrequest && empty($items)) {
				$error = "Please add at least one item.";
				$isvalidrequest = false;
			}

			if ($isvalidrequest) {
				$dup = $poModel->findByPONumber(trim($post['po_number']));
				if ($dup && (int) ($dup['po_id'] ?? 0) !== (int) $id) {
					$error = "PO number already exists.";
					$isvalidrequest = false;
				}
			}

			if ($isvalidrequest) {
				$totals = $this->calculateTotals($items, $post);
				$result = $poModel->update($id, array(
					'po_number' => trim($post['po_number']),
					'godown_id' => (int) $post['godown_id'],
					'vendor_id' => (int) $post['vendor_id'],
					'total_amount' => $totals['total_amount'],
					'tax_amount' => $totals['tax_amount'],
					'discount_amount' => $totals['discount_amount'],
					'final_amount' => $totals['final_amount'],
					'status' => $post['status'],
					'order_date' => $post['order_date'],
					'expected_delivery_date' => $post['expected_delivery_date'] ?? null,
					'actual_delivery_date' => $post['actual_delivery_date'] ?? null,
					'notes' => trim($post['notes'] ?? ''),
				));

				if ($result) {
					// Replace items
					$itemModel->where('po_id', (int) $id)->delete();

					$hasItemTax = false;
					$hasItemDiscount = false;
					try {
						$hasItemTax = $itemModel->db->fieldExists('tax_amount', 'purchase_order_items');
						$hasItemDiscount = $itemModel->db->fieldExists('discount_amount', 'purchase_order_items');
					} catch (\Throwable $e) {
						$hasItemTax = false;
						$hasItemDiscount = false;
					}

					foreach ($items as $it) {
						$insert = array(
							'po_id' => (int) $id,
							'product_id' => (int) $it['product_id'],
							'quantity' => $it['quantity'],
							'unit_price' => $it['unit_price'],
							'total_price' => $it['total_price'],
						);
						if ($hasItemTax) {
							$insert['tax_amount'] = $it['tax_amount'] ?? 0;
						}
						if ($hasItemDiscount) {
							$insert['discount_amount'] = $it['discount_amount'] ?? 0;
						}
						$itemModel->insert($insert);
					}
					return redirect()->to('purchase-orders/view/' . $id);
				}
				$error = 'Error updating purchase order. Please try again.';
			}
		}

		$vendorOptions = $vendorModel->search(array());
		usort($vendorOptions, function ($a, $b) {
			return strcmp((string) ($a['company_name'] ?? ''), (string) ($b['company_name'] ?? ''));
		});

		$existingItems = $itemModel->findByPOID($id);
		$formdata = array(
			'mode' => 'edit',
			'id' => $po['po_id'],
			'error' => $error,
			'po_number' => isset($post['po_number']) ? $post['po_number'] : ($po['po_number'] ?? ''),
			'vendor_id' => isset($post['vendor_id']) ? $post['vendor_id'] : ($po['vendor_id'] ?? ''),
			'godown_id' => isset($post['godown_id']) ? $post['godown_id'] : ($po['godown_id'] ?? ''),
			'status' => isset($post['status']) ? $post['status'] : (($po['status'] ?? '') !== '' ? (string) $po['status'] : '0'),
			'order_date' => isset($post['order_date']) ? $post['order_date'] : ($po['order_date'] ?? ''),
			'expected_delivery_date' => isset($post['expected_delivery_date']) ? $post['expected_delivery_date'] : ($po['expected_delivery_date'] ?? ''),
			'actual_delivery_date' => isset($post['actual_delivery_date']) ? $post['actual_delivery_date'] : ($po['actual_delivery_date'] ?? ''),
			'notes' => isset($post['notes']) ? $post['notes'] : ($po['notes'] ?? ''),
			'tax_amount' => isset($post['tax_amount']) ? $post['tax_amount'] : ($po['tax_amount'] ?? ''),
			'discount_amount' => isset($post['discount_amount']) ? $post['discount_amount'] : ($po['discount_amount'] ?? ''),
			'items' => !empty($itemsForRepopulate) ? $itemsForRepopulate : $this->mapItemsForForm($existingItems),
		);

		$this->setData('formdata', $formdata);
		$this->setData('statusOptions', $this->getStatusOptions($poModel));
		$this->setData('vendorOptions', $vendorOptions);
		$this->setData('godownOptions', $godownModel->getActiveGodowns());
		// Products are loaded via Select2 AJAX after 3 chars (no preloading)
		$this->pageCss('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
		$this->pageJs('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js');
		$this->pageTitle('Edit Purchase Order');
		$this->pageJs('assets/js/custom/purchase_orders.js?v=s' . $this->AppConfig->jsVersion);
		return view('purchase_orders/details', $this->viewdata);
	}

	public function view($id = null)
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$poModel = new PurchaseOrderModel();
		$itemModel = new PurchaseOrderItemModel();

		$po = $poModel->findByID($id);
		if (!$po) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$items = $itemModel->findByPOID($id);
		$statusOptions = $this->getStatusOptions($poModel);

		$this->setData('po', $po);
		$this->setData('items', $items);
		$this->setData('statusLabel', $this->normalizeStatusLabel($po['status'] ?? '', $statusOptions));
		$this->pageTitle('View Purchase Order');
		return view('purchase_orders/view', $this->viewdata);
	}

	public function view_qrcodes($id = null)
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$poModel = new PurchaseOrderModel();
		$qrCodeModel = new QrCodeModel();

		$po = $poModel->findByID($id);
		if (!$po) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$qrCodes = $qrCodeModel->search(array('po_id' => $id));

		$this->setData('po', $po);
		$this->setData('qrCodes', $qrCodes);
		$this->pageTitle('Purchase Order QR Codes');
		return view('purchase_orders/qr_codes', $this->viewdata);
	}

	public function qrcodes_pdf($id = null)
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$poModel = new PurchaseOrderModel();
		$qrCodeModel = new QrCodeModel();

		$po = $poModel->findByID($id);
		if (!$po) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$qrCodes = $qrCodeModel->search(array('po_id' => $id));

		// Convert QR code images to base64 for PDF embedding
		$qrCodesWithImages = array();
		foreach ($qrCodes as $qr) {
			$qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($qr['qr_code']);

			// Download and convert to base64
			$imageData = @file_get_contents($qrImageUrl);
			if ($imageData !== false) {
				$qr['qr_image_base64'] = 'data:image/png;base64,' . base64_encode($imageData);
			} else {
				$qr['qr_image_base64'] = '';
			}

			$qrCodesWithImages[] = $qr;
		}

		$html = view('templates/documents/qr_code', array('po' => $po, 'qrCodes' => $qrCodesWithImages));

		$pdf = new PDF();
		$filename = 'qr_codes_po_' . $po['po_number'] . '_' . date('YmdHis') . '.pdf';
		$pdf->generate($html, array(
			'name' => $filename,
			'download' => true,
			'orientation' => 'portrait',
			'paper' => 'a4'
		));
	}

	public function delete($id = null)
	{
		if (!$this->isUserLoggedIn()) {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
		}

		$poModel = new PurchaseOrderModel();
		$itemModel = new PurchaseOrderItemModel();
		$qrCodeModel = new QrCodeModel();
		$stockInventoryModel = new StockInventoryModel();
		$expiryTrackerModel = new ExpiryTrackerModel();

		$po = $poModel->findByID($id);
		if (!$po) {
			return $this->response->setJSON(['success' => false, 'message' => 'Purchase order not found!']);
		}

		// Get purchase order items to revert stock
		$items = $itemModel->findByPOID($id);
		$godownId = (int) ($po['godown_id'] ?? 0);

		// Get all QR codes for this purchase order
		$qrCodes = $qrCodeModel->search(array('po_id' => $id));
		$qrIds = array();
		foreach ($qrCodes as $qr) {
			$qrIds[] = (int) $qr['qr_id'];
		}

		// Revert stock inventory
		if ($godownId > 0 && !empty($items)) {
			foreach ($items as $item) {
				$productId = (int) $item['product_id'];
				$quantity = (float) $item['quantity'];

				// Find existing stock inventory
				$existingStock = $stockInventoryModel->findLatestByProductIDLocation($productId, 'godown', $godownId);

				if ($existingStock) {
					$currentQuantity = (float) ($existingStock['quantity'] ?? 0);
					$newQuantity = $currentQuantity - $quantity;

					// If quantity becomes zero or negative, delete the stock entry
					if ($newQuantity <= 0) {
						$stockInventoryModel->delete($existingStock['stock_id']);
					} else {
						// Update stock quantity
						$stockInventoryModel->update($existingStock['stock_id'], array(
							'quantity' => $newQuantity,
						));
					}
				}
			}
		}

		// Delete expiry tracker entries for QR codes
		if (!empty($qrIds)) {
			foreach ($qrIds as $qrId) {
				$expiryTrackerModel->where('qr_id', $qrId)->delete();
			}
		}

		// Delete QR codes
		if (!empty($qrIds)) {
			foreach ($qrIds as $qrId) {
				$qrCodeModel->delete($qrId);
			}
		}

		// Delete purchase order items
		$itemModel->where('po_id', (int) $id)->delete();

		// Delete purchase order
		$poModel->delete($id);

		return $this->response->setJSON(['success' => true, 'message' => 'Purchase order deleted successfully!']);
	}

	private function collectItemsFromPost($post)
	{
		$items = array();
		$productIds = $post['item_product_id'] ?? array();
		$quantities = $post['item_quantity'] ?? array();
		$unitPrices = $post['item_unit_price'] ?? array();
		$taxAmounts = $post['item_tax_amount'] ?? array();
		$discountAmounts = $post['item_discount_amount'] ?? array();
		$expiryDates = $post['item_expiry_date'] ?? array();

		if (!is_array($productIds) || !is_array($quantities) || !is_array($unitPrices) || !is_array($taxAmounts) || !is_array($discountAmounts)) {
			return $items;
		}

		$count = max(count($productIds), count($quantities), count($unitPrices), count($taxAmounts), count($discountAmounts), count($expiryDates));
		for ($i = 0; $i < $count; $i++) {
			$pid = isset($productIds[$i]) ? (int) $productIds[$i] : 0;
			$qty = isset($quantities[$i]) && $quantities[$i] !== '' ? (float) $quantities[$i] : 0;
			$price = isset($unitPrices[$i]) && $unitPrices[$i] !== '' ? (float) $unitPrices[$i] : 0;
			$tax = isset($taxAmounts[$i]) && $taxAmounts[$i] !== '' ? (float) $taxAmounts[$i] : 0;
			$discount = isset($discountAmounts[$i]) && $discountAmounts[$i] !== '' ? (float) $discountAmounts[$i] : 0;
			$expiryDate = isset($expiryDates[$i]) && $expiryDates[$i] !== '' ? trim($expiryDates[$i]) : null;

			if ($pid <= 0 || $qty <= 0) {
				continue;
			}
			$baseTotal = $qty * $price;
			$total = $baseTotal + $tax - $discount;
			if ($total < 0) {
				$total = 0;
			}
			$items[] = array(
				'product_id' => $pid,
				'quantity' => $qty,
				'unit_price' => $price,
				'tax_amount' => $tax,
				'discount_amount' => $discount,
				'expiry_date' => $expiryDate,
				'base_total' => $baseTotal,
				'total_price' => $total,
			);
		}

		return $items;
	}

	private function calculateTotals($items, $post)
	{
		$subTotal = 0.0;
		$itemTaxTotal = 0.0;
		$itemDiscountTotal = 0.0;
		foreach ($items as $it) {
			$subTotal += (float) ($it['base_total'] ?? (($it['quantity'] ?? 0) * ($it['unit_price'] ?? 0)));
			$itemTaxTotal += (float) ($it['tax_amount'] ?? 0);
			$itemDiscountTotal += (float) ($it['discount_amount'] ?? 0);
		}

		// Discount on full purchase (in addition to per-item discounts)
		$purchaseDiscount = isset($post['discount_amount']) && $post['discount_amount'] !== '' ? (float) $post['discount_amount'] : 0.0;

		$final = $subTotal + $itemTaxTotal - $itemDiscountTotal - $purchaseDiscount;
		if ($final < 0) {
			$final = 0;
		}

		return array(
			'total_amount' => $subTotal,
			'tax_amount' => $itemTaxTotal,
			// Store purchase-level discount only (item discounts are stored per row when DB supports it)
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
				$out[] = array(
					'product_id' => $it['product_id'] ?? '',
					'product_text' => $productText,
					'quantity' => $it['quantity'] ?? '',
					'unit_price' => $it['unit_price'] ?? '',
					'tax_amount' => $it['tax_amount'] ?? '',
					'discount_amount' => $it['discount_amount'] ?? '',
				);
			}
		}
		return $out;
	}

	private function generateQRCodeString($pattern, $poNumber, $productId, $productCode, $vendorId, $quantityIndex, $timestamp = null)
	{
		if ($timestamp === null) {
			$timestamp = time();
		}

		$qrString = $pattern;
		$qrString = str_replace('{PO_NUMBER}', $poNumber, $qrString);
		$qrString = str_replace('{PRODUCT_ID}', (string) $productId, $qrString);
		$qrString = str_replace('{PRODUCT_CODE}', $productCode, $qrString);
		$qrString = str_replace('{VENDOR_ID}', (string) $vendorId, $qrString);
		$qrString = str_replace('{QUANTITY_INDEX}', (string) $quantityIndex, $qrString);
		$qrString = str_replace('{TIMESTAMP}', (string) $timestamp, $qrString);

		return $qrString;
	}

	private function generateQRCodesForPurchaseOrder($poId, $poNumber, $vendorId, $godownId, $items, ProductModel $productModel, QrCodeModel $qrCodeModel, ExpiryTrackerModel $expiryTrackerModel)
	{
		$pattern = $this->AppConfig->qrCodePattern ?? '{PO_NUMBER}-{PRODUCT_CODE}-{QUANTITY_INDEX}';

		foreach ($items as $item) {
			$productId = (int) $item['product_id'];
			$quantity = (float) $item['quantity'];
			$expiryDate = !empty($item['expiry_date']) ? $item['expiry_date'] : null;

			// Get product details
			$product = $productModel->findByID($productId);
			if (!$product) {
				continue;
			}

			$productCode = $product['product_code'] ?? 'PROD' . $productId;
			$unitPrice = (float) ($item['unit_price'] ?? 0);

			// Generate one QR code for each unit of quantity
			for ($i = 1; $i <= $quantity; $i++) {
				$qrString = $this->generateQRCodeString($pattern, $poNumber, $productId, $productCode, $vendorId, $i);

				// Check if QR code already exists
				$existing = $qrCodeModel->findByQRCode($qrString);
				if ($existing) {
					// If exists, append timestamp to make it unique
					$qrString = $this->generateQRCodeString($pattern, $poNumber, $productId, $productCode, $vendorId, $i, time());
				}

				$qrId = $qrCodeModel->insert(array(
					'qr_code' => $qrString,
					'po_id' => $poId,
					'product_id' => $productId,
					'vendor_id' => $vendorId,
					'original_quantity' => 1,
					'current_quantity' => 1,
					'purchase_price' => $unitPrice,
					'expiry_date' => $expiryDate,
					'status' => 'active',
					'created_at' => date('Y-m-d H:i:s'),
				));

				// Create expiry tracker entry if expiry_date is provided
				if ($qrId && $expiryDate && $godownId > 0) {
					$expiryTrackerModel->insert(array(
						'qr_id' => $qrId,
						'product_id' => $productId,
						'location_type' => 'godown',
						'location_id' => $godownId,
						'expiry_date' => $expiryDate,
					));
				}
			}
		}
	}
}


