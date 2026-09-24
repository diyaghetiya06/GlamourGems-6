<?php
require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Order Management';

// Handle Order Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = intval($_POST['order_id'] ?? 0);
    $new_status = sanitize($_POST['order_status'] ?? '');

    $allowed_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];

    if ($order_id > 0 && in_array($new_status, $allowed_statuses)) {
        $stmt = mysqli_prepare($conn, "UPDATE orders SET order_status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $new_status, $order_id);
        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', "Order #$order_id status updated to '$new_status'.");
        } else {
            set_flash('danger', 'Failed to update order status.');
        }
        mysqli_stmt_close($stmt);
    }
    header('Location: ' . ADMIN_URL . 'orders.php');
    exit;
}

// Filter Status parameter
$filter_status = sanitize($_GET['status'] ?? 'All');
$search = sanitize($_GET['search'] ?? '');

$sql = "SELECT * FROM orders WHERE 1=1";

if ($filter_status !== 'All') {
    $safe_status = mysqli_real_escape_string($conn, $filter_status);
    $sql .= " AND order_status = '$safe_status'";
}

if (!empty($search)) {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (order_number LIKE '%$safe_search%' OR customer_name LIKE '%$safe_search%' OR customer_email LIKE '%$safe_search%')";
}

$sql .= " ORDER BY created_at DESC";

$orders = [];
$res = @mysqli_query($conn, $sql);
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        // Fetch order items for each order
        $items = [];
        $order_id = $row['id'];
        $item_res = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id = $order_id");
        if ($item_res) {
            while ($item = mysqli_fetch_assoc($item_res)) {
                $items[] = $item;
            }
        }
        $row['items'] = $items;
        $orders[] = $row;
    }
}

include(__DIR__ . '/includes/header.php');
?>

<!-- Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="page-header-title mb-1">Customer Orders</h2>
        <p class="page-header-sub mb-0">Track customer purchases, fulfillment, and update order statuses.</p>
    </div>
</div>

