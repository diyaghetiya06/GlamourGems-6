<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/includes/auth_check.php');
require_once(__DIR__ . '/../config/database.php');

$page_title = 'Executive Dashboard';

// Metrics Queries (with fallback defaults if table empty or query fails)
$total_revenue = 0;
$total_orders = 0;
$total_products = 0;
$total_users = 0;

// Total Revenue
$res = @mysqli_query($conn, "SELECT SUM(total_amount) AS revenue FROM orders WHERE payment_status = 'Paid'");
if ($res && $row = mysqli_fetch_assoc($res)) {
    $total_revenue = $row['revenue'] ?? 0;
}

// Total Orders
$res = @mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders");
if ($res && $row = mysqli_fetch_assoc($res)) {
    $total_orders = $row['total'] ?? 0;
}

// Total Active Products
$res = @mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE status = 'active'");
if ($res && $row = mysqli_fetch_assoc($res)) {
    $total_products = $row['total'] ?? 0;
}

// Total Customers
$res = @mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
if ($res && $row = mysqli_fetch_assoc($res)) {
    $total_users = $row['total'] ?? 0;
}

// Recent Orders
$recent_orders = [];
$res = @mysqli_query($conn, "SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $recent_orders[] = $row;
    }
}

// Low Stock Products Alert
$low_stock = [];
$res = @mysqli_query($conn, "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.stock <= 5 ORDER BY p.stock ASC LIMIT 5");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $low_stock[] = $row;
    }
}

// Unread Contact Messages
$unread_contacts = 0;
$res = @mysqli_query($conn, "SELECT COUNT(*) as total FROM contacts WHERE status = 'unread'");
if ($res && $row = mysqli_fetch_assoc($res)) {
    $unread_contacts = $row['total'] ?? 0;
}

include(__DIR__ . '/includes/header.php');

?>

<div class="dashboard-page">

<!-- Header Banner -->
<div class="dashboard-hero d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <span class="dashboard-eyebrow"><i class="fa-solid fa-sparkles"></i> Store overview</span>
        <h2 class="page-header-title mb-1">Executive Dashboard</h2>
        <p class="page-header-sub mb-0">Overview of sales, catalog performance, and customer orders.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo ADMIN_URL; ?>add-product.php" class="btn btn-gg-gold btn-sm">
            <i class="fa-solid fa-plus me-1"></i> Add Product
        </a>
        <a href="<?php echo ADMIN_URL; ?>orders.php" class="btn btn-gg-outline btn-sm">
            <i class="fa-solid fa-receipt me-1"></i> View Orders
        </a>
    </div>
</div>

<!-- Key Performance Indicators (KPI Cards) -->
<div class="row g-4 mb-4 dashboard-kpis">
    <!-- Revenue -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="metric-label">Total Revenue</span>
                    <div class="metric-value"><?php echo format_price($total_revenue); ?></div>
                </div>
                <div class="metric-icon">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                </div>
            </div>
            <div class="mt-3 fs-7 text-success">
                <i class="fa-solid fa-arrow-trend-up me-1"></i> Paid completed orders
            </div>
        </div>
    </div>

    <!-- Orders -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="metric-label">Total Orders</span>
                    <div class="metric-value"><?php echo number_format($total_orders); ?></div>
                </div>
                <div class="metric-icon">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>
            </div>
            <div class="mt-3 fs-7 text-info">
                <i class="fa-solid fa-clock me-1"></i> Processing & Shipped
            </div>
        </div>
    </div>

    <!-- Products -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="metric-label">Active Products</span>
                    <div class="metric-value"><?php echo number_format($total_products); ?></div>
                </div>
                <div class="metric-icon">
                    <i class="fa-solid fa-gem"></i>
                </div>
            </div>
            <div class="mt-3 fs-7 text-gold" style="color: var(--gg-gold);">
                <i class="fa-solid fa-box-open me-1"></i> In live catalog
            </div>
        </div>
    </div>

    <!-- Customers -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="metric-label">Registered Users</span>
                    <div class="metric-value"><?php echo number_format($total_users); ?></div>
                </div>
                <div class="metric-icon">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
            </div>
            <div class="mt-3 fs-7 text-secondary">
                <i class="fa-solid fa-users me-1"></i> Active VIP accounts
            </div>
        </div>
    </div>
</div>

