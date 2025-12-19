<?php namespace App\Controllers;

use App\Models\StockTransferModel;
use App\Models\StockTransferItemModel;
use App\Models\QrCodeModel;
use App\Models\ProductModel;
use App\Models\GodownModel;
use App\Models\ShopModel;
use App\Models\StockInventoryModel;
use App\Libraries\ExcelExporter;

class StockTransfers extends BaseController
{
	private function generateTransferNumber(StockTransferModel $transferModel)
	{
		$prefix = 'TRF';
		$padLen = 6;

		$last = $transferModel->select('transfer_id')->orderBy('transfer_id', 'DESC')->first();
		$nextId = (int) (($last['transfer_id'] ?? 0) + 1);
		if ($nextId < 1) {
			$nextId = 1;
		}

		for ($i = 0; $i < 50; $i++) {
			$trf = $prefix . str_pad((string) $nextId, $padLen, '0', STR_PAD_LEFT);
			if (!$transferModel->findByTransferNumber($trf)) {
				return $trf;
			}
			$nextId++;
		}

		return $prefix . date('YmdHis');
	}

	private function getStatusOptions()
	{
		return array(
			'pending' => 'Pending',
			'dispatched' => 'Dispatched',
			'delivered' => 'Delivered',
			'cancelled' => 'Cancelled',
		);
	}

	public function index()
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$transferModel = new StockTransferModel();
		$recordsTotal = intval($this->getParam('recordstotal', 0));
		$recordsFiltered = intval($this->getParam('recordsfiltered', 0));
		$params = array();

		if ($recordsTotal === 0) {
			$params['count'] = true;
			$recordsTotal = $transferModel->search($params);
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
				$recordsFiltered = $transferModel->search($countParams);
				unset($countParams);
			} else {
				$recordsFiltered = $recordsTotal;
			}

			$params['limit'] = array('length' => $length, 'offset' => $start);

			if (!empty($sorting) && isset($sorting[0]['column'])) {
				$columnIndex = intval($sorting[0]['column']);
				$columnMap = array(
					0 => 'stock_transfers.transfer_number',
					1 => 'stock_transfers.from_location_type',
					2 => 'stock_transfers.to_location_type',
					3 => 'stock_transfers.status',
					4 => 'stock_transfers.dispatch_date',
					5 => 'stock_transfers.created_at',
				);
				if (isset($columnMap[$columnIndex])) {
					$params['sort'] = array(
						'column' => $columnMap[$columnIndex],
						'order' => isset($sorting[0]['dir']) && in_array(strtolower($sorting[0]['dir']), array('asc', 'desc')) ? $sorting[0]['dir'] : 'asc',
					);
				}
			}