<!-- Filters & Search -->
<div class="gg-card mb-4">
    <div class="gg-card-body p-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <!-- Status Tabs -->
            <div class="nav nav-pills flex-nowrap overflow-auto w-100 w-md-auto pb-2 pb-md-0" style="gap: 6px;">
                <?php
                $statuses = ['All', 'Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
                foreach ($statuses as $st):
                    $active = ($filter_status === $st) ? 'active bg-gold text-dark font-semibold' : 'btn-gg-outline text-secondary';
                ?>
                    <a href="<?php echo ADMIN_URL; ?>orders.php?status=<?php echo urlencode($st); ?>" class="nav-link py-1 px-3 fs-8 rounded-pill btn <?php echo ($filter_status === $st) ? 'btn-gg-gold' : 'btn-gg-outline'; ?>">
                        <?php echo $st; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Search Form -->
            <form action="" method="GET" class="d-flex gap-2 w-100 w-md-auto" style="max-width: 320px;">
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter_status); ?>">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Search Order # or Customer..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-gg-gold" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Orders List Table -->
<div class="gg-card">
    <div class="gg-card-header">
        <h5 class="gg-card-title"><i class="fa-solid fa-receipt"></i> Orders List (<?php echo count($orders); ?>)</h5>
    </div>
    <div class="gg-card-body p-0">
        <div class="table-responsive">
            <table class="table table-gg align-middle mb-0">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer Details</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Order Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $ord): ?>
                            <tr>
                                <td class="fw-semibold text-gold"><?php echo htmlspecialchars($ord['order_number']); ?></td>
                                <td>
                                    <div class="fw-semibold text-light"><?php echo htmlspecialchars($ord['customer_name']); ?></div>
                                    <small class="text-muted d-block"><?php echo htmlspecialchars($ord['customer_email']); ?></small>
                                    <small class="text-muted"><?php echo htmlspecialchars($ord['customer_phone']); ?></small>
                                </td>
                                <td class="fw-bold"><?php echo format_price($ord['total_amount']); ?></td>
                                <td>
                                    <span class="badge-gg <?php echo ($ord['payment_status'] === 'Paid') ? 'badge-gg-success' : 'badge-gg-warning'; ?>">
                                        <?php echo $ord['payment_status']; ?> (<?php echo htmlspecialchars($ord['payment_method']); ?>)
                                    </span>
                                </td>
                                <td>
                                    <!-- Status Update Select Dropdown Form -->
                                    <form action="" method="POST" class="d-inline-block">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="order_id" value="<?php echo $ord['id']; ?>">
                                        <select name="order_status" class="form-select form-select-sm bg-dark text-light border-secondary fs-8 py-1 px-2" onchange="this.form.submit()">
                                            <?php
                                            foreach (['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'] as $st_option) {
                                                $selected = ($ord['order_status'] === $st_option) ? 'selected' : '';
                                                echo "<option value='{$st_option}' {$selected}>{$st_option}</option>";
                                            }
                                            ?>
                                        </select>
                                    </form>
                                </td>
                                <td class="text-muted fs-8"><?php echo date('d M Y, h:i A', strtotime($ord['created_at'])); ?></td>
                                <td class="text-end">
                                    <button class="btn-action" data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $ord['id']; ?>" title="View Order Details">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Order Detail Modal -->
                            <div class="modal fade" id="orderModal<?php echo $ord['id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content modal-content-gg">
                                        <div class="modal-header modal-header-gg">
                                            <h5 class="modal-title brand-font text-gold">Order Details - <?php echo htmlspecialchars($ord['order_number']); ?></h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="row g-3 mb-4">
                                                <div class="col-12 col-md-6 border-end border-secondary">
                                                    <h6 class="text-gold fs-7 uppercase fw-bold mb-2">Customer Info</h6>
                                                    <p class="mb-1 text-light fw-medium"><?php echo htmlspecialchars($ord['customer_name']); ?></p>
                                                    <p class="mb-1 text-muted fs-8"><i class="fa-solid fa-envelope me-1"></i> <?php echo htmlspecialchars($ord['customer_email']); ?></p>
                                                    <p class="mb-0 text-muted fs-8"><i class="fa-solid fa-phone me-1"></i> <?php echo htmlspecialchars($ord['customer_phone']); ?></p>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <h6 class="text-gold fs-7 uppercase fw-bold mb-2">Shipping Address</h6>
                                                    <p class="mb-0 text-secondary fs-8"><?php echo nl2br(htmlspecialchars($ord['shipping_address'])); ?></p>
                                                </div>
                                            </div>

                                            <h6 class="text-gold fs-7 uppercase fw-bold mb-2">Purchased Items</h6>
                                            <div class="table-responsive mb-3">
                                                <table class="table table-dark table-sm align-middle fs-8">
                                                    <thead>
                                                        <tr>
                                                            <th>Item Name</th>
                                                            <th>Price</th>
                                                            <th>Qty</th>
                                                            <th class="text-end">Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (!empty($ord['items'])): ?>
                                                            <?php foreach ($ord['items'] as $item): ?>
                                                                <tr>
                                                                    <td class="text-light"><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                                    <td><?php echo format_price($item['price']); ?></td>
                                                                    <td><?php echo $item['quantity']; ?></td>
                                                                    <td class="text-end fw-bold"><?php echo format_price($item['total']); ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <tr><td colspan="4" class="text-center text-muted">No item breakdown available.</td></tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary">
                                                <span class="text-muted fs-7">Payment Method: <strong><?php echo htmlspecialchars($ord['payment_method']); ?></strong></span>
                                                <div class="fs-5 fw-bold text-gold">Total: <?php echo format_price($ord['total_amount']); ?></div>
                                            </div>
                                        </div>
                                        <div class="modal-footer modal-footer-gg">
                                            <button type="button" class="btn btn-gg-outline btn-sm" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">No orders found matching the criteria.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include(__DIR__ . '/includes/footer.php'); ?>