<!-- Revenue Analytics Chart & Inventory Alerts Section -->
<div class="row g-4 mb-4">
    <!-- Chart Column -->
    <div class="col-12 col-lg-8">
        <div class="gg-card h-100">
            <div class="gg-card-header">
                <h5 class="gg-card-title"><i class="fa-solid fa-chart-line"></i> Revenue & Order Trends</h5>
                <span class="badge bg-dark border border-secondary text-gold">2026 Overview</span>
            </div>
            <div class="gg-card-body">
                <canvas id="salesChart" style="max-height: 320px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Low Stock & Quick Info Column -->
    <div class="col-12 col-lg-4">
        <!-- Low Stock Card -->
        <div class="gg-card mb-4">
            <div class="gg-card-header">
                <h5 class="gg-card-title"><i class="fa-solid fa-triangle-exclamation text-warning"></i> Stock Warning</h5>
                <small class="text-muted">Threshold &le; 5 units</small>
            </div>
            <div class="gg-card-body p-0">
                <?php if (!empty($low_stock)): ?>
                    <div class="list-group list-group-flush bg-transparent">
                        <?php foreach ($low_stock as $item): ?>
                            <div class="list-group-item bg-transparent border-secondary text-light d-flex justify-content-between align-items-center py-3 px-3">
                                <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                                    <i class="fa-solid fa-gem text-gold"></i>
                                    <div class="text-truncate">
                                        <div class="fw-semibold text-truncate fs-7"><?php echo htmlspecialchars($item['name']); ?></div>
                                        <small class="text-muted fs-8">SKU: <?php echo htmlspecialchars($item['sku']); ?></small>
                                    </div>
                                </div>
                                <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-25 rounded-pill fs-8">
                                    <?php echo $item['stock']; ?> left
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-4 text-center text-muted">
                        <i class="fa-solid fa-circle-check text-success fa-2x mb-2 d-block"></i>
                        All inventory stocks are healthy.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Notice Card -->
        <div class="gg-card">
            <div class="gg-card-header">
                <h5 class="gg-card-title"><i class="fa-solid fa-envelope"></i> Customer Messages</h5>
                <a href="<?php echo ADMIN_URL; ?>contacts.php" class="fs-8 text-gold">View All</a>
            </div>
            <div class="gg-card-body text-center py-4">
                <div class="display-6 fw-bold text-gold brand-font mb-2"><?php echo $unread_contacts; ?></div>
                <p class="text-secondary fs-7 mb-3">Unread customer inquiries from contact form.</p>
                <a href="<?php echo ADMIN_URL; ?>contacts.php" class="btn btn-sm btn-gg-outline">
                    <i class="fa-solid fa-comments me-1"></i> Open Inquiry Inbox
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="gg-card">
    <div class="gg-card-header">
        <h5 class="gg-card-title"><i class="fa-solid fa-receipt"></i> Recent Orders</h5>
        <a href="<?php echo ADMIN_URL; ?>orders.php" class="btn btn-sm btn-gg-outline">View All Orders</a>
    </div>
    <div class="gg-card-body p-0">
        <div class="table-responsive">
            <table class="table table-gg align-middle mb-0">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Order Status</th>
                        <th>Date</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_orders)): ?>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td class="fw-semibold text-gold"><?php echo htmlspecialchars($order['order_number']); ?></td>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($order['customer_email']); ?></small>
                                </td>
                                <td class="fw-bold"><?php echo format_price($order['total_amount']); ?></td>
                                <td>
                                    <?php
                                    $p_status = $order['payment_status'];
                                    $p_class = ($p_status === 'Paid') ? 'badge-gg-success' : 'badge-gg-warning';
                                    ?>
                                    <span class="badge-gg <?php echo $p_class; ?>"><?php echo $p_status; ?></span>
                                </td>
                                <td>
                                    <?php
                                    $status = $order['order_status'];
                                    $badge_class = 'badge-gg-info';
                                    if ($status === 'Delivered') $badge_class = 'badge-gg-success';
                                    elseif ($status === 'Pending') $badge_class = 'badge-gg-warning';
                                    elseif ($status === 'Cancelled') $badge_class = 'badge-gg-danger';
                                    ?>
                                    <span class="badge-gg <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                                </td>
                                <td class="text-muted fs-8"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></td>
                                <td class="text-end">
                                    <a href="<?php echo ADMIN_URL; ?>orders.php?id=<?php echo $order['id']; ?>" class="btn-action" title="View Order Details">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-inbox me-2"></i> No order records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart Initialization Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    // Gradient fill for chart
    const goldGradient = ctx.createLinearGradient(0, 0, 0, 300);
    goldGradient.addColorStop(0, 'rgba(212, 175, 55, 0.4)');
    goldGradient.addColorStop(1, 'rgba(212, 175, 55, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'],
            datasets: [{
                label: 'Monthly Revenue (₹)',
                data: [420000, 580000, 490000, 640000, 720000, 810000, 790000, 950000],
                borderColor: '#d4af37',
                backgroundColor: goldGradient,
                fill: true,
                tension: 0.35,
                borderWidth: 3,
                pointBackgroundColor: '#f4d068',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: '#9aa4b8',
                        font: { family: 'Poppins' }
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { color: '#9aa4b8' }
                },
                y: {
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { 
                        color: '#9aa4b8',
                        callback: function(value) { return '₹' + (value/1000) + 'k'; }
                    }
                }
            }
        }
    });
});
</script>

</div>

<?php include(__DIR__ . '/includes/footer.php'); ?>