			$transfers = $transferModel->search($params);
			$data = array();
			if (!empty($transfers)) {
				foreach ($transfers as $t) {
					$transferId = $t['transfer_id'];
					$transferNumber = $t['transfer_number'] ?? '';

					$fromLocation = '—';
					if ($t['from_location_type'] === 'godown' && !empty($t['from_godown_name'])) {
						$fromLocation = $t['from_godown_name'];
					} elseif ($t['from_location_type'] === 'shop' && !empty($t['from_shop_name'])) {
						$fromLocation = $t['from_shop_name'];
					}

					$toLocation = '—';
					if ($t['to_location_type'] === 'godown' && !empty($t['to_godown_name'])) {
						$toLocation = $t['to_godown_name'];
					} elseif ($t['to_location_type'] === 'shop' && !empty($t['to_shop_name'])) {
						$toLocation = $t['to_shop_name'];
					}

					$status = $t['status'] ?? '—';
					$dispatchDate = !empty($t['dispatch_date']) ? date('d M Y', strtotime($t['dispatch_date'])) : '—';
					$createdAt = !empty($t['created_at']) ? date('d M Y', strtotime($t['created_at'])) : '—';

					$data[] = array(
						'id' => $transferId,
						'transfer_number' => '<a href="' . site_url('stock-transfers/view/' . $transferId) . '" class="fw-semibold text-reset">' . esc($transferNumber) . '</a>',
						'from_location' => esc($fromLocation),
						'to_location' => esc($toLocation),
						'status' => '<span class="badge bg-info-subtle text-info text-uppercase">' . esc((string) $status) . '</span>',
						'dispatch_date' => $dispatchDate,
						'created_at' => $createdAt,
						'actions' => '
							<div class="dropdown text-center">
								<button type="button" class="btn btn-sm btn-soft-info btn-icon fs-14" data-bs-toggle="dropdown" aria-expanded="false">
									<i class="ri-more-2-fill"></i>
								</button>
								<ul class="dropdown-menu dropdown-menu-end">
									<li><a class="dropdown-item" href="' . site_url('stock-transfers/view/' . $transferId) . '"><i class="ri-eye-line align-middle me-1"></i> View</a></li>
									<li><a class="dropdown-item" href="' . site_url('stock-transfers/edit/' . $transferId) . '"><i class="ri-edit-line align-middle me-1"></i> Edit</a></li>
									<li><a class="dropdown-item text-danger" data-action="delete" data-id="' . $transferId . '" data-name="' . esc($transferNumber) . '" href="javascript:void(0);"><i class="ri-delete-bin-line align-middle me-1"></i> Delete</a></li>
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
		$this->setData('statusOptions', $this->getStatusOptions());
		$this->pageTitle('Stock Transfers');
		$this->pageJs('assets/js/custom/stock-transfers.js?v=s' . $this->AppConfig->jsVersion);
		return view('stock_transfers/index', $this->viewdata);
	}

	public function searchQr()
	{
		if (!$this->isUserLoggedIn()) {
			return $this->response->setJSON(['results' => []]);
		}

		$term = trim((string) $this->getParam('term', $this->getParam('q', '')));
		$fromLocationType = trim((string) $this->getParam('from_location_type', ''));
		$fromLocationId = (int) $this->getParam('from_location_id', 0);

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
			$stockModel = new StockInventoryModel();
			foreach ($qrs as $q) {
				// Check if QR has available stock at from location
				if ($fromLocationType !== '' && $fromLocationId > 0) {
					$latestStock = $stockModel->findLatestByQRIDLocation($q['qr_id'], $fromLocationType, $fromLocationId);
					if (
						!$latestStock
						|| (isset($latestStock['is_available']) && (int) $latestStock['is_available'] === 0)
						|| (isset($latestStock['quantity']) && (float) $latestStock['quantity'] <= 0)
					) {
						continue; // Skip if not available
					}
				}

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
		$fromLocationType = trim((string) $this->getParam('from_location_type', ''));
		$fromLocationId = (int) $this->getParam('from_location_id', 0);

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

		// Check stock availability at from location
		$stockAvailable = false;
		$availableQuantity = 0.0;
		$sourceStockId = null;
		if ($fromLocationType !== '' && $fromLocationId > 0) {
			$stockModel = new StockInventoryModel();
			$latestStock = $stockModel->findLatestByQRIDLocation($qr['qr_id'], $fromLocationType, $fromLocationId);
			if (
				$latestStock
				&& (isset($latestStock['is_available']) && (int) $latestStock['is_available'] === 1)
				&& (isset($latestStock['quantity']) && (float) $latestStock['quantity'] > 0)
			) {
				$stockAvailable = true;
				$availableQuantity = (float) $latestStock['quantity'];
				$sourceStockId = (int) $latestStock['stock_id'];
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
			'stock_available' => $stockAvailable,
			'stock_quantity' => $availableQuantity,
			'source_stock_id' => $sourceStockId,
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

		$transferModel = new StockTransferModel();
		$params = array();
		$filter_keywords = trim($this->getParam('keywords', ''));
		$filter_status = trim($this->getParam('status', ''));

		if ($filter_keywords !== '') {
			$params['keywords'] = $filter_keywords;
		}
		if ($filter_status !== '') {
			$params['status'] = $filter_status;
		}

		$transfers = $transferModel->search($params);
		$exporter = new ExcelExporter();
		$sheet = $exporter->spreadsheet->getActiveSheet();

		$headers = array('Transfer Number', 'From Location', 'To Location', 'Status', 'Dispatch Date', 'Delivery Date', 'Total Items', 'Created At');
		$rows = array();
		if (!empty($transfers)) {
			foreach ($transfers as $t) {
				$fromLocation = '—';
				if ($t['from_location_type'] === 'godown' && !empty($t['from_godown_name'])) {
					$fromLocation = $t['from_godown_name'];
				} elseif ($t['from_location_type'] === 'shop' && !empty($t['from_shop_name'])) {
					$fromLocation = $t['from_shop_name'];
				}

				$toLocation = '—';
				if ($t['to_location_type'] === 'godown' && !empty($t['to_godown_name'])) {
					$toLocation = $t['to_godown_name'];
				} elseif ($t['to_location_type'] === 'shop' && !empty($t['to_shop_name'])) {
					$toLocation = $t['to_shop_name'];
				}

				$rows[] = array(
					$t['transfer_number'] ?? '',
					$fromLocation,
					$toLocation,
					$t['status'] ?? '',
					$t['dispatch_date'] ?? '',
					$t['delivery_date'] ?? '',
					$t['total_items'] ?? 0,
					$t['created_at'] ?? '',
				);
			}
		}

		$sheet->fromArray($headers, null, 'A1');
		if (!empty($rows)) {
			$sheet->fromArray($rows, null, 'A2');
		}
		$sheet->freezePane('A2');
		$sheet->getStyle('A1:H1')->getFont()->setBold(true);
		foreach (range('A', 'H') as $col) {
			$sheet->getColumnDimension($col)->setAutoSize(true);
		}

		$filename = 'stock_transfers_' . date('Ymd_His') . '.xlsx';
		$exporter->download($filename);
	}

	public function new()
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$transferModel = new StockTransferModel();
		$itemModel = new StockTransferItemModel();
		$godownModel = new GodownModel();
		$shopModel = new ShopModel();

		$error = '';
		$post = array();
		$itemsForRepopulate = array();

		if ($this->isPost()) {
			$post = esc($this->getPost());
			$isvalidrequest = true;

			if (!isset($post['transfer_number']) || trim($post['transfer_number']) === '') {
				$post['transfer_number'] = $this->generateTransferNumber($transferModel);
			}

			if ($isvalidrequest && (!isset($post['from_location_type']) || !in_array($post['from_location_type'], array('godown', 'shop'), true))) {
				$error = "Please select from location type.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['from_location_id']) || (string) $post['from_location_id'] === '')) {
				$error = "Please select from location.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['to_location_type']) || !in_array($post['to_location_type'], array('godown', 'shop'), true))) {
				$error = "Please select to location type.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['to_location_id']) || (string) $post['to_location_id'] === '')) {
				$error = "Please select to location.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['status']) || trim((string) $post['status']) === '')) {
				$error = "Please select status.";
				$isvalidrequest = false;
			}

			$items = $this->collectItemsFromPost($post);
			if ($isvalidrequest && empty($items)) {
				$error = "Please add at least one item.";
				$isvalidrequest = false;
			}

			if ($isvalidrequest) {
				$dup = $transferModel->findByTransferNumber(trim($post['transfer_number']));
				if ($dup) {
					$post['transfer_number'] = $this->generateTransferNumber($transferModel);
				}
			}

			if ($isvalidrequest) {
				$created_at = date('Y-m-d H:i:s');
				$totalItems = count($items);

				$transferId = $transferModel->insert(array(
					'transfer_number' => trim($post['transfer_number']),
					'from_location_type' => $post['from_location_type'],
					'from_location_id' => (int) $post['from_location_id'],
					'to_location_type' => $post['to_location_type'],
					'to_location_id' => (int) $post['to_location_id'],
					'request_id' => isset($post['request_id']) && $post['request_id'] !== '' ? (int) $post['request_id'] : null,
					'total_items' => $totalItems,
					'status' => $post['status'],
					'dispatch_date' => isset($post['dispatch_date']) && $post['dispatch_date'] !== '' ? $post['dispatch_date'] : null,
					'delivery_date' => isset($post['delivery_date']) && $post['delivery_date'] !== '' ? $post['delivery_date'] : null,
					'transporter_name' => isset($post['transporter_name']) && $post['transporter_name'] !== '' ? trim($post['transporter_name']) : null,
					'vehicle_number' => isset($post['vehicle_number']) && $post['vehicle_number'] !== '' ? trim($post['vehicle_number']) : null,
					'notes' => trim($post['notes'] ?? ''),
					'created_at' => $created_at,
				));

				if ($transferId) {
					foreach ($items as $it) {
						$itemModel->insert(array(
							'transfer_id' => $transferId,
							'qr_id' => $it['qr_id'] ?? null,
							'product_id' => (int) $it['product_id'],
							'quantity' => $it['quantity'],
							'source_stock_id' => $it['source_stock_id'] ?? null,
						));
					}
					return redirect()->to('stock-transfers/view/' . $transferId);
				}
				$error = 'Error creating stock transfer. Please try again.';
			}
		}

		$formdata = array(
			'mode' => 'new',
			'id' => 0,
			'error' => $error,
			'transfer_number' => isset($post['transfer_number']) && $post['transfer_number'] !== '' ? $post['transfer_number'] : $this->generateTransferNumber($transferModel),
			'from_location_type' => $post['from_location_type'] ?? 'godown',
			'from_location_id' => $post['from_location_id'] ?? '',
			'to_location_type' => $post['to_location_type'] ?? 'shop',
			'to_location_id' => $post['to_location_id'] ?? '',
			'request_id' => $post['request_id'] ?? '',
			'status' => $post['status'] ?? 'pending',
			'dispatch_date' => $post['dispatch_date'] ?? '',
			'delivery_date' => $post['delivery_date'] ?? '',
			'transporter_name' => $post['transporter_name'] ?? '',
			'vehicle_number' => $post['vehicle_number'] ?? '',
			'notes' => $post['notes'] ?? '',
			'items' => !empty($itemsForRepopulate) ? $itemsForRepopulate : array(),
		);

		$this->setData('formdata', $formdata);
		$this->setData('statusOptions', $this->getStatusOptions());
		$this->setData('godownOptions', $godownModel->getActiveGodowns());
		$this->setData('shopOptions', $shopModel->getActiveShops());
		$this->pageCss('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
		$this->pageJs('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js');
		$this->pageTitle('Add Stock Transfer');
		$this->pageJs('assets/js/custom/stock-transfers.js?v=s' . $this->AppConfig->jsVersion);
		return view('stock_transfers/details', $this->viewdata);
	}

	public function edit($id = null)
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$transferModel = new StockTransferModel();
		$itemModel = new StockTransferItemModel();
		$godownModel = new GodownModel();
		$shopModel = new ShopModel();

		$transfer = $transferModel->findByID($id);
		if (!$transfer) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$error = '';
		$post = array();
		$itemsForRepopulate = array();

		if ($this->isPost()) {
			$post = esc($this->getPost());
			$isvalidrequest = true;

			if (!isset($post['transfer_number']) || trim($post['transfer_number']) === '') {
				$error = "Transfer number is missing.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['from_location_type']) || !in_array($post['from_location_type'], array('godown', 'shop'), true))) {
				$error = "Please select from location type.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['from_location_id']) || (string) $post['from_location_id'] === '')) {
				$error = "Please select from location.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['to_location_type']) || !in_array($post['to_location_type'], array('godown', 'shop'), true))) {
				$error = "Please select to location type.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['to_location_id']) || (string) $post['to_location_id'] === '')) {
				$error = "Please select to location.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['status']) || trim((string) $post['status']) === '')) {
				$error = "Please select status.";
				$isvalidrequest = false;
			}

			$items = $this->collectItemsFromPost($post);
			if ($isvalidrequest && empty($items)) {
				$error = "Please add at least one item.";
				$isvalidrequest = false;
			}

			if ($isvalidrequest) {
				$dup = $transferModel->findByTransferNumber(trim($post['transfer_number']));
				if ($dup && (int) ($dup['transfer_id'] ?? 0) !== (int) $id) {
					$error = "Transfer number already exists.";
					$isvalidrequest = false;
				}
			}

			if ($isvalidrequest) {
				$totalItems = count($items);
				$result = $transferModel->update($id, array(
					'transfer_number' => trim($post['transfer_number']),
					'from_location_type' => $post['from_location_type'],
					'from_location_id' => (int) $post['from_location_id'],
					'to_location_type' => $post['to_location_type'],
					'to_location_id' => (int) $post['to_location_id'],
					'request_id' => isset($post['request_id']) && $post['request_id'] !== '' ? (int) $post['request_id'] : null,
					'total_items' => $totalItems,
					'status' => $post['status'],
					'dispatch_date' => isset($post['dispatch_date']) && $post['dispatch_date'] !== '' ? $post['dispatch_date'] : null,
					'delivery_date' => isset($post['delivery_date']) && $post['delivery_date'] !== '' ? $post['delivery_date'] : null,
					'transporter_name' => isset($post['transporter_name']) && $post['transporter_name'] !== '' ? trim($post['transporter_name']) : null,
					'vehicle_number' => isset($post['vehicle_number']) && $post['vehicle_number'] !== '' ? trim($post['vehicle_number']) : null,
					'notes' => trim($post['notes'] ?? ''),
				));

				if ($result) {
					$itemModel->where('transfer_id', (int) $id)->delete();
					foreach ($items as $it) {
						$itemModel->insert(array(
							'transfer_id' => (int) $id,
							'qr_id' => $it['qr_id'] ?? null,
							'product_id' => (int) $it['product_id'],
							'quantity' => $it['quantity'],
							'source_stock_id' => $it['source_stock_id'] ?? null,
						));
					}
					return redirect()->to('stock-transfers/view/' . $id);
				}
				$error = 'Error updating stock transfer. Please try again.';
			}
		}

		$existingItems = $itemModel->findByTransferID($id);
		$formdata = array(
			'mode' => 'edit',
			'id' => $transfer['transfer_id'],
			'error' => $error,
			'transfer_number' => isset($post['transfer_number']) ? $post['transfer_number'] : ($transfer['transfer_number'] ?? ''),
			'from_location_type' => isset($post['from_location_type']) ? $post['from_location_type'] : ($transfer['from_location_type'] ?? 'godown'),
			'from_location_id' => isset($post['from_location_id']) ? $post['from_location_id'] : ($transfer['from_location_id'] ?? ''),
			'to_location_type' => isset($post['to_location_type']) ? $post['to_location_type'] : ($transfer['to_location_type'] ?? 'shop'),
			'to_location_id' => isset($post['to_location_id']) ? $post['to_location_id'] : ($transfer['to_location_id'] ?? ''),
			'request_id' => isset($post['request_id']) ? $post['request_id'] : ($transfer['request_id'] ?? ''),
			'status' => isset($post['status']) ? $post['status'] : ($transfer['status'] ?? 'pending'),
			'dispatch_date' => isset($post['dispatch_date']) ? $post['dispatch_date'] : ($transfer['dispatch_date'] ?? ''),
			'delivery_date' => isset($post['delivery_date']) ? $post['delivery_date'] : ($transfer['delivery_date'] ?? ''),
			'transporter_name' => isset($post['transporter_name']) ? $post['transporter_name'] : ($transfer['transporter_name'] ?? ''),
			'vehicle_number' => isset($post['vehicle_number']) ? $post['vehicle_number'] : ($transfer['vehicle_number'] ?? ''),
			'notes' => isset($post['notes']) ? $post['notes'] : ($transfer['notes'] ?? ''),
			'items' => !empty($itemsForRepopulate) ? $itemsForRepopulate : $this->mapItemsForForm($existingItems),
		);

		$this->setData('formdata', $formdata);
		$this->setData('statusOptions', $this->getStatusOptions());
		$this->setData('godownOptions', $godownModel->getActiveGodowns());
		$this->setData('shopOptions', $shopModel->getActiveShops());
		$this->pageCss('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
		$this->pageJs('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js');
		$this->pageTitle('Edit Stock Transfer');
		$this->pageJs('assets/js/custom/stock-transfers.js?v=s' . $this->AppConfig->jsVersion);
		return view('stock_transfers/details', $this->viewdata);
	}

	public function view($id = null)
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$transferModel = new StockTransferModel();
		$itemModel = new StockTransferItemModel();

		$transfer = $transferModel->findByID($id);
		if (!$transfer) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$items = $itemModel->findByTransferID($id);

		$fromLocation = '—';
		if ($transfer['from_location_type'] === 'godown' && !empty($transfer['from_godown_name'])) {
			$fromLocation = $transfer['from_godown_name'];
		} elseif ($transfer['from_location_type'] === 'shop' && !empty($transfer['from_shop_name'])) {
			$fromLocation = $transfer['from_shop_name'];
		}

		$toLocation = '—';
		if ($transfer['to_location_type'] === 'godown' && !empty($transfer['to_godown_name'])) {
			$toLocation = $transfer['to_godown_name'];
		} elseif ($transfer['to_location_type'] === 'shop' && !empty($transfer['to_shop_name'])) {
			$toLocation = $transfer['to_shop_name'];
		}

		$this->setData('transfer', $transfer);
		$this->setData('items', $items);
		$this->setData('fromLocation', $fromLocation);
		$this->setData('toLocation', $toLocation);
		$this->setData('statusOptions', $this->getStatusOptions());
		$this->pageTitle('View Stock Transfer');
		return view('stock_transfers/view', $this->viewdata);
	}

	public function delete($id = null)
	{
		if (!$this->isUserLoggedIn()) {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
		}

		$transferModel = new StockTransferModel();
		$itemModel = new StockTransferItemModel();
		$transfer = $transferModel->findByID($id);
		if (!$transfer) {
			return $this->response->setJSON(['success' => false, 'message' => 'Stock transfer not found!']);
		}

		$itemModel->where('transfer_id', (int) $id)->delete();
		$transferModel->delete($id);
		return $this->response->setJSON(['success' => true, 'message' => 'Stock transfer deleted successfully!']);
	}

	private function collectItemsFromPost($post)
	{
		$items = array();
		$qrIds = $post['item_qr_id'] ?? array();
		$productIds = $post['item_product_id'] ?? array();
		$quantities = $post['item_quantity'] ?? array();
		$sourceStockIds = $post['item_source_stock_id'] ?? array();

		if (!is_array($qrIds) || !is_array($productIds) || !is_array($quantities)) {
			return $items;
		}

		$count = max(count($qrIds), count($productIds), count($quantities));
		for ($i = 0; $i < $count; $i++) {
			$qid = isset($qrIds[$i]) && $qrIds[$i] !== '' ? (int) $qrIds[$i] : null;
			$pid = isset($productIds[$i]) ? (int) $productIds[$i] : 0;
			$qty = isset($quantities[$i]) && $quantities[$i] !== '' ? (float) $quantities[$i] : 0;
			$srcStockId = isset($sourceStockIds[$i]) && $sourceStockIds[$i] !== '' ? (int) $sourceStockIds[$i] : null;

			if ($pid <= 0 || $qty <= 0) {
				continue;
			}

			$items[] = array(
				'qr_id' => $qid,
				'product_id' => $pid,
				'quantity' => $qty,
				'source_stock_id' => $srcStockId,
			);
		}

		return $items;
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
					'source_stock_id' => $it['source_stock_id'] ?? '',
				);
			}
		}
		return $out;
	}
}

