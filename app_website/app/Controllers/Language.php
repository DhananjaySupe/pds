<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Language extends BaseController
{
    public function change()
    {
        // Check if it's an AJAX request
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $language = $this->request->getPost('language');

        // Validate language
        $supportedLanguages = ['en', 'hi', 'mr']; // Add more languages as needed
        if (!in_array($language, $supportedLanguages)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unsupported language']);
        }

        try {
            // Set the locale for the current request
            $this->request->setLocale($language);

            // Update user session if user is logged in
            if ($this->isUserLoggedIn()) {
                $this->session->set('user_language', $language);

                // Update user record in database if needed
                $userModel = new \App\Models\UserModel();
                $userModel->update($this->_user['id'], [
                    'language' => $language,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                // For non-logged in users, store in session
                $this->session->set('guest_language', $language);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Language changed successfully',
                'language' => $language
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error changing language: ' . $e->getMessage()
            ]);
        }
    }

    public function getCurrent()
    {
        $language = 'en'; // default

        if ($this->isUserLoggedIn()) {
            $language = $this->session->get('user_language') ?? 'en';
        } else {
            $language = $this->session->get('guest_language') ?? 'en';
        }

        return $this->response->setJSON([
            'success' => true,
            'language' => $language
        ]);
    }
}