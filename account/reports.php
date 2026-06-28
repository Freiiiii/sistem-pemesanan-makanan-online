<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireAdmin();
$user = getCurrentUser();
class Report
{
    private $startDate;
    private $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    public function getSalesReport()
    {
        return generateSalesReport($this->startDate, $this->endDate);
    }

    public function getProductReport()
    {
        return generateProductReport($this->startDate, $this->endDate);
    }

    public function getTotalOrders($salesReport)
    {
        $total = 0;

        foreach ($salesReport as $day) {
            $total += $day['order_count'];
        }

        return $total;
    }

    public function getTotalRevenue($salesReport)
    {
        $total = 0;

        foreach ($salesReport as $day) {
            $total += $day['total_sales'];
        }

        return $total;
    }

    public function getTotalItems($salesReport)
    {
        $total = 0;

        foreach ($salesReport as $day) {
            $total += $day['items_sold'];
        }

        return $total;
    }

}

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date   = $_GET['end_date'] ?? date('Y-m-d');

// Membuat object Report
$report = new Report(
    $start_date, 
    $end_date
    );

// Mengambil data laporan
$sales_report   = $report->getSalesReport();
$product_report = $report->getProductReport();

// Menghitung total menggunakan method class
$total_orders  = $report->getTotalOrders($sales_report);
$total_revenue = $report->getTotalRevenue($sales_report);
$total_items   = $report->getTotalItems($sales_report);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include 'includes/nav_admin.php'; ?> <!-- admin navbar -->

<main>
    <section class="section">
        <div class="container">
            <h2 class="section-title">Laporan Penjualan</h2>

            <div class="card" style="margin-bottom:24px">
                <form method="GET" action="" class="filter-form">
                    <div class="form-group">
                        <label>Tanggal Mulai</label>
                        <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                    </div>
                    <div class="form-group">
                        <label>Tanggal Selesai</label>
                        <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
                    </div>
                    <div class="form-group form-group--action">
                        <button type="submit" class="btn btn-primary">Tampilkan</button>
                    </div>
                    <!-- Tampilkan Laporan Bulan Ini, start date tgl 1 - end date hari ini -->
                    <div class="form-group form-group--action">
                        <a href="reports.php?start_date=<?= date('Y-m-01') ?>&end_date=<?= date('Y-m-d') ?>" class="btn btn-secondary">Bulan Ini</a>
                    </div>
                </form>
            </div>

            <div class="stats-grid stats-grid--3" style="margin-bottom:24px">
                <div class="stat-card stat-card--blue">
                    <p class="stat-label">Total Pesanan Selesai</p>
                    <p class="stat-value"><?= $total_orders ?></p>
                </div>
                <div class="stat-card stat-card--green">
                    <p class="stat-label">Total Pendapatan</p>
                    <p class="stat-value stat-value--sm">Rp <?= number_format($total_revenue, 0, ',', '.') ?></p>
                </div>
                <div class="stat-card stat-card--teal">
                    <p class="stat-label">Item Terjual</p>
                    <p class="stat-value"><?= $total_items ?></p>
                </div>
            </div>

            <!-- Laporan Harian -->
            <div class="card" style="margin-bottom:24px">
                <div class="card-header">
                    <h3>Laporan Harian</h3>
                    <span class="text-muted" style="font-size:0.8rem">
                        <?= date('d M Y', strtotime($start_date)) ?> — <?= date('d M Y', strtotime($end_date)) ?>
                    </span>
                </div>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Pesanan Selesai</th>
                                <th>Item Terjual</th>
                                <th>Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($sales_report)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        Tidak ada data penjualan untuk periode ini.
                                        <?php if ($start_date == date('Y-m-01') && $end_date == date('Y-m-d')): ?>
                                            <br><small>Pastikan ada pesanan dengan status <strong>"completed"</strong>.</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($sales_report as $day): ?>
                                    <tr>
                                        <td><?= date('d M Y', strtotime($day['date'])) ?></td>
                                        <td><strong><?= $day['order_count'] ?></strong></td>
                                        <td><?= $day['items_sold'] ?></td>
                                        <td><strong>Rp <?= number_format($day['total_sales'], 0, ',', '.') ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($sales_report)): ?>
                        <tfoot>
                            <tr class="total-row">
                                <td><strong>TOTAL</strong></td>
                                <td><strong><?= $total_orders ?></strong></td>
                                <td><strong><?= $total_items ?></strong></td>
                                <td><strong>Rp <?= number_format($total_revenue, 0, ',', '.') ?></strong></td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <!-- Performa Produk Terlaris -->
            <div class="card">
                <div class="card-header">
                    <h3>Performa Produk Terlaris</h3>
                    <span class="text-muted" style="font-size:0.8rem">Berdasarkan pendapatan</span>
                </div>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Produk</th>
                                <th>Jumlah Pesanan</th>
                                <th>Qty Terjual</th>
                                <th>Pendapatan</th>
                            </tr>
                        </thead>

                        <!-- Data produk terlaris -->
                        <tbody>
                            <?php if (empty($product_report)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        Tidak ada data produk untuk periode ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $rank = 1;
                                foreach ($product_report as $p): 
                                ?>
                                    <tr>
                                        <td><strong>#<?= $rank++ ?></strong></td>
                                        <td><?= htmlspecialchars($p['name']) ?></td>
                                        <td><?= $p['order_count'] ?></td>
                                        <td><?= $p['total_quantity'] ?></td>
                                        <td><strong>Rp <?= number_format($p['total_revenue'], 0, ',', '.') ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($product_report)): ?>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="4" style="text-align:right"><strong>TOTAL</strong></td>
                                <td><strong>Rp <?= number_format($total_revenue, 0, ',', '.') ?></strong></td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?> <!-- footer -->
</body>
</html>