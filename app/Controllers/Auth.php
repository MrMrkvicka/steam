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
}
