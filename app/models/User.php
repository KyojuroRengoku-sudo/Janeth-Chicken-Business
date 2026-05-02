<?php
namespace App\Models;

use PDO;

/**
 * User model – wraps all user / registration-request queries.
 */
class User
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // ── Auth ──────────────────────────────────────────────────────────────

    public function findByUsername(string $username): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, username, password_hash, role FROM users WHERE username = ?'
        );
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    // ── User management (admin) ───────────────────────────────────────────

    public function all(): array
    {
        return $this->pdo
            ->query('SELECT id, username, role, created_at FROM users ORDER BY created_at DESC')
            ->fetchAll();
    }

    public function create(string $username, string $passwordHash, string $role): int
    {
        $this->pdo->prepare(
            'INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)'
        )->execute([$username, $passwordHash, $role]);
        return (int)$this->pdo->lastInsertId();
    }

    public function updatePassword(int $id, string $passwordHash): void
    {
        $this->pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
            ->execute([$passwordHash, $id]);
    }

    public function updateRole(int $id, string $role): void
    {
        $this->pdo->prepare('UPDATE users SET role = ? WHERE id = ?')
            ->execute([$role, $id]);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
    }

    public function usernameExists(string $username): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        return (bool)$stmt->fetch();
    }

    // ── Registration requests ─────────────────────────────────────────────

    public function pendingRequests(): array
    {
        return $this->pdo
            ->query("SELECT * FROM registration_requests WHERE status='pending' ORDER BY created_at DESC")
            ->fetchAll();
    }

    public function allRequests(): array
    {
        return $this->pdo
            ->query('SELECT * FROM registration_requests ORDER BY created_at DESC')
            ->fetchAll();
    }

    public function createRequest(string $username, string $passwordHash, string $role): void
    {
        $this->pdo->prepare(
            'INSERT INTO registration_requests (username, password_hash, requested_role) VALUES (?, ?, ?)'
        )->execute([$username, $passwordHash, $role]);
    }

    public function pendingRequestExists(string $username): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT id FROM registration_requests WHERE username = ? AND status = 'pending'"
        );
        $stmt->execute([$username]);
        return (bool)$stmt->fetch();
    }

    public function findPendingRequest(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM registration_requests WHERE id = ? AND status = 'pending'"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function approveRequest(int $id, array $row): void
    {
        $this->pdo->beginTransaction();
        try {
            $this->create($row['username'], $row['password_hash'], $row['requested_role']);
            $this->pdo->prepare(
                "UPDATE registration_requests SET status='approved', reviewed_at=NOW() WHERE id=?"
            )->execute([$id]);
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function rejectRequest(int $id): void
    {
        $this->pdo->prepare(
            "UPDATE registration_requests SET status='rejected', reviewed_at=NOW() WHERE id=?"
        )->execute([$id]);
    }
}
