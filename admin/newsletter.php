<?php
require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Newsletter Subscribers';

// Export CSV Action
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=glamour_gems_subscribers_' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Email Address', 'Subscribed At']);

    $res = mysqli_query($conn, "SELECT * FROM newsletter ORDER BY subscribed_at DESC");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            fputcsv($output, [$row['id'], $row['email'], $row['subscribed_at']]);
        }
    }
    fclose($output);
    exit;
}

// Handle Delete Subscriber
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = mysqli_prepare($conn, "DELETE FROM newsletter WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    set_flash('success', 'Subscriber removed from list.');
    header('Location: ' . ADMIN_URL . 'newsletter.php');
    exit;
}

// Fetch Subscribers
$subscribers = [];
$res = @mysqli_query($conn, "SELECT * FROM newsletter ORDER BY subscribed_at DESC");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $subscribers[] = $row;
    }
}

include(__DIR__ . '/includes/header.php');
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="page-header-title mb-1">Newsletter Subscribers</h2>
        <p class="page-header-sub mb-0">VIP email newsletter subscriptions collected from the storefront.</p>
    </div>
    <div>
        <a href="<?php echo ADMIN_URL; ?>newsletter.php?action=export_csv" class="btn btn-gg-gold">
            <i class="fa-solid fa-file-csv me-2"></i> Export Subscribers (CSV)
        </a>
    </div>
</div>

<div class="gg-card">
    <div class="gg-card-header">
        <h5 class="gg-card-title"><i class="fa-solid fa-paper-plane"></i> Subscriber Mailing List (<?php echo count($subscribers); ?>)</h5>
    </div>
    <div class="gg-card-body p-0">
        <div class="table-responsive">
            <table class="table table-gg align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Email Address</th>
                        <th>Subscribed Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($subscribers)): ?>
                        <?php foreach ($subscribers as $sub): ?>
                            <tr>
                                <td>#<?php echo $sub['id']; ?></td>
                                <td><span class="text-gold fw-medium"><?php echo htmlspecialchars($sub['email']); ?></span></td>
                                <td class="text-muted fs-8"><?php echo date('d M Y, h:i A', strtotime($sub['subscribed_at'])); ?></td>
                                <td class="text-end">
                                    <a href="<?php echo ADMIN_URL; ?>newsletter.php?delete_id=<?php echo $sub['id']; ?>" class="btn-action btn-action-danger" title="Remove Subscriber" onclick="return confirm('Remove <?php echo htmlspecialchars($sub['email']); ?> from mailing list?');">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No newsletter subscribers yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include(__DIR__ . '/includes/footer.php'); ?>
