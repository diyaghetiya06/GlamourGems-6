<?php
require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Customer Management';

// Handle Toggle Status
if (isset($_GET['toggle_id'])) {
    $toggle_id = intval($_GET['toggle_id']);
    $stmt = mysqli_prepare($conn, "SELECT status FROM users WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $toggle_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($u = mysqli_fetch_assoc($res)) {
        $new_status = ($u['status'] === 'active') ? 'inactive' : 'active';
        $up_stmt = mysqli_prepare($conn, "UPDATE users SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($up_stmt, "si", $new_status, $toggle_id);
        mysqli_stmt_execute($up_stmt);
        mysqli_stmt_close($up_stmt);
        set_flash('success', "Customer status toggled to '$new_status'.");
    }
    mysqli_stmt_close($stmt);
    header('Location: ' . ADMIN_URL . 'users.php');
    exit;
}

// Handle Delete User
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $delete_id);
    if (mysqli_stmt_execute($stmt)) {
        set_flash('success', 'Customer record deleted successfully.');
    } else {
        set_flash('danger', 'Failed to delete customer record.');
    }
    mysqli_stmt_close($stmt);
    header('Location: ' . ADMIN_URL . 'users.php');
    exit;
}

// Fetch Users
$search = sanitize($_GET['search'] ?? '');
$sql = "SELECT * FROM users WHERE 1=1";
if (!empty($search)) {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (name LIKE '%$safe_search%' OR email LIKE '%$safe_search%' OR phone LIKE '%$safe_search%')";
}
$sql .= " ORDER BY created_at DESC";

$users = [];
$res = @mysqli_query($conn, $sql);
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $users[] = $row;
    }
}

include(__DIR__ . '/includes/header.php');
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="page-header-title mb-1">Registered Customers</h2>
        <p class="page-header-sub mb-0">View customer profiles, manage access permissions, and account statuses.</p>
    </div>
</div>

<!-- Search Bar -->
<div class="gg-card mb-4">
    <div class="gg-card-body p-3">
        <form action="" method="GET" class="row g-3">
            <div class="col-12 col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by Customer Name, Email, or Phone..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-gg-gold btn-sm w-100"><i class="fa-solid fa-search me-1"></i> Search</button>
                <?php if (!empty($search)): ?>
                    <a href="<?php echo ADMIN_URL; ?>users.php" class="btn btn-gg-outline btn-sm">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="gg-card">
    <div class="gg-card-header">
        <h5 class="gg-card-title"><i class="fa-solid fa-users"></i> Customer Accounts (<?php echo count($users); ?>)</h5>
    </div>
    <div class="gg-card-body p-0">
        <div class="table-responsive">
            <table class="table table-gg align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">Avatar</th>
                        <th>Customer Name</th>
                        <th>Email Address</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Joined Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($u['name']); ?>&background=1e2430&color=d4af37" class="rounded-circle" width="36" height="36" alt="User Avatar">
                                </td>
                                <td><div class="fw-semibold text-light"><?php echo htmlspecialchars($u['name']); ?></div></td>
                                <td><span class="text-gold"><?php echo htmlspecialchars($u['email']); ?></span></td>
                                <td><span class="text-muted fs-8"><?php echo htmlspecialchars($u['phone'] ?? 'N/A'); ?></span></td>
                                <td>
                                    <?php if ($u['status'] === 'active'): ?>
                                        <span class="badge-gg badge-gg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge-gg badge-gg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted fs-8"><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="<?php echo ADMIN_URL; ?>users.php?toggle_id=<?php echo $u['id']; ?>" class="btn-action" title="Toggle Status">
                                            <i class="fa-solid fa-power-off"></i>
                                        </a>
                                        <a href="<?php echo ADMIN_URL; ?>users.php?delete_id=<?php echo $u['id']; ?>" class="btn-action btn-action-danger" title="Delete User" onclick="return confirm('Delete customer account for <?php echo htmlspecialchars($u['name']); ?>?');">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No customer accounts found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include(__DIR__ . '/includes/footer.php'); ?>
