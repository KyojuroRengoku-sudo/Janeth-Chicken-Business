<?php
namespace App\Controllers;

use App\Models\User;

/**
 * UserController – admin AJAX endpoints for user + registration management.
 */
class UserController
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            send(['error' => 'Method not allowed'], 405);
        }

        // Approve registration request
        if (isset($_POST['approve_id'])) {
            $id  = (int)$_POST['approve_id'];
            $row = $this->user->findPendingRequest($id);
            if (!$row) {
                send(['success' => false, 'message' => 'Request not found or already handled.']);
            }
            if ($this->user->usernameExists($row['username'])) {
                $this->user->rejectRequest($id);
                send(['success' => false, 'message' => 'Username already exists. Request rejected.']);
            }
            try {
                $this->user->approveRequest($id, $row);
                send(['success' => true]);
            } catch (\Exception $e) {
                send(['success' => false, 'message' => $e->getMessage()]);
            }
        }

        // Reject registration request
        if (isset($_POST['reject_id'])) {
            $this->user->rejectRequest((int)$_POST['reject_id']);
            send(['success' => true]);
        }

        // Add user directly
        if (isset($_POST['add_user'])) {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $role     = in_array($_POST['role'] ?? '', ['admin', 'staff']) ? $_POST['role'] : 'staff';

            if (strlen($username) < 3) send(['success' => false, 'message' => 'Username too short (min 3 chars).']);
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) send(['success' => false, 'message' => 'Username: letters, numbers, underscores only.']);
            if (strlen($password) < 6) send(['success' => false, 'message' => 'Password must be at least 6 characters.']);
            if ($this->user->usernameExists($username)) send(['success' => false, 'message' => 'Username already exists.']);

            $this->user->create($username, password_hash($password, PASSWORD_DEFAULT), $role);
            send(['success' => true]);
        }

        // Change password
        if (isset($_POST['change_password'])) {
            $id       = (int)$_POST['user_id'];
            $password = $_POST['new_password'] ?? '';
            if (strlen($password) < 6) send(['success' => false, 'message' => 'Password must be at least 6 characters.']);
            $this->user->updatePassword($id, password_hash($password, PASSWORD_DEFAULT));
            send(['success' => true]);
        }

        // Change role
        if (isset($_POST['change_role'])) {
            $id   = (int)$_POST['user_id'];
            $role = in_array($_POST['role'] ?? '', ['admin', 'staff']) ? $_POST['role'] : 'staff';
            $this->user->updateRole($id, $role);
            send(['success' => true]);
        }

        // Delete user
        if (isset($_POST['delete_user'])) {
            $id = (int)$_POST['delete_user'];
            if ($id === (int)$_SESSION['user_id']) send(['success' => false, 'message' => 'Cannot delete your own account.']);
            $this->user->delete($id);
            send(['success' => true]);
        }

        send(['error' => 'Invalid request'], 400);
    }
}
