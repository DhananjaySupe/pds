<?php namespace App\Controllers;

use App\Models\VendorModel;
use App\Models\GodownModel;
use App\Models\ShopModel;
use App\Models\PurchaseOrderModel;
use App\Models\PurchaseOrderItemModel;
use App\Models\ReceiptModel;
use App\Models\StockInventoryModel;
use App\Models\QrCodeModel;
use App\Models\SaleModel;
use App\Models\SaleItemModel;
use App\Models\StockTransferModel;
use App\Models\StockTransferItemModel;
use App\Models\ShopRequestModel;
use App\Models\ShopRequestItemModel;
use App\Models\ProductModel;
use App\Libraries\ExcelExporter;
use App\Libraries\PDF;

class Reports extends BaseController
{
	public function index()
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$vendorModel = new VendorModel();
		$godownModel = new GodownModel();
		$shopModel = new ShopModel();

		$reportType = trim($this->getParam('type', ''));
		$entityType = trim($this->getParam('entity_type', ''));
		$entityId = (int) $this->getParam('entity_id', 0);
		$startDate = trim($this->getParam('start_date', ''));
		$endDate = trim($this->getParam('end_date', ''));

		$this->setData('reportType', $reportType);
		$this->setData('entityType', $entityType);
		$this->setData('entityId', $entityId);
		$this->setData('startDate', $startDate);
		$this->setData('endDate', $endDate);
		$this->setData('vendorOptions', $vendorModel->search(array()));
		$this->setData('godownOptions', $godownModel->getActiveGodowns());
		$this->setData('shopOptions', $shopModel->getActiveShops());
		$this->pageTitle('Reports');
		$this->pageJs('assets/js/custom/reports.js?v=s' . $this->AppConfig->jsVersion);
		return view('reports/index', $this->viewdata);
	}

	public function vendor()
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$vendorId = (int) $this->getParam('vendor_id', 0);
		$startDate = trim($this->getParam('start_date', ''));
		$endDate = trim($this->getParam('end_date', ''));

		if ($vendorId <= 0) {
			// Show filter form
			$vendorModel = new VendorModel();
			$this->setData('vendorOptions', $vendorModel->search(array()));
			$this->setData('startDate', $startDate);
			$this->setData('endDate', $endDate);
			$this->pageTitle('Vendor Reports');
			return view('reports/vendor_filter', $this->viewdata);
		}

		$vendorModel = new VendorModel();
		$vendor = $vendorModel->findByID($vendorId);
		if (!$vendor) {
			return redirect()->to('reports')->with('error', 'Vendor not found');
		}

		$purchaseOrderModel = new PurchaseOrderModel();
		$receiptModel = new ReceiptModel();
		$qrCodeModel = new QrCodeModel();
		$stockInventoryModel = new StockInventoryModel();

		// Purchase Orders
		$poParams = array('vendor_id' => $vendorId);
		if ($startDate !== '') $poParams['order_date_from'] = $startDate;
		if ($endDate !== '') $poParams['order_date_to'] = $endDate;
		$purchaseOrders = $purchaseOrderModel->search($poParams);

		// Stock Receipts
		$receiptParams = array('vendor_id' => $vendorId);
		if ($startDate !== '') $receiptParams['receipt_date_from'] = $startDate;
		if ($endDate !== '') $receiptParams['receipt_date_to'] = $endDate;
		$receipts = $receiptModel->search($receiptParams);

		// QR Codes
		$qrParams = array('vendor_id' => $vendorId);
		$qrCodes = $qrCodeModel->search($qrParams);

		// Stock Inventory
		$inventoryParams = array();
		$stockInventory = array();
		if (!empty($qrCodes)) {
			$qrIds = array_column($qrCodes, 'qr_id');
			foreach ($qrIds as $qrId) {
				$inv = $stockInventoryModel->findByQRID($qrId);
				$stockInventory = array_merge($stockInventory, $inv);
			}
		}

		// Calculate totals
		$totalPurchaseOrders = count($purchaseOrders);
		$totalPurchaseAmount = 0;
		foreach ($purchaseOrders as $po) {
			$totalPurchaseAmount += (float) ($po['final_amount'] ?? 0);
		}

		$totalReceipts = count($receipts);
		$totalQRCodes = count($qrCodes);
		$totalInventoryItems = count($stockInventory);
		$totalInventoryValue = 0;
		$qrIds = array();
		foreach ($stockInventory as $inv) {
			if (isset($inv['qr_id'])) {
				$qrIds[] = (int) $inv['qr_id'];
			}
		}
		if (!empty($qrIds)) {
			$qrModel = new QrCodeModel();
			$qrCodesForPrice = $qrModel->whereIn('qr_id', array_unique($qrIds))->findAll();
			$qrPriceMap = array();
			foreach ($qrCodesForPrice as $qr) {
				$qrPriceMap[(int) $qr['qr_id']] = (float) ($qr['purchase_price'] ?? 0);
			}
			foreach ($stockInventory as $inv) {
				$qty = (float) ($inv['quantity'] ?? 0);
				$qrId = (int) ($inv['qr_id'] ?? 0);
				$price = isset($qrPriceMap[$qrId]) ? $qrPriceMap[$qrId] : 0;
				$totalInventoryValue += $qty * $price;
			}
		}

		$this->setData('vendor', $vendor);
		$this->setData('purchaseOrders', $purchaseOrders);
		$this->setData('receipts', $receipts);
		$this->setData('qrCodes', $qrCodes);
		$this->setData('stockInventory', $stockInventory);
		$this->setData('startDate', $startDate);
		$this->setData('endDate', $endDate);
		$this->setData('summary', array(
			'total_purchase_orders' => $totalPurchaseOrders,
			'total_purchase_amount' => $totalPurchaseAmount,
			'total_receipts' => $totalReceipts,
			'total_qr_codes' => $totalQRCodes,
			'total_inventory_items' => $totalInventoryItems,
			'total_inventory_value' => $totalInventoryValue,
		));
		$this->pageTitle('Vendor Report - ' . ($vendor['company_name'] ?? ''));
		return view('reports/vendor', $this->viewdata);
	}

	public function godown()
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$godownId = (int) $this->getParam('godown_id', 0);
		$startDate = trim($this->getParam('start_date', ''));
		$endDate = trim($this->getParam('end_date', ''));

		if ($godownId <= 0) {
			// Show filter form
			$godownModel = new GodownModel();
			$this->setData('godownOptions', $godownModel->getActiveGodowns());
			$this->setData('startDate', $startDate);
			$this->setData('endDate', $endDate);
			$this->pageTitle('Godown Reports');
			return view('reports/godown_filter', $this->viewdata);
		}

		$godownModel = new GodownModel();
		$godown = $godownModel->findByID($godownId);
		if (!$godown) {
			return redirect()->to('reports')->with('error', 'Godown not found');
		}

		$purchaseOrderModel = new PurchaseOrderModel();
		$receiptModel = new ReceiptModel();
		$stockInventoryModel = new StockInventoryModel();
		$stockTransferModel = new StockTransferModel();
		$shopRequestModel = new ShopRequestModel();

		// Purchase Orders
		$poParams = array('godown_id' => $godownId);
		if ($startDate !== '') $poParams['order_date_from'] = $startDate;
		if ($endDate !== '') $poParams['order_date_to'] = $endDate;
		$purchaseOrders = $purchaseOrderModel->search($poParams);

		// Stock Receipts
		$receiptParams = array('godown_id' => $godownId);
		if ($startDate !== '') $receiptParams['receipt_date_from'] = $startDate;
		if ($endDate !== '') $receiptParams['receipt_date_to'] = $endDate;
		$receipts = $receiptModel->search($receiptParams);

		// Stock Inventory
		$inventoryParams = array(
			'location_type' => 'godown',
			'location_id' => $godownId,
			'is_available' => 1
		);
		$stockInventory = $stockInventoryModel->search($inventoryParams);

		// Stock Transfers (from this godown)
		$transferFromParams = array(
			'from_location_type' => 'godown',
			'from_location_id' => $godownId
		);
		if ($startDate !== '') $transferFromParams['dispatch_date_from'] = $startDate;
		if ($endDate !== '') $transferFromParams['dispatch_date_to'] = $endDate;
		$transfersFrom = $stockTransferModel->search($transferFromParams);

		// Stock Transfers (to this godown)
		$transferToParams = array(
			'to_location_type' => 'godown',
			'to_location_id' => $godownId
		);
		if ($startDate !== '') $transferToParams['dispatch_date_from'] = $startDate;
		if ($endDate !== '') $transferToParams['dispatch_date_to'] = $endDate;
		$transfersTo = $stockTransferModel->search($transferToParams);

		// Shop Requests
		$requestParams = array('godown_id' => $godownId);
		if ($startDate !== '') $requestParams['request_date_from'] = $startDate;
		if ($endDate !== '') $requestParams['request_date_to'] = $endDate;
		$shopRequests = $shopRequestModel->search($requestParams);

		// Calculate totals
		$totalPurchaseOrders = count($purchaseOrders);
		$totalPurchaseAmount = 0;
		foreach ($purchaseOrders as $po) {
			$totalPurchaseAmount += (float) ($po['final_amount'] ?? 0);
		}

		$totalReceipts = count($receipts);
		$totalInventoryItems = count($stockInventory);
		$totalInventoryValue = 0;
		$totalInventoryQuantity = 0;
		$qrIds = array();
		foreach ($stockInventory as $inv) {
			if (isset($inv['qr_id'])) {
				$qrIds[] = (int) $inv['qr_id'];
			}
		}
		$qrPriceMap = array();
		if (!empty($qrIds)) {
			$qrModel = new QrCodeModel();
			$qrCodes = $qrModel->whereIn('qr_id', array_unique($qrIds))->findAll();
			foreach ($qrCodes as $qr) {
				$qrPriceMap[(int) $qr['qr_id']] = (float) ($qr['purchase_price'] ?? 0);
			}
		}
		foreach ($stockInventory as $inv) {
			$qty = (float) ($inv['quantity'] ?? 0);
			$qrId = (int) ($inv['qr_id'] ?? 0);
			$price = isset($qrPriceMap[$qrId]) ? $qrPriceMap[$qrId] : 0;
			$totalInventoryQuantity += $qty;
			$totalInventoryValue += $qty * $price;
		}

		$totalTransfersFrom = count($transfersFrom);
		$totalTransfersTo = count($transfersTo);
		$totalShopRequests = count($shopRequests);

		$this->setData('godown', $godown);
		$this->setData('purchaseOrders', $purchaseOrders);
		$this->setData('receipts', $receipts);
		$this->setData('stockInventory', $stockInventory);
		$this->setData('transfersFrom', $transfersFrom);
		$this->setData('transfersTo', $transfersTo);
		$this->setData('shopRequests', $shopRequests);
		$this->setData('startDate', $startDate);
		$this->setData('endDate', $endDate);
		$this->setData('summary', array(
			'total_purchase_orders' => $totalPurchaseOrders,
			'total_purchase_amount' => $totalPurchaseAmount,
			'total_receipts' => $totalReceipts,
			'total_inventory_items' => $totalInventoryItems,
			'total_inventory_quantity' => $totalInventoryQuantity,
			'total_inventory_value' => $totalInventoryValue,
			'total_transfers_from' => $totalTransfersFrom,
			'total_transfers_to' => $totalTransfersTo,
			'total_shop_requests' => $totalShopRequests,
		));
		$this->pageTitle('Godown Report - ' . ($godown['godown_name'] ?? ''));
		return view('reports/godown', $this->viewdata);
	}

	public function shop()
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$shopId = (int) $this->getParam('shop_id', 0);
		$startDate = trim($this->getParam('start_date', ''));
		$endDate = trim($this->getParam('end_date', ''));

		if ($shopId <= 0) {
			// Show filter form
			$shopModel = new ShopModel();
			$this->setData('shopOptions', $shopModel->getActiveShops());
			$this->setData('startDate', $startDate);
			$this->setData('endDate', $endDate);
			$this->pageTitle('Shop Reports');
			return view('reports/shop_filter', $this->viewdata);
		}

		$shopModel = new ShopModel();
		$shop = $shopModel->findByID($shopId);
		if (!$shop) {
			return redirect()->to('reports')->with('error', 'Shop not found');
		}

		$saleModel = new SaleModel();
		$stockInventoryModel = new StockInventoryModel();
		$stockTransferModel = new StockTransferModel();
		$shopRequestModel = new ShopRequestModel();

		// Sales
		$saleParams = array(
			'location_type' => 'shop',
			'location_id' => $shopId
		);
		if ($startDate !== '') $saleParams['sale_date_from'] = $startDate;
		if ($endDate !== '') $saleParams['sale_date_to'] = $endDate;
		$sales = $saleModel->search($saleParams);

		// Stock Inventory
		$inventoryParams = array(
			'location_type' => 'shop',
			'location_id' => $shopId,
			'is_available' => 1
		);
		$stockInventory = $stockInventoryModel->search($inventoryParams);

		// Stock Transfers (from this shop)
		$transferFromParams = array(
			'from_location_type' => 'shop',
			'from_location_id' => $shopId
		);
		if ($startDate !== '') $transferFromParams['dispatch_date_from'] = $startDate;
		if ($endDate !== '') $transferFromParams['dispatch_date_to'] = $endDate;
		$transfersFrom = $stockTransferModel->search($transferFromParams);

		// Stock Transfers (to this shop)
		$transferToParams = array(
			'to_location_type' => 'shop',
			'to_location_id' => $shopId
		);
		if ($startDate !== '') $transferToParams['dispatch_date_from'] = $startDate;
		if ($endDate !== '') $transferToParams['dispatch_date_to'] = $endDate;
		$transfersTo = $stockTransferModel->search($transferToParams);

		// Shop Requests
		$requestParams = array('shop_id' => $shopId);
		if ($startDate !== '') $requestParams['request_date_from'] = $startDate;
		if ($endDate !== '') $requestParams['request_date_to'] = $endDate;
		$shopRequests = $shopRequestModel->search($requestParams);

		// Calculate totals
		$totalSales = count($sales);
		$totalSalesAmount = 0;
		foreach ($sales as $sale) {
			$totalSalesAmount += (float) ($sale['final_amount'] ?? 0);
		}

		$totalInventoryItems = count($stockInventory);
		$totalInventoryValue = 0;
		$totalInventoryQuantity = 0;
		$qrIds = array();
		foreach ($stockInventory as $inv) {
			if (isset($inv['qr_id'])) {
				$qrIds[] = (int) $inv['qr_id'];
			}
		}
		$qrPriceMap = array();
		if (!empty($qrIds)) {
			$qrModel = new QrCodeModel();
			$qrCodes = $qrModel->whereIn('qr_id', array_unique($qrIds))->findAll();
			foreach ($qrCodes as $qr) {
				$qrPriceMap[(int) $qr['qr_id']] = (float) ($qr['purchase_price'] ?? 0);
			}
		}
		foreach ($stockInventory as $inv) {
			$qty = (float) ($inv['quantity'] ?? 0);
			$qrId = (int) ($inv['qr_id'] ?? 0);
			$price = isset($qrPriceMap[$qrId]) ? $qrPriceMap[$qrId] : 0;
			$totalInventoryQuantity += $qty;
			$totalInventoryValue += $qty * $price;
		}

		$totalTransfersFrom = count($transfersFrom);
		$totalTransfersTo = count($transfersTo);
		$totalShopRequests = count($shopRequests);

		$this->setData('shop', $shop);
		$this->setData('sales', $sales);
		$this->setData('stockInventory', $stockInventory);
		$this->setData('transfersFrom', $transfersFrom);
		$this->setData('transfersTo', $transfersTo);
		$this->setData('shopRequests', $shopRequests);
		$this->setData('startDate', $startDate);
		$this->setData('endDate', $endDate);
		$this->setData('summary', array(
			'total_sales' => $totalSales,
			'total_sales_amount' => $totalSalesAmount,
			'total_inventory_items' => $totalInventoryItems,
			'total_inventory_quantity' => $totalInventoryQuantity,
			'total_inventory_value' => $totalInventoryValue,
			'total_transfers_from' => $totalTransfersFrom,
			'total_transfers_to' => $totalTransfersTo,
			'total_shop_requests' => $totalShopRequests,
		));
		$this->pageTitle('Shop Report - ' . ($shop['shop_name'] ?? ''));
		return view('reports/shop', $this->viewdata);
	}

	public function export()
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$reportType = trim($this->getParam('type', ''));
		$entityType = trim($this->getParam('entity_type', ''));
		$entityId = (int) $this->getParam('entity_id', 0);
		$startDate = trim($this->getParam('start_date', ''));
		$endDate = trim($this->getParam('end_date', ''));

		if ($reportType === 'vendor' && $entityType === 'vendor' && $entityId > 0) {
			return $this->exportVendor($entityId, $startDate, $endDate);
		} elseif ($reportType === 'godown' && $entityType === 'godown' && $entityId > 0) {
			return $this->exportGodown($entityId, $startDate, $endDate);
		} elseif ($reportType === 'shop' && $entityType === 'shop' && $entityId > 0) {
			return $this->exportShop($entityId, $startDate, $endDate);
		}

		return redirect()->to('reports')->with('error', 'Invalid export parameters');
	}

	public function pdfExport()
	{
		if (!$this->isUserLoggedIn()) {
			return redirect()->route('login');
		}

		$reportType = trim($this->getParam('type', ''));
		$entityType = trim($this->getParam('entity_type', ''));
		$entityId = (int) $this->getParam('entity_id', 0);
		$startDate = trim($this->getParam('start_date', ''));
		$endDate = trim($this->getParam('end_date', ''));

		if ($reportType === 'vendor' && $entityType === 'vendor' && $entityId > 0) {
			return $this->exportVendorPdf($entityId, $startDate, $endDate);
		} elseif ($reportType === 'godown' && $entityType === 'godown' && $entityId > 0) {
			return $this->exportGodownPdf($entityId, $startDate, $endDate);
		} elseif ($reportType === 'shop' && $entityType === 'shop' && $entityId > 0) {
			return $this->exportShopPdf($entityId, $startDate, $endDate);
		}

		return redirect()->to('reports')->with('error', 'Invalid export parameters');
	}

	private function exportVendor($vendorId, $startDate, $endDate)
	{
		$vendorModel = new VendorModel();
		$vendor = $vendorModel->findByID($vendorId);
		if (!$vendor) {
			return redirect()->to('reports')->with('error', 'Vendor not found');
		}

		$purchaseOrderModel = new PurchaseOrderModel();
		$poParams = array('vendor_id' => $vendorId);
		if ($startDate !== '') $poParams['order_date_from'] = $startDate;
		if ($endDate !== '') $poParams['order_date_to'] = $endDate;
		$purchaseOrders = $purchaseOrderModel->search($poParams);

		$exporter = new ExcelExporter();
		$sheet = $exporter->spreadsheet->getActiveSheet();
		$sheet->setTitle('Vendor Report');

		$headers = array('PO Number', 'Godown', 'Order Date', 'Status', 'Total Amount', 'Final Amount');
		$rows = array();
		foreach ($purchaseOrders as $po) {
			$rows[] = array(
				$po['po_number'] ?? '',
				$po['godown_name'] ?? '',
				$po['order_date'] ?? '',
				$po['status'] ?? '',
				$po['total_amount'] ?? 0,
				$po['final_amount'] ?? 0,
			);
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

		$filename = 'vendor_report_' . ($vendor['company_name'] ?? $vendorId) . '_' . date('Ymd_His') . '.xlsx';
		$exporter->download($filename);
	}

	private function exportGodown($godownId, $startDate, $endDate)
	{
		$godownModel = new GodownModel();
		$godown = $godownModel->findByID($godownId);
		if (!$godown) {
			return redirect()->to('reports')->with('error', 'Godown not found');
		}

		$stockInventoryModel = new StockInventoryModel();
		$inventoryParams = array(
			'location_type' => 'godown',
			'location_id' => $godownId,
			'is_available' => 1
		);
		$stockInventory = $stockInventoryModel->search($inventoryParams);

		$exporter = new ExcelExporter();
		$sheet = $exporter->spreadsheet->getActiveSheet();
		$sheet->setTitle('Godown Inventory');

		$qrIds = array();
		foreach ($stockInventory as $inv) {
			if (isset($inv['qr_id'])) {
				$qrIds[] = (int) $inv['qr_id'];
			}
		}
		$qrDataMap = array();
		if (!empty($qrIds)) {
			$qrModel = new QrCodeModel();
			$qrCodes = $qrModel->whereIn('qr_id', array_unique($qrIds))->findAll();
			foreach ($qrCodes as $qr) {
				$qrDataMap[(int) $qr['qr_id']] = array(
					'purchase_price' => (float) ($qr['purchase_price'] ?? 0),
					'mrp' => (float) ($qr['mrp'] ?? 0),
					'batch_number' => $qr['batch_number'] ?? '',
				);
			}
		}

		$headers = array('QR Code', 'Product', 'Product Code', 'Batch Number', 'Quantity', 'Purchase Price', 'MRP', 'Stock Date');
		$rows = array();
		foreach ($stockInventory as $inv) {
			$qrId = (int) ($inv['qr_id'] ?? 0);
			$qrData = isset($qrDataMap[$qrId]) ? $qrDataMap[$qrId] : array('purchase_price' => 0, 'mrp' => 0, 'batch_number' => '');
			$rows[] = array(
				$inv['qr_code'] ?? '',
				$inv['product_name'] ?? '',
				$inv['product_code'] ?? '',
				$qrData['batch_number'],
				$inv['quantity'] ?? 0,
				$qrData['purchase_price'],
				$qrData['mrp'],
				$inv['stock_date'] ?? '',
			);
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

		$filename = 'godown_report_' . ($godown['godown_name'] ?? $godownId) . '_' . date('Ymd_His') . '.xlsx';
		$exporter->download($filename);
	}

	private function exportShop($shopId, $startDate, $endDate)
	{
		$shopModel = new ShopModel();
		$shop = $shopModel->findByID($shopId);
		if (!$shop) {
			return redirect()->to('reports')->with('error', 'Shop not found');
		}

		$saleModel = new SaleModel();
		$saleParams = array(
			'location_type' => 'shop',
			'location_id' => $shopId
		);
		if ($startDate !== '') $saleParams['sale_date_from'] = $startDate;
		if ($endDate !== '') $saleParams['sale_date_to'] = $endDate;
		$sales = $saleModel->search($saleParams);

		$exporter = new ExcelExporter();
		$sheet = $exporter->spreadsheet->getActiveSheet();
		$sheet->setTitle('Shop Sales');

		$headers = array('Invoice Number', 'Customer', 'Sale Date', 'Payment Status', 'Total Amount', 'Final Amount');
		$rows = array();
		foreach ($sales as $sale) {
			$rows[] = array(
				$sale['invoice_number'] ?? '',
				$sale['customer_name'] ?? '',
				$sale['sale_date'] ?? '',
				$sale['payment_status'] ?? '',
				$sale['total_amount'] ?? 0,
				$sale['final_amount'] ?? 0,
			);
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

		$filename = 'shop_report_' . ($shop['shop_name'] ?? $shopId) . '_' . date('Ymd_His') . '.xlsx';
		$exporter->download($filename);
	}

	private function exportVendorPdf($vendorId, $startDate, $endDate)
	{
		$vendorModel = new VendorModel();
		$vendor = $vendorModel->findByID($vendorId);
		if (!$vendor) {
			return redirect()->to('reports/vendor')->with('error', 'Vendor not found');
		}

		$purchaseOrderModel = new PurchaseOrderModel();
		$receiptModel = new ReceiptModel();
		$qrCodeModel = new QrCodeModel();
		$stockInventoryModel = new StockInventoryModel();

		// Purchase Orders
		$poParams = array('vendor_id' => $vendorId);
		if ($startDate !== '') $poParams['order_date_from'] = $startDate;
		if ($endDate !== '') $poParams['order_date_to'] = $endDate;
		$purchaseOrders = $purchaseOrderModel->search($poParams);

		// Stock Receipts
		$receiptParams = array('vendor_id' => $vendorId);
		if ($startDate !== '') $receiptParams['receipt_date_from'] = $startDate;
		if ($endDate !== '') $receiptParams['receipt_date_to'] = $endDate;
		$receipts = $receiptModel->search($receiptParams);

		// QR Codes
		$qrParams = array('vendor_id' => $vendorId);
		$qrCodes = $qrCodeModel->search($qrParams);

		// Stock Inventory
		$stockInventory = array();
		if (!empty($qrCodes)) {
			$qrIds = array_column($qrCodes, 'qr_id');
			foreach ($qrIds as $qrId) {
				$inv = $stockInventoryModel->findByQRID($qrId);
				$stockInventory = array_merge($stockInventory, $inv);
			}
		}

		// Calculate totals
		$totalPurchaseOrders = count($purchaseOrders);
		$totalPurchaseAmount = 0;
		foreach ($purchaseOrders as $po) {
			$totalPurchaseAmount += (float) ($po['final_amount'] ?? 0);
		}

		$totalReceipts = count($receipts);
		$totalQRCodes = count($qrCodes);
		$totalInventoryItems = count($stockInventory);
		$totalInventoryValue = 0;
		$qrIds = array();
		foreach ($stockInventory as $inv) {
			if (isset($inv['qr_id'])) {
				$qrIds[] = (int) $inv['qr_id'];
			}
		}
		if (!empty($qrIds)) {
			$qrModel = new QrCodeModel();
			$qrCodesForPrice = $qrModel->whereIn('qr_id', array_unique($qrIds))->findAll();
			$qrPriceMap = array();
			foreach ($qrCodesForPrice as $qr) {
				$qrPriceMap[(int) $qr['qr_id']] = (float) ($qr['purchase_price'] ?? 0);
			}
			foreach ($stockInventory as $inv) {
				$qty = (float) ($inv['quantity'] ?? 0);
				$qrId = (int) ($inv['qr_id'] ?? 0);
				$price = isset($qrPriceMap[$qrId]) ? $qrPriceMap[$qrId] : 0;
				$totalInventoryValue += $qty * $price;
			}
		}

		$data = array(
			'vendor' => $vendor,
			'purchaseOrders' => $purchaseOrders,
			'receipts' => $receipts,
			'qrCodes' => $qrCodes,
			'stockInventory' => $stockInventory,
			'startDate' => $startDate,
			'endDate' => $endDate,
			'summary' => array(
				'total_purchase_orders' => $totalPurchaseOrders,
				'total_purchase_amount' => $totalPurchaseAmount,
				'total_receipts' => $totalReceipts,
				'total_qr_codes' => $totalQRCodes,
				'total_inventory_items' => $totalInventoryItems,
				'total_inventory_value' => $totalInventoryValue,
			),
		);

		$html = view('reports/pdf/vendor', $data);
		$pdf = new PDF();
		$filename = 'vendor_report_' . ($vendor['company_name'] ?? $vendorId) . '_' . date('Ymd_His') . '.pdf';
		$pdf->generate($html, array(
			'name' => $filename,
			'download' => true,
			'orientation' => 'portrait',
			'paper' => 'a4'
		));
	}

	private function exportGodownPdf($godownId, $startDate, $endDate)
	{
		$godownModel = new GodownModel();
		$godown = $godownModel->findByID($godownId);
		if (!$godown) {
			return redirect()->to('reports/godown')->with('error', 'Godown not found');
		}

		$purchaseOrderModel = new PurchaseOrderModel();
		$receiptModel = new ReceiptModel();
		$stockInventoryModel = new StockInventoryModel();
		$stockTransferModel = new StockTransferModel();
		$shopRequestModel = new ShopRequestModel();

		// Purchase Orders
		$poParams = array('godown_id' => $godownId);
		if ($startDate !== '') $poParams['order_date_from'] = $startDate;
		if ($endDate !== '') $poParams['order_date_to'] = $endDate;
		$purchaseOrders = $purchaseOrderModel->search($poParams);

		// Stock Receipts
		$receiptParams = array('godown_id' => $godownId);
		if ($startDate !== '') $receiptParams['receipt_date_from'] = $startDate;
		if ($endDate !== '') $receiptParams['receipt_date_to'] = $endDate;
		$receipts = $receiptModel->search($receiptParams);

		// Stock Inventory
		$inventoryParams = array(
			'location_type' => 'godown',
			'location_id' => $godownId,
			'is_available' => 1
		);
		$stockInventory = $stockInventoryModel->search($inventoryParams);

		// Stock Transfers (from this godown)
		$transferFromParams = array('from_location_type' => 'godown', 'from_location_id' => $godownId);
		if ($startDate !== '') $transferFromParams['dispatch_date_from'] = $startDate;
		if ($endDate !== '') $transferFromParams['dispatch_date_to'] = $endDate;
		$transfersFrom = $stockTransferModel->search($transferFromParams);

		// Stock Transfers (to this godown)
		$transferToParams = array('to_location_type' => 'godown', 'to_location_id' => $godownId);
		if ($startDate !== '') $transferToParams['dispatch_date_from'] = $startDate;
		if ($endDate !== '') $transferToParams['dispatch_date_to'] = $endDate;
		$transfersTo = $stockTransferModel->search($transferToParams);

		// Shop Requests
		$requestParams = array('godown_id' => $godownId);
		if ($startDate !== '') $requestParams['request_date_from'] = $startDate;
		if ($endDate !== '') $requestParams['request_date_to'] = $endDate;
		$shopRequests = $shopRequestModel->search($requestParams);

		// Calculate totals
		$totalPurchaseOrders = count($purchaseOrders);
		$totalPurchaseAmount = 0;
		foreach ($purchaseOrders as $po) {
			$totalPurchaseAmount += (float) ($po['final_amount'] ?? 0);
		}

		$totalReceipts = count($receipts);
		$totalInventoryItems = count($stockInventory);
		$totalInventoryQuantity = 0;
		$totalInventoryValue = 0;
		$qrIds = array();
		foreach ($stockInventory as $inv) {
			if (isset($inv['qr_id'])) {
				$qrIds[] = (int) $inv['qr_id'];
			}
			$totalInventoryQuantity += (float) ($inv['quantity'] ?? 0);
		}
		if (!empty($qrIds)) {
			$qrModel = new QrCodeModel();
			$qrCodesForPrice = $qrModel->whereIn('qr_id', array_unique($qrIds))->findAll();
			$qrPriceMap = array();
			foreach ($qrCodesForPrice as $qr) {
				$qrPriceMap[(int) $qr['qr_id']] = (float) ($qr['purchase_price'] ?? 0);
			}
			foreach ($stockInventory as $inv) {
				$qty = (float) ($inv['quantity'] ?? 0);
				$qrId = (int) ($inv['qr_id'] ?? 0);
				$price = isset($qrPriceMap[$qrId]) ? $qrPriceMap[$qrId] : 0;
				$totalInventoryValue += $qty * $price;
			}
		}

		$totalTransfersFrom = count($transfersFrom);
		$totalTransfersTo = count($transfersTo);
		$totalShopRequests = count($shopRequests);

		$data = array(
			'godown' => $godown,
			'purchaseOrders' => $purchaseOrders,
			'receipts' => $receipts,
			'stockInventory' => $stockInventory,
			'transfersFrom' => $transfersFrom,
			'transfersTo' => $transfersTo,
			'shopRequests' => $shopRequests,
			'startDate' => $startDate,
			'endDate' => $endDate,
			'summary' => array(
				'total_purchase_orders' => $totalPurchaseOrders,
				'total_purchase_amount' => $totalPurchaseAmount,
				'total_receipts' => $totalReceipts,
				'total_inventory_items' => $totalInventoryItems,
				'total_inventory_quantity' => $totalInventoryQuantity,
				'total_inventory_value' => $totalInventoryValue,
				'total_transfers_from' => $totalTransfersFrom,
				'total_transfers_to' => $totalTransfersTo,
				'total_shop_requests' => $totalShopRequests,
			),
		);

		$html = view('reports/pdf/godown', $data);
		$pdf = new PDF();
		$filename = 'godown_report_' . ($godown['godown_name'] ?? $godownId) . '_' . date('Ymd_His') . '.pdf';
		$pdf->generate($html, array(
			'name' => $filename,
			'download' => true,
			'orientation' => 'portrait',
			'paper' => 'a4'
		));
	}

	private function exportShopPdf($shopId, $startDate, $endDate)
	{
		$shopModel = new ShopModel();
		$shop = $shopModel->findByID($shopId);
		if (!$shop) {
			return redirect()->to('reports/shop')->with('error', 'Shop not found');
		}

		$saleModel = new SaleModel();
		$stockInventoryModel = new StockInventoryModel();
		$stockTransferModel = new StockTransferModel();
		$shopRequestModel = new ShopRequestModel();

		// Sales
		$saleParams = array('location_type' => 'shop', 'location_id' => $shopId);
		if ($startDate !== '') $saleParams['sale_date_from'] = $startDate;
		if ($endDate !== '') $saleParams['sale_date_to'] = $endDate;
		$sales = $saleModel->search($saleParams);

		// Stock Inventory
		$inventoryParams = array(
			'location_type' => 'shop',
			'location_id' => $shopId,
			'is_available' => 1
		);
		$stockInventory = $stockInventoryModel->search($inventoryParams);

		// Stock Transfers (from this shop)
		$transferFromParams = array('from_location_type' => 'shop', 'from_location_id' => $shopId);
		if ($startDate !== '') $transferFromParams['dispatch_date_from'] = $startDate;
		if ($endDate !== '') $transferFromParams['dispatch_date_to'] = $endDate;
		$transfersFrom = $stockTransferModel->search($transferFromParams);

		// Stock Transfers (to this shop)
		$transferToParams = array('to_location_type' => 'shop', 'to_location_id' => $shopId);
		if ($startDate !== '') $transferToParams['dispatch_date_from'] = $startDate;
		if ($endDate !== '') $transferToParams['dispatch_date_to'] = $endDate;
		$transfersTo = $stockTransferModel->search($transferToParams);

		// Shop Requests
		$requestParams = array('shop_id' => $shopId);
		if ($startDate !== '') $requestParams['request_date_from'] = $startDate;
		if ($endDate !== '') $requestParams['request_date_to'] = $endDate;
		$shopRequests = $shopRequestModel->search($requestParams);

		// Calculate totals
		$totalSales = count($sales);
		$totalSalesAmount = 0;
		foreach ($sales as $sale) {
			$totalSalesAmount += (float) ($sale['final_amount'] ?? 0);
		}

		$totalInventoryItems = count($stockInventory);
		$totalInventoryQuantity = 0;
		$totalInventoryValue = 0;
		$qrIds = array();
		foreach ($stockInventory as $inv) {
			if (isset($inv['qr_id'])) {
				$qrIds[] = (int) $inv['qr_id'];
			}
			$totalInventoryQuantity += (float) ($inv['quantity'] ?? 0);
		}
		if (!empty($qrIds)) {
			$qrModel = new QrCodeModel();
			$qrCodesForPrice = $qrModel->whereIn('qr_id', array_unique($qrIds))->findAll();
			$qrPriceMap = array();
			foreach ($qrCodesForPrice as $qr) {
				$qrPriceMap[(int) $qr['qr_id']] = (float) ($qr['purchase_price'] ?? 0);
			}
			foreach ($stockInventory as $inv) {
				$qty = (float) ($inv['quantity'] ?? 0);
				$qrId = (int) ($inv['qr_id'] ?? 0);
				$price = isset($qrPriceMap[$qrId]) ? $qrPriceMap[$qrId] : 0;
				$totalInventoryValue += $qty * $price;
			}
		}

		$totalTransfersFrom = count($transfersFrom);
		$totalTransfersTo = count($transfersTo);
		$totalShopRequests = count($shopRequests);

		$data = array(
			'shop' => $shop,
			'sales' => $sales,
			'stockInventory' => $stockInventory,
			'transfersFrom' => $transfersFrom,
			'transfersTo' => $transfersTo,
			'shopRequests' => $shopRequests,
			'startDate' => $startDate,
			'endDate' => $endDate,
			'summary' => array(
				'total_sales' => $totalSales,
				'total_sales_amount' => $totalSalesAmount,
				'total_inventory_items' => $totalInventoryItems,
				'total_inventory_quantity' => $totalInventoryQuantity,
				'total_inventory_value' => $totalInventoryValue,
				'total_transfers_from' => $totalTransfersFrom,
				'total_transfers_to' => $totalTransfersTo,
				'total_shop_requests' => $totalShopRequests,
			),
		);

		$html = view('reports/pdf/shop', $data);
		$pdf = new PDF();
		$filename = 'shop_report_' . ($shop['shop_name'] ?? $shopId) . '_' . date('Ymd_His') . '.pdf';
		$pdf->generate($html, array(
			'name' => $filename,
			'download' => true,
			'orientation' => 'portrait',
			'paper' => 'a4'
		));
	}
}

