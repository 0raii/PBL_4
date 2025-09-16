<?php
include 'config.php';

// Cek apakah user sudah login dan role-nya admin
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Ambil data user
$user_id = $_SESSION['id'];
$query = "SELECT * FROM mahasiswa WHERE id=$user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Ambil data peminjaman pending
$query_pending = "SELECT p.*, m.namaLengkap, m.nim, r.namaRuangan, u.namaUnit 
                  FROM peminjaman p 
                  JOIN mahasiswa m ON p.idMahasiswa = m.id 
                  LEFT JOIN ruangan r ON p.idRuangan = r.id 
                  LEFT JOIN unit u ON p.idUnit = u.id 
                  WHERE p.status = 'pending' 
                  ORDER BY p.created_at DESC";
$peminjaman_pending = mysqli_query($conn, $query_pending);

// Ambil data peminjaman disetujui
$query_disetujui = "SELECT p.*, m.namaLengkap, m.nim, r.namaRuangan, u.namaUnit 
                  FROM peminjaman p 
                  JOIN mahasiswa m ON p.idMahasiswa = m.id 
                  LEFT JOIN ruangan r ON p.idRuangan = r.id 
                  LEFT JOIN unit u ON p.idUnit = u.id 
                  WHERE p.status = 'disetujui' 
                  ORDER BY p.created_at DESC";
$peminjaman_disetujui = mysqli_query($conn, $query_disetujui);

// Ambil data peminjaman selesai
$query_selesai = "SELECT p.*, m.namaLengkap, m.nim, r.namaRuangan, u.namaUnit 
                 FROM peminjaman p 
                 JOIN mahasiswa m ON p.idMahasiswa = m.id 
                 LEFT JOIN ruangan r ON p.idRuangan = r.id 
                 LEFT JOIN unit u ON p.idUnit = u.id 
                 WHERE p.status = 'selesai' 
                 ORDER BY p.created_at DESC LIMIT 5";
$peminjaman_selesai = mysqli_query($conn, $query_selesai);

// Statistik
$query_stats = "SELECT 
                 (SELECT COUNT(*) FROM peminjaman WHERE status = 'pending') as total_pending,
                 (SELECT COUNT(*) FROM peminjaman WHERE status = 'disetujui') as total_disetujui,
                 (SELECT COUNT(*) FROM peminjaman WHERE status = 'selesai') as total_selesai,
                 (SELECT COUNT(*) FROM peminjaman WHERE status = 'ditolak') as total_ditolak";
$stats = mysqli_query($conn, $query_stats);
$stats_data = mysqli_fetch_assoc($stats);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Peminjaman Sarpras</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Sarpras TI</h2>
            </div>
            
            <div class="sidebar-user">
                <div class="user-avatar">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="user-info">
                    <h3><?php echo $user['namaLengkap']; ?></h3>
                    <p>Administrator</p>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li class="active"><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="approval.php"><i class="fas fa-check-circle"></i> Approval Peminjaman</a></li>
                <li><a href="ruangan.php"><i class="fas fa-building"></i> Manajemen Ruangan</a></li>
                <li><a href="unit.php"><i class="fas fa-tools"></i> Manajemen Unit</a></li>
                <li><a href="mahasiswa.php"><i class="fas fa-users"></i> Manajemen User</a></li>
                <li><a href="laporan.php"><i class="fas fa-chart-bar"></i> Laporan</a></li>
                <li><a href="process/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Dashboard Admin</h1>
                <div class="header-info">
                    <span><?php echo date('d F Y'); ?></span>
                </div>
            </div>
            
            <!-- Cards -->
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="card-content">
                        <h3>Menunggu Persetujuan</h3>
                        <p class="card-value"><?php echo $stats_data['total_pending']; ?></p>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="card-content">
                        <h3>Disetujui</h3>
                        <p class="card-value"><?php echo $stats_data['total_disetujui']; ?></p>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-flag-checkered"></i>
                    </div>
                    <div class="card-content">
                        <h3>Selesai</h3>
                        <p class="card-value"><?php echo $stats_data['total_selesai']; ?></p>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-icon bg-danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="card-content">
                        <h3>Ditolak</h3>
                        <p class="card-value"><?php echo $stats_data['total_ditolak']; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Peminjaman Pending -->
            <div class="recent-peminjaman">
                <div class="section-header">
                    <h2>Peminjaman Menunggu Persetujuan</h2>
                    <a href="approval.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                </div>
                
                <?php if (mysqli_num_rows($peminjaman_pending) > 0): ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Peminjam</th>
                                    <th>Tanggal</th>
                                    <th>Jam</th>
                                    <th>Ruangan/Unit</th>
                                    <th>Keperluan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($p = mysqli_fetch_assoc($peminjaman_pending)): ?>
                                    <tr>
                                        <td>#<?php echo $p['id']; ?></td>
                                        <td><?php echo $p['namaLengkap']; ?> (<?php echo $p['nim']; ?>)</td>
                                        <td><?php echo date('d/m/Y', strtotime($p['tanggalPinjam'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($p['jamMulai'])) . ' - ' . date('H:i', strtotime($p['jamSelesai'])); ?></td>
                                        <td>
                                            <?php 
                                            if ($p['idRuangan']) {
                                                echo $p['namaRuangan'];
                                            } elseif ($p['idUnit']) {
                                                echo $p['namaUnit'];
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo substr($p['keperluan'], 0, 30) . '...'; ?></td>
                                        <td>
                                            <a href="approval.php?action=approve&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-success">Setujui</a>
                                            <a href="approval.php?action=reject&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger">Tolak</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <p>Tidak ada peminjaman yang menunggu persetujuan</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Peminjaman Disetujui -->
            <div class="recent-peminjaman">
                <div class="section-header">
                    <h2>Peminjaman Disetujui</h2>
                    <a href="peminjaman_disetujui.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                </div>
                
                <?php if (mysqli_num_rows($peminjaman_disetujui) > 0): ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Peminjam</th>
                                    <th>Tanggal</th>
                                    <th>Jam</th>
                                    <th>Ruangan/Unit</th>
                                    <th>Keperluan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($p = mysqli_fetch_assoc($peminjaman_disetujui)): ?>
                                    <tr>
                                        <td>#<?php echo $p['id']; ?></td>
                                        <td><?php echo $p['namaLengkap']; ?> (<?php echo $p['nim']; ?>)</td>
                                        <td><?php echo date('d/m/Y', strtotime($p['tanggalPinjam'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($p['jamMulai'])) . ' - ' . date('H:i', strtotime($p['jamSelesai'])); ?></td>
                                        <td>
                                            <?php 
                                            if ($p['idRuangan']) {
                                                echo $p['namaRuangan'];
                                            } elseif ($p['idUnit']) {
                                                echo $p['namaUnit'];
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo substr($p['keperluan'], 0, 30) . '...'; ?></td>
                                        <td>
                                            <a href="javascript:void(0)" onclick="selesaikanPeminjaman(<?php echo $p['id']; ?>)" class="btn btn-sm btn-primary">Selesaikan</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list"></i>
                        <p>Belum ada peminjaman yang disetujui</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Peminjaman Selesai -->
            <div class="recent-peminjaman">
                <div class="section-header">
                    <h2>Peminjaman Selesai Terkini</h2>
                    <a href="peminjaman_selesai.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                </div>
                
                <?php if (mysqli_num_rows($peminjaman_selesai) > 0): ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Peminjam</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Ruangan/Unit</th>
                                    <th>Keperluan</th>
                                    <th>Tanggal Selesai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($p = mysqli_fetch_assoc($peminjaman_selesai)): ?>
                                    <tr>
                                        <td>#<?php echo $p['id']; ?></td>
                                        <td><?php echo $p['namaLengkap']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($p['tanggalPinjam'])); ?></td>
                                        <td>
                                            <?php 
                                            if ($p['idRuangan']) {
                                                echo $p['namaRuangan'];
                                            } elseif ($p['idUnit']) {
                                                echo $p['namaUnit'];
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo substr($p['keperluan'], 0, 30) . '...'; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($p['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <p>Belum ada peminjaman yang selesai</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="assets/js/script.js"></script>
    <script>
        function selesaikanPeminjaman(id) {
            if (confirm('Apakah Anda yakin ingin menyelesaikan peminjaman ini?')) {
                window.location.href = 'process/update_status.php?id=' + id + '&status=selesai';
            }
        }
    </script>
</body>
</html>