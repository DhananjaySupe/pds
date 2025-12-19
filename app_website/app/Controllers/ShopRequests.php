<?php namespace App\Controllers;

use App\Models\ShopRequestModel;
use App\Models\ShopRequestItemModel;
use App\Models\ProductModel;
use App\Models\GodownModel;
use App\Models\ShopModel;
use App\Libraries\ExcelExporter;

class ShopRequests extends BaseController
{
	private function generateRequestNumber(ShopRequestModel $requestModel)
	{
		$prefix = 'REQ';
		$padLen = 6;

		$last = $requestModel->select('request_id')->orderBy('request_id', 'DESC')->first();
		$nextId = (int) (($last['request_id'] ?? 0) + 1);
		if ($nextId < 1) {
			$nextId = 1;
		}

		for ($i = 0; $i < 50; $i++) {
			$req = $prefix . str_pad((string) $nextId, $padLen, '0', STR_PAD_LEFT);
			if (!$requestModel->findByRequestNumber($req)) {
				return $req;
			}
			$nextId++;
		}

		return $prefix . date('YmdHis');
	}

	private function getStatusOptions()
	{
		return array(
			'pending' => 'Pending',
			'approved' => 'Approved',
			'rejected' => 'Rejected',
			'fulfilled' => 'Fulfilled',
			'cancelled' => 'Cancelled',
		);
	}

	public function index()
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$requestModel = new ShopRequestModel();
		$recordsTotal = intval($this->getParam('recordstotal', 0));
		$recordsFiltered = intval($this->getParam('recordsfiltered', 0));
		$params = array();

		if ($recordsTotal === 0) {
			$params['count'] = true;
			$recordsTotal = $requestModel->search($params);
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
				$recordsFiltered = $requestModel->search($countParams);
				unset($countParams);
			} else {
				$recordsFiltered = $recordsTotal;
			}

			$params['limit'] = array('length' => $length, 'offset' => $start);

			if (!empty($sorting) && isset($sorting[0]['column'])) {
				$columnIndex = intval($sorting[0]['column']);
				$columnMap = array(
					0 => 'shop_requests.request_number',
					1 => 'shops.shop_name',
					2 => 'godowns.godown_name',
					3 => 'shop_requests.status',
					4 => 'shop_requests.request_date',
					5 => 'shop_requests.created_at',
				);
				if (isset($columnMap[$columnIndex])) {
					$params['sort'] = array(
						'column' => $columnMap[$columnIndex],
						'order' => isset($sorting[0]['dir']) && in_array(strtolower($sorting[0]['dir']), array('asc', 'desc')) ? $sorting[0]['dir'] : 'asc',
					);
				}
			}

			$requests = $requestModel->search($params);
			$data = array();
			if (!empty($requests)) {
				foreach ($requests as $r) {
					$requestId = $r['request_id'];
					$requestNumber = $r['request_number'] ?? '';
					$shopName = $r['shop_name'] ?? '—';
					$godownName = $r['godown_name'] ?? '—';
					$status = $r['status'] ?? '—';
					$requestDate = !empty($r['request_date']) ? date('d M Y', strtotime($r['request_date'])) : '—';
					$createdAt = !empty($r['created_at']) ? date('d M Y', strtotime($r['created_at'])) : '—';

					$data[] = array(
						'id' => $requestId,
						'request_number' => '<a href="' . site_url('shop-requests/view/' . $requestId) . '" class="fw-semibold text-reset">' . esc($requestNumber) . '</a>',
						'shop_name' => esc($shopName),
						'godown_name' => esc($godownName),
						'status' => '<span class="badge bg-info-subtle text-info text-uppercase">' . esc((string) $status) . '</span>',
						'request_date' => $requestDate,
						'created_at' => $createdAt,
						'actions' => '
							<div class="dropdown text-center">
								<button type="button" class="btn btn-sm btn-soft-info btn-icon fs-14" data-bs-toggle="dropdown" aria-expanded="false">
									<i class="ri-more-2-fill"></i>
								</button>
								<ul class="dropdown-menu dropdown-menu-end">
									<li><a class="dropdown-item" href="' . site_url('shop-requests/view/' . $requestId) . '"><i class="ri-eye-line align-middle me-1"></i> View</a></li>
									<li><a class="dropdown-item" href="' . site_url('shop-requests/edit/' . $requestId) . '"><i class="ri-edit-line align-middle me-1"></i> Edit</a></li>
									<li><a class="dropdown-item text-danger" data-action="delete" data-id="' . $requestId . '" data-name="' . esc($requestNumber) . '" href="javascript:void(0);"><i class="ri-delete-bin-line align-middle me-1"></i> Delete</a></li>
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
		$this->pageTitle('Shop Requests');
		$this->pageJs('assets/js/custom/shop-requests.js?v=s' . $this->AppConfig->jsVersion);
		return view('shop_requests/index', $this->viewdata);
	}

	public function export()
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$requestModel = new ShopRequestModel();
		$params = array();
		$filter_keywords = trim($this->getParam('keywords', ''));
		$filter_status = trim($this->getParam('status', ''));

		if ($filter_keywords !== '') {
			$params['keywords'] = $filter_keywords;
		}
		if ($filter_status !== '') {
			$params['status'] = $filter_status;
		}

		$requests = $requestModel->search($params);
		$exporter = new ExcelExporter();
		$sheet = $exporter->spreadsheet->getActiveSheet();

		$headers = array('Request Number', 'Shop', 'Godown', 'Status', 'Request Date', 'Required Date', 'Total Items', 'Created At');
		$rows = array();
		if (!empty($requests)) {
			foreach ($requests as $r) {
				$rows[] = array(
					$r['request_number'] ?? '',
					$r['shop_name'] ?? '',
					$r['godown_name'] ?? '',
					$r['status'] ?? '',
					$r['request_date'] ?? '',
					$r['required_date'] ?? '',
					$r['total_items'] ?? 0,
					$r['created_at'] ?? '',
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

		$filename = 'shop_requests_' . date('Ymd_His') . '.xlsx';
		$exporter->download($filename);
	}

	public function new()
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$requestModel = new ShopRequestModel();
		$itemModel = new ShopRequestItemModel();
		$godownModel = new GodownModel();
		$shopModel = new ShopModel();

		$error = '';
		$post = array();
		$itemsForRepopulate = array();

		if ($this->isPost()) {
			$post = esc($this->getPost());
			$isvalidrequest = true;

			if (!isset($post['request_number']) || trim($post['request_number']) === '') {
				$post['request_number'] = $this->generateRequestNumber($requestModel);
			}

			if ($isvalidrequest && (!isset($post['shop_id']) || (string) $post['shop_id'] === '')) {
				$error = "Please select shop.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['godown_id']) || (string) $post['godown_id'] === '')) {
				$error = "Please select godown.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['status']) || trim((string) $post['status']) === '')) {
				$error = "Please select status.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['request_date']) || trim((string) $post['request_date']) === '')) {
				$error = "Please select request date.";
				$isvalidrequest = false;
			}

			$items = $this->collectItemsFromPost($post);
			if ($isvalidrequest && empty($items)) {
				$error = "Please add at least one item.";
				$isvalidrequest = false;
			}

			if ($isvalidrequest) {
				$dup = $requestModel->findByRequestNumber(trim($post['request_number']));
				if ($dup) {
					$post['request_number'] = $this->generateRequestNumber($requestModel);
				}
			}

			if ($isvalidrequest) {
				$created_at = date('Y-m-d H:i:s');
				$totalItems = count($items);

				$requestId = $requestModel->insert(array(
					'request_number' => trim($post['request_number']),
					'shop_id' => (int) $post['shop_id'],
					'godown_id' => (int) $post['godown_id'],
					'total_items' => $totalItems,
					'status' => $post['status'],
					'request_date' => $post['request_date'],
					'required_date' => isset($post['required_date']) && $post['required_date'] !== '' ? $post['required_date'] : null,
					'notes' => trim($post['notes'] ?? ''),
					'created_at' => $created_at,
				));

				if ($requestId) {
					foreach ($items as $it) {
						$itemModel->insert(array(
							'request_id' => $requestId,
							'product_id' => (int) $it['product_id'],
							'quantity' => $it['quantity'],
							'fulfilled_quantity' => 0,
							'priority' => isset($it['priority']) ? (int) $it['priority'] : 0,
						));
					}
					return redirect()->to('shop-requests/view/' . $requestId);
				}
				$error = 'Error creating shop request. Please try again.';
			}
		}

		$formdata = array(
			'mode' => 'new',
			'id' => 0,
			'error' => $error,
			'request_number' => isset($post['request_number']) && $post['request_number'] !== '' ? $post['request_number'] : $this->generateRequestNumber($requestModel),
			'shop_id' => $post['shop_id'] ?? '',
			'godown_id' => $post['godown_id'] ?? '',
			'status' => $post['status'] ?? 'pending',
			'request_date' => $post['request_date'] ?? date('Y-m-d'),
			'required_date' => $post['required_date'] ?? '',
			'notes' => $post['notes'] ?? '',
			'items' => !empty($itemsForRepopulate) ? $itemsForRepopulate : array(),
		);

		$this->setData('formdata', $formdata);
		$this->setData('statusOptions', $this->getStatusOptions());
		$this->setData('godownOptions', $godownModel->getActiveGodowns());
		$this->setData('shopOptions', $shopModel->getActiveShops());
		$this->pageCss('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
		$this->pageJs('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js');
		$this->pageTitle('Add Shop Request');
		$this->pageJs('assets/js/custom/shop-requests.js?v=s' . $this->AppConfig->jsVersion);
		return view('shop_requests/details', $this->viewdata);
	}

	public function edit($id = null)
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$requestModel = new ShopRequestModel();
		$itemModel = new ShopRequestItemModel();
		$godownModel = new GodownModel();
		$shopModel = new ShopModel();

		$request = $requestModel->findByID($id);
		if (!$request) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$error = '';
		$post = array();
		$itemsForRepopulate = array();

		if ($this->isPost()) {
			$post = esc($this->getPost());
			$isvalidrequest = true;

			if (!isset($post['request_number']) || trim($post['request_number']) === '') {
				$error = "Request number is missing.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['shop_id']) || (string) $post['shop_id'] === '')) {
				$error = "Please select shop.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['godown_id']) || (string) $post['godown_id'] === '')) {
				$error = "Please select godown.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['status']) || trim((string) $post['status']) === '')) {
				$error = "Please select status.";
				$isvalidrequest = false;
			}
			if ($isvalidrequest && (!isset($post['request_date']) || trim((string) $post['request_date']) === '')) {
				$error = "Please select request date.";
				$isvalidrequest = false;
			}

			$items = $this->collectItemsFromPost($post);
			if ($isvalidrequest && empty($items)) {
				$error = "Please add at least one item.";
				$isvalidrequest = false;
			}

			if ($isvalidrequest) {
				$dup = $requestModel->findByRequestNumber(trim($post['request_number']));
				if ($dup && (int) ($dup['request_id'] ?? 0) !== (int) $id) {
					$error = "Request number already exists.";
					$isvalidrequest = false;
				}
			}

			if ($isvalidrequest) {
				$totalItems = count($items);
				$result = $requestModel->update($id, array(
					'request_number' => trim($post['request_number']),
					'shop_id' => (int) $post['shop_id'],
					'godown_id' => (int) $post['godown_id'],
					'total_items' => $totalItems,
					'status' => $post['status'],
					'request_date' => $post['request_date'],
					'required_date' => isset($post['required_date']) && $post['required_date'] !== '' ? $post['required_date'] : null,
					'notes' => trim($post['notes'] ?? ''),
				));

				if ($result) {
					// Get existing items to preserve fulfilled_quantity
					$existingItems = $itemModel->findByRequestID($id);
					$fulfilledMap = array();
					foreach ($existingItems as $ei) {
						$fulfilledMap[(int) $ei['product_id']] = (float) ($ei['fulfilled_quantity'] ?? 0);
					}

					$itemModel->where('request_id', (int) $id)->delete();
					foreach ($items as $it) {
						$pid = (int) $it['product_id'];
						$itemModel->insert(array(
							'request_id' => (int) $id,
							'product_id' => $pid,
							'quantity' => $it['quantity'],
							'fulfilled_quantity' => isset($fulfilledMap[$pid]) ? $fulfilledMap[$pid] : 0,
							'priority' => isset($it['priority']) ? (int) $it['priority'] : 0,
						));
					}
					return redirect()->to('shop-requests/view/' . $id);
				}
				$error = 'Error updating shop request. Please try again.';
			}
		}

		$existingItems = $itemModel->findByRequestID($id);
		$formdata = array(
			'mode' => 'edit',
			'id' => $request['request_id'],
			'error' => $error,
			'request_number' => isset($post['request_number']) ? $post['request_number'] : ($request['request_number'] ?? ''),
			'shop_id' => isset($post['shop_id']) ? $post['shop_id'] : ($request['shop_id'] ?? ''),
			'godown_id' => isset($post['godown_id']) ? $post['godown_id'] : ($request['godown_id'] ?? ''),
			'status' => isset($post['status']) ? $post['status'] : ($request['status'] ?? 'pending'),
			'request_date' => isset($post['request_date']) ? $post['request_date'] : ($request['request_date'] ?? date('Y-m-d')),
			'required_date' => isset($post['required_date']) ? $post['required_date'] : ($request['required_date'] ?? ''),
			'notes' => isset($post['notes']) ? $post['notes'] : ($request['notes'] ?? ''),
			'items' => !empty($itemsForRepopulate) ? $itemsForRepopulate : $this->mapItemsForForm($existingItems),
		);

		$this->setData('formdata', $formdata);
		$this->setData('statusOptions', $this->getStatusOptions());
		$this->setData('godownOptions', $godownModel->getActiveGodowns());
		$this->setData('shopOptions', $shopModel->getActiveShops());
		$this->pageCss('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
		$this->pageJs('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js');
		$this->pageTitle('Edit Shop Request');
		$this->pageJs('assets/js/custom/shop-requests.js?v=s' . $this->AppConfig->jsVersion);
		return view('shop_requests/details', $this->viewdata);
	}

	public function view($id = null)
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$requestModel = new ShopRequestModel();
		$itemModel = new ShopRequestItemModel();

		$request = $requestModel->findByID($id);
		if (!$request) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$items = $itemModel->findByRequestID($id);

		$this->setData('request', $request);
		$this->setData('items', $items);
		$this->setData('statusOptions', $this->getStatusOptions());
		$this->pageTitle('View Shop Request');
		return view('shop_requests/view', $this->viewdata);
	}

	public function delete($id = null)
	{
		if (!$this->isUserLoggedIn()) {
			return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
		}

		$requestModel = new ShopRequestModel();
		$itemModel = new ShopRequestItemModel();
		$request = $requestModel->findByID($id);
		if (!$request) {
			return $this->response->setJSON(['success' => false, 'message' => 'Shop request not found!']);
		}

		$itemModel->where('request_id', (int) $id)->delete();
		$requestModel->delete($id);
		return $this->response->setJSON(['success' => true, 'message' => 'Shop request deleted successfully!']);
	}

	private function collectItemsFromPost($post)
	{
		$items = array();
		$productIds = $post['item_product_id'] ?? array();
		$quantities = $post['item_quantity'] ?? array();
		$priorities = $post['item_priority'] ?? array();

		if (!is_array($productIds) || !is_array($quantities)) {
			return $items;
		}

		$count = max(count($productIds), count($quantities));
		for ($i = 0; $i < $count; $i++) {
			$pid = isset($productIds[$i]) ? (int) $productIds[$i] : 0;
			$qty = isset($quantities[$i]) && $quantities[$i] !== '' ? (float) $quantities[$i] : 0;
			$priority = isset($priorities[$i]) && $priorities[$i] !== '' ? (int) $priorities[$i] : 0;

			if ($pid <= 0 || $qty <= 0) {
				continue;
			}

			$items[] = array(
				'product_id' => $pid,
				'quantity' => $qty,
				'priority' => $priority,
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
				$out[] = array(
					'product_id' => $it['product_id'] ?? '',
					'product_text' => $productText,
					'quantity' => $it['quantity'] ?? '',
					'fulfilled_quantity' => $it['fulfilled_quantity'] ?? '',
					'priority' => $it['priority'] ?? '',
				);
			}
		}
		return $out;
	}
}

