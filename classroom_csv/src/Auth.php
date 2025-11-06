<?php

namespace Classroom;

require_once __DIR__ . '/AccountStore.php';

class Auth
{
    private AccountStore $store;

    public function __construct(AccountStore $store)
    {
        $this->store = $store;
    }

   
    public function authenticate(string $username, string $password)
    {
        $rec = $this->store->find($username);
        if ($rec === null) return false;
        if (password_verify($password, $rec['password_hash'])) {
            return [
                'username' => $rec['username'],
                'role'     => $rec['role'] ?? 'user',
                'name'     => $rec['name'] ?? ''
            ];
        }
        return false;
    }

    /**
     * Đăng ký user mới
     * @return array ['ok'=>bool, 'error'=>string?]
     */
    public function registerUser(string $username, string $password, string $name = ''): array
    {
        if ($this->store->existsUser($username)) {
            return ['ok' => false, 'error' => 'Username đã tồn tại.'];
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $ok = $this->store->addUser($username, $hash, $name);
        return ['ok' => $ok];
    }

    public function getStore(): AccountStore
    {
        return $this->store;
    }
}