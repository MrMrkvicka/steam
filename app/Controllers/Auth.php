<?php

namespace App\Controllers;

use App\Models\User;

class Auth extends BaseController
{
    /**
     * Displays the login page.
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/');
        }
        return view('auth/login', [
            'title' => 'Přihlášení | Steam',
        ]);
    }

    /**
     * Handles the login submission.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function attemptLogin()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        if (empty($username) || empty($password)) {
            return redirect()->back()->withInput()->with('error', 'Vyplňte uživatelské jméno a heslo.');
        }

        $userModel = new User();
        $user = $userModel->where('username', $username)->first();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Set session data
            session()->set([
                'userId'     => $user['id'],
                'username'   => $user['username'],
                'isLoggedIn' => true,
            ]);

            return redirect()->to('/')->with('success', 'Vítejte zpět, ' . esc($user['username']) . '!');
        }

        return redirect()->back()->withInput()->with('error', 'Nesprávné přihlašovací údaje.');
    }

    /**
     * Logs the user out and clears session.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/')->with('success', 'Byli jste úspěšně odhlášeni.');
    }

    /**
     * Displays the registration page.
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function register()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/');
        }
        return view('auth/register', [
            'title' => 'Registrace | Steam DB',
        ]);
    }

    /**
     * Handles user registration.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function attemptRegister()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/');
        }

        $rules = [
            'username'         => 'required|min_length[3]|max_length[100]|is_unique[users.username]',
            'password'         => 'required|min_length[5]|max_length[255]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            
            // Translate error messages to Czech for better UX
            $errorMsgs = [];
            foreach ($errors as $field => $err) {
                if (strpos($err, 'is_unique') !== false || strpos($err, 'unique') !== false) {
                    $errorMsgs[] = 'Toto uživatelské jméno je již obsazené.';
                } elseif (strpos($err, 'matches') !== false) {
                    $errorMsgs[] = 'Hesla se neshodují.';
                } else {
                    $errorMsgs[] = $err;
                }
            }
            if (empty($errorMsgs)) {
                $errorMsgs = $errors;
            }
            
            return redirect()->back()->withInput()->with('error', 'Chyba registrace: ' . implode(' ', $errorMsgs));
        }

        $userModel = new User();
        
        $userId = $userModel->insert([
            'username'      => $this->request->getPost('username'),
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
        ]);

        if ($userId) {
            // Log in the user immediately
            session()->set([
                'userId'     => $userId,
                'username'   => $this->request->getPost('username'),
                'isLoggedIn' => true,
            ]);

            return redirect()->to('/')->with('success', 'Registrace byla úspěšná! Vítejte, ' . esc($this->request->getPost('username')) . '!');
        }

        return redirect()->back()->withInput()->with('error', 'Při registraci došlo k chybě.');
    }
}
