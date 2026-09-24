<?php
require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Contact Messages';

// Handle Actions (Mark Read / Delete)
if (isset($_GET['mark_read'])) {
    $id = intval($_GET['mark_read']);
    $stmt = mysqli_prepare($conn, "UPDATE contacts SET status = 'read' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    set_flash('success', 'Message marked as read.');
    header('Location: ' . ADMIN_URL . 'contacts.php');
    exit;
}

if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = mysqli_prepare($conn, "DELETE FROM contacts WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    set_flash('success', 'Contact message deleted.');
    header('Location: ' . ADMIN_URL . 'contacts.php');
    exit;
}

// Fetch Messages
$contacts = [];
$res = @mysqli_query($conn, "SELECT * FROM contacts ORDER BY created_at DESC");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $contacts[] = $row;
    }
}

include(__DIR__ . '/includes/header.php');
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="page-header-title mb-1">Contact Form Inquiries</h2>
        <p class="page-header-sub mb-0">Customer inquiries, custom jewelry consultation requests, and support messages.</p>
    </div>
</div>

<div class="gg-card">
    <div class="gg-card-header">
        <h5 class="gg-card-title"><i class="fa-solid fa-envelope"></i> Message Inbox (<?php echo count($contacts); ?>)</h5>
    </div>
    <div class="gg-card-body p-0">
        <div class="table-responsive">
            <table class="table table-gg align-middle mb-0">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Sender</th>
                        <th>Subject</th>
                        <th>Date Received</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($contacts)): ?>
                        <?php foreach ($contacts as $msg): ?>
                            <tr class="<?php echo ($msg['status'] === 'unread') ? 'fw-bold bg-dark bg-opacity-25' : ''; ?>">
                                <td>
                                    <?php if ($msg['status'] === 'unread'): ?>
                                        <span class="badge-gg badge-gg-warning"><i class="fa-solid fa-envelope"></i> Unread</span>
                                    <?php elseif ($msg['status'] === 'read'): ?>
                                        <span class="badge-gg badge-gg-info"><i class="fa-solid fa-envelope-open"></i> Read</span>
                                    <?php else: ?>
                                        <span class="badge-gg badge-gg-success"><i class="fa-solid fa-reply"></i> Replied</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="text-light"><?php echo htmlspecialchars($msg['name']); ?></div>
                                    <small class="text-gold"><?php echo htmlspecialchars($msg['email']); ?></small>
                                </td>
                                <td>
                                    <span class="text-light"><?php echo htmlspecialchars($msg['subject']); ?></span>
                                    <small class="text-muted d-block text-truncate" style="max-width: 300px;"><?php echo htmlspecialchars($msg['message']); ?></small>
                                </td>
                                <td class="text-muted fs-8"><?php echo date('d M Y, h:i A', strtotime($msg['created_at'])); ?></td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn-action" data-bs-toggle="modal" data-bs-target="#msgModal<?php echo $msg['id']; ?>" title="Read Full Message">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <?php if ($msg['status'] === 'unread'): ?>
                                            <a href="<?php echo ADMIN_URL; ?>contacts.php?mark_read=<?php echo $msg['id']; ?>" class="btn-action" title="Mark as Read">
                                                <i class="fa-solid fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?php echo ADMIN_URL; ?>contacts.php?delete_id=<?php echo $msg['id']; ?>" class="btn-action btn-action-danger" title="Delete Message" onclick="return confirm('Delete message from <?php echo htmlspecialchars($msg['name']); ?>?');">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <!-- Message Detail Modal -->
                            <div class="modal fade" id="msgModal<?php echo $msg['id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content modal-content-gg">
                                        <div class="modal-header modal-header-gg">
                                            <h5 class="modal-title brand-font text-gold">Inquiry - <?php echo htmlspecialchars($msg['subject']); ?></h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="mb-3 border-bottom border-secondary pb-3">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <strong class="text-light fs-6"><?php echo htmlspecialchars($msg['name']); ?></strong>
                                                        <div class="text-gold fs-7"><?php echo htmlspecialchars($msg['email']); ?></div>
                                                    </div>
                                                    <div class="text-muted fs-8"><?php echo date('d M Y, h:i A', strtotime($msg['created_at'])); ?></div>
                                                </div>
                                            </div>
                                            <div class="p-3 bg-dark rounded border border-secondary text-secondary fs-7">
                                                <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                            </div>
                                        </div>
                                        <div class="modal-footer modal-footer-gg d-flex justify-content-between">
                                            <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>?subject=Re: <?php echo urlencode($msg['subject']); ?>" class="btn btn-gg-gold btn-sm">
                                                <i class="fa-solid fa-reply me-1"></i> Reply via Email
                                            </a>
                                            <button type="button" class="btn btn-gg-outline btn-sm" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No contact messages received.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include(__DIR__ . '/includes/footer.php'); ?>
