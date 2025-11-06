<?php

namespace Classroom;

class AccountStore
{
    private string $adminPath;
    private string $userPath;

    public function __construct(string $adminPath, string $userPath)
    {
        $this->adminPath = $adminPath;
        $this->userPath = $userPath;

        if (!file_exists($this->adminPath)) {
            $this->addAdmin('admin', password_hash('Admin@123', PASSWORD_DEFAULT), 'Administrator');
        }
        if (!file_exists($this->userPath)) {
            touch($this->userPath);
        }
    }

    private function readCsv(string $path): array
    {
        $out = [];
        if (!file_exists($path)) return $out;
        $f = fopen($path, 'r');
        if ($f === false) return $out;
        while (($row = fgetcsv($f)) !== false) {
            if (!is_array($row) || count($row) < 2) continue;
            $username = trim($row[0]);
            if ($username === '') continue;
            $out[$username] = [
                'password_hash' => $row[1] ?? '',
                'role' => $row[2] ?? 'user',
                'name' => $row[3] ?? ''
            ];
        }
        fclose($f);
        return $out;
    }

    private function writeCsvAppend(string $path, array $row): bool
    {
        $f = fopen($path, 'a');
        if ($f === false) return false;
        if (!flock($f, LOCK_EX)) { fclose($f); return false; }
        fputcsv($f, $row);
        fflush($f);
        flock($f, LOCK_UN);
        fclose($f);
        return true;
    }

    public function loadAdmins(): array { return $this->readCsv($this->adminPath); }
    public function loadUsers(): array { return $this->readCsv($this->userPath); }

    public function existsUser(string $username): bool
    {
        $username = trim($username);
        if ($username === '') return false;
        $admins = $this->loadAdmins();
        if (isset($admins[$username])) return true;
        $users = $this->loadUsers();
        return isset($users[$username]);
    }

    public function addUser(string $username, string $passwordHash, string $name = ''): bool
    {
        return $this->writeCsvAppend($this->userPath, [$username, $passwordHash, 'user', $name]);
    }

    public function addAdmin(string $username, string $passwordHash, string $name = ''): bool
    {
        return $this->writeCsvAppend($this->adminPath, [$username, $passwordHash, 'admin', $name]);
    }

    public function find(string $username): ?array
    {
        $username = trim($username);
        if ($username === '') return null;
        $admins = $this->loadAdmins();
        if (isset($admins[$username])) {
            $r = $admins[$username];
            $r['username'] = $username;
            return $r;
        }
        $users = $this->loadUsers();
        if (isset($users[$username])) {
            $r = $users[$username];
            $r['username'] = $username;
            return $r;
        }
        return null;
    }
}