<?php namespace App\Controllers;

use App\Models\ExpiryTrackerModel;

class Cron extends BaseController
{
	/**
	 * Update expiry tracker status based on expiry dates
	 * This should be run daily via cron job
	 */
	public function updateExpiryTracker()
	{
		// Optional: Add authentication/key check for security
		$cronKey = $this->request->getGet('key');
		$expectedKey = $this->AppConfig->cronKey;

		if ($cronKey !== $expectedKey) {
			return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized']);
		}

		$expiryTrackerModel = new ExpiryTrackerModel();
		$db = \Config\Database::connect();

		$today = date('Y-m-d');
		$nearExpiryThreshold = date('Y-m-d', strtotime('+30 days'));

		$updated = 0;
		$errors = array();

		try {
			// Update expired items (expiry_date < today)
			// days_remaining will be negative (expiry_date - today)
			$expiredResult = $db->query("
				UPDATE expiry_tracker
				SET status = 'expired',
					days_remaining = DATEDIFF(expiry_date, CURDATE())
				WHERE expiry_date < ?
			", [$today]);
			$expiredCount = $db->affectedRows();

			// Update near expiry items (expiry_date between today and today + 30 days)
			$nearExpiryResult = $db->query("
				UPDATE expiry_tracker
				SET status = 'near_expiry',
					days_remaining = DATEDIFF(expiry_date, CURDATE())
				WHERE expiry_date >= ?
				AND expiry_date <= ?
			", [$today, $nearExpiryThreshold]);
			$nearExpiryCount = $db->affectedRows();

			// Update fresh items (expiry_date > today + 30 days)
			$freshResult = $db->query("
				UPDATE expiry_tracker
				SET status = 'fresh',
					days_remaining = DATEDIFF(expiry_date, CURDATE())
				WHERE expiry_date > ?
			", [$nearExpiryThreshold]);
			$freshCount = $db->affectedRows();

			$updated = $expiredCount + $nearExpiryCount + $freshCount;

			return $this->response->setJSON([
				'success' => true,
				'message' => 'Expiry tracker updated successfully',
				'updated' => $updated,
				'expired' => $expiredCount,
				'near_expiry' => $nearExpiryCount,
				'fresh' => $freshCount,
				'timestamp' => date('Y-m-d H:i:s')
			]);

		} catch (\Exception $e) {
			return $this->response->setStatusCode(500)->setJSON([
				'success' => false,
				'message' => 'Error updating expiry tracker: ' . $e->getMessage(),
				'error' => $e->getMessage()
			]);
		}
	}

}

