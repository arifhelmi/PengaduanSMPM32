<?php
    // database
    require_once("database.php");
    require_once("auth.php"); // Session
    logged_admin ();
    // global var
    global $nomor, $foundreply;
    // hapus Balasan laporan berdasarkan id Balasan laporan


    if (isset($_POST['Hapus'])) {
        $id_laporan = isset($_POST['id_laporan']) ? intval($_POST['id_laporan']) : 0;

        if ($id_laporan > 0) {
            // Query delete hanya untuk laporan dengan id tertentu
            $query = "DELETE FROM laporan WHERE id_laporan = :id_laporan";
    
            // Gunakan prepared statement untuk keamanan
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':id_laporan', $id_laporan, PDO::PARAM_INT);
            if ($stmt->execute()) {
                echo "Laporan berhasil dihapus.";
            } else {
                echo "Gagal menghapus laporan.";
            }
        } else {
            echo "ID laporan tidak valid.";
        }
    }
    
    // Balas laporan
    if (isset($_POST['Balas'])) {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id_laporan1 = isset($_POST['id_laporan1']) ? intval($_POST['id_laporan1']) : 0;
                $isi_tanggapan = $_POST['isi_tanggapan'];
                $cerita_real_krb = $_POST['cerita_real_krb'];
                $kasus_penanganan = $_POST['kasus_penanganan'];
                $nama_pendamping = $_POST['nama_pendamping'];
                $admin_value = 'Admin'; // Create a variable for the admin value
    
                // Format ID Tanggapan
                $sql = "SELECT MAX(CAST(SUBSTRING_INDEX(id_tanggapan, '-', -1) AS UNSIGNED)) AS last_order 
                        FROM tanggapan";
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
                // Tentukan nomor urut berikutnya
                $last_order = $row['last_order'] ? $row['last_order'] + 1 : 1;
    
                // Format ID Tanggapan
                $tanggal = date('Ymd'); // Tanggal saat ini
                $id_tanggapan = "IDT-" . $tanggal . "-" . str_pad($last_order, 6, "0", STR_PAD_LEFT);
    
                // Insert data ke tabel tanggapan
                $sql = "INSERT INTO tanggapan 
                        (id_tanggapan, id_laporan, admin, isi_tanggapan, tanggal_tanggapan, 
                         cerita_real_krb, kasus_penanganan, nama_pendamping) 
                        VALUES 
                        (:id_tanggapan, :id_laporan, :admin, :isi_tanggapan, CURRENT_TIMESTAMP,
                         :cerita_real_krb, :kasus_penanganan, :nama_pendamping)";
                
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':id_tanggapan', $id_tanggapan, PDO::PARAM_STR);
                $stmt->bindParam(':id_laporan', $id_laporan1, PDO::PARAM_INT);
                $stmt->bindParam(':admin', $admin_value, PDO::PARAM_STR); // Use the variable instead of literal
                $stmt->bindParam(':isi_tanggapan', $isi_tanggapan, PDO::PARAM_STR);
                $stmt->bindParam(':cerita_real_krb', $cerita_real_krb, PDO::PARAM_STR);
                $stmt->bindParam(':kasus_penanganan', $kasus_penanganan, PDO::PARAM_STR);
                $stmt->bindParam(':nama_pendamping', $nama_pendamping, PDO::PARAM_STR);
    
                if ($stmt->execute()) {
                    // Update status laporan menjadi 'Ditanggapi'
                    $update_sql = "UPDATE laporan SET status = 'Ditanggapi' WHERE id_laporan = :id_laporan";
                    $update_stmt = $db->prepare($update_sql);
                    $update_stmt->bindParam(':id_laporan', $id_laporan1, PDO::PARAM_INT);
                    $update_stmt->execute();
                    
                    echo "<script>alert('Tanggapan berhasil ditambahkan.');</script>";
                } else {
                    echo "<script>alert('Gagal menambahkan tanggapan.');</script>";
                }
            }
        } catch (PDOException $e) {
            echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
        }
    }
    
    // Penanganan laporan
    if (isset($_POST['submit_penanganan'])) {
        try {
            // Ambil data dari form
            $id_laporan2 = isset($_POST['id_laporan2']) ? intval($_POST['id_laporan2']) : 0;
            $jenis_penanganan = $_POST['jenis_penanganan'];
            $tanggal_penanganan = $_POST['tanggal_penanganan'];
            $alamat_penanganan = $_POST['alamat_penanganan'];
            $nama_pendamping = $_POST['nama_pendamping'];
            $nomor_hp_pendamping = $_POST['nomor_hp_pendamping'];
    
            // Format tanggal saat ini untuk ID penanganan
            $tanggal = date('Ymd');
    
            // Ambil nomor urut terakhir
            $sql = "SELECT MAX(CAST(SUBSTRING_INDEX(id_penanganan, '-', -1) AS UNSIGNED)) AS last_order 
                    FROM penanganan 
                    WHERE id_penanganan LIKE :id_pattern";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id_pattern', 'IDP-' . $tanggal . '%', PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $last_order = $row['last_order'] ? $row['last_order'] + 1 : 1;
    
            // Format ID penanganan
            $id_penanganan = 'IDP-' . $tanggal . '-' . str_pad($last_order, 6, '0', STR_PAD_LEFT);
    
            // Insert data ke tabel penanganan
            $sql = "INSERT INTO penanganan 
                    (id_penanganan, id_laporan, jenis_penanganan, tanggal_penanganan,
                     alamat_penanganan, nama_pendamping, nomor_hp_pendamping) 
                    VALUES 
                    (:id_penanganan, :id_laporan, :jenis_penanganan, :tanggal_penanganan,
                     :alamat_penanganan, :nama_pendamping, :nomor_hp_pendamping)";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id_penanganan', $id_penanganan, PDO::PARAM_STR);
            $stmt->bindParam(':id_laporan', $id_laporan2, PDO::PARAM_INT);
            $stmt->bindParam(':jenis_penanganan', $jenis_penanganan, PDO::PARAM_STR);
            $stmt->bindParam(':tanggal_penanganan', $tanggal_penanganan, PDO::PARAM_STR);
            $stmt->bindParam(':alamat_penanganan', $alamat_penanganan, PDO::PARAM_STR);
            $stmt->bindParam(':nama_pendamping', $nama_pendamping, PDO::PARAM_STR);
            $stmt->bindParam(':nomor_hp_pendamping', $nomor_hp_pendamping, PDO::PARAM_STR);
    
            if ($stmt->execute()) {
                // Update status laporan menjadi 'Ditangani'
                $update_sql = "UPDATE laporan SET status = 'Ditangani' WHERE id_laporan = :id_laporan";
                $update_stmt = $db->prepare($update_sql);
                $update_stmt->bindParam(':id_laporan', $id_laporan2, PDO::PARAM_INT);
                $update_stmt->execute();
                
                echo "<script>alert('Penanganan berhasil ditambahkan.');</script>";
            } else {
                echo "<script>alert('Gagal menambahkan penanganan.');</script>";
            }
        } catch (PDOException $e) {
            echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
        }
    }
    
  
?>    
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="images/favicon.ico">
    <title>Table </title>
    <!-- Bootstrap core CSS-->
    <link href="vendor/bootstrap/css/bootstrap.css" rel="stylesheet">
    <!-- Custom fonts for this template-->
    <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <!-- Page level plugin CSS-->
    <link href="vendor/datatables/dataTables.bootstrap4.css" rel="stylesheet">
    <!-- Custom styles for this template-->
    <link href="css/admin.css" rel="stylesheet">
</head>

<body class="fixed-nav sticky-footer" id="page-top">
    <!-- Navigation-->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top" id="mainNav">
        <a class="navbar-brand" href="index">SMP MUHAMMADIYAH 32 Jakarta</a>
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav navbar-sidenav sidebar-menu" id="exampleAccordion">

                <li class="sidebar-profile nav-item" data-toggle="tooltip" data-placement="right" title="Admin">
                    <div class="profile-main">
                        <p class="image">
                            <img alt="image" src="images/avatar1.png" width="80">
                            <span class="status"><i class="fa fa-circle text-success"></i></span>
                        </p>
                        <p>
                            <span class="">Admin</span><br>
                            <span class="user" style="font-family: monospace;"><?php echo $divisi; ?></span>
                        </p>
                    </div>
                </li>

                <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Dashboard">
                    <a class="nav-link" href="index">
                        <i class="fa fa-fw fa-dashboard"></i>
                        <span class="nav-link-text">Dashboard</span>
                    </a>
                </li>

                <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Export">
                    <a class="nav-link" href="export">
                        <i class="fa fa-fw fa-print"></i>
                        <span class="nav-link-text">Ekspor</span>
                    </a>
                </li>

                <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Version">
                    <a class="nav-link" href="#VersionModal" data-toggle="modal" data-target="#VersionModal">
                        <i class="fa fa-fw fa-code"></i>
                        <span class="nav-link-text">v-6.0</span>
                    </a>
                </li>

            </ul>

            <ul class="navbar-nav sidenav-toggler">
                <li class="nav-item">
                    <a class="nav-link text-center" id="sidenavToggler">
                        <i class="fa fa-fw fa-angle-left"></i>
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle mr-lg-2" id="messagesDropdown" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-fw fa-envelope"></i>
                        <span class="d-lg-none">Messages
                            <span class="badge badge-pill badge-primary">12 New</span>
                        </span>
                        <span class="indicator text-primary d-none d-lg-block">
                            <i class="fa fa-fw fa-circle"></i>
                        </span>
                    </a>
                    <?php
                        $statement = $db->query("SELECT * FROM laporan ORDER BY laporan.id_laporan DESC LIMIT 1");
                        foreach ($statement as $key ) {
                            $mysqldate = $key['tanggal_pengaduan'];
                            $phpdate = strtotime($mysqldate);
                            $tanggal = date( 'd/m/Y', $phpdate);
                     ?>
                    <div class="dropdown-menu" aria-labelledby="messagesDropdown">
                        <h6 class="dropdown-header">Laporan Baru :</h6>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">
                            <strong><?php echo $key['nama_plp']; ?></strong>
                            <span class="small float-right text-muted"><?php echo $key['tanggal_pengaduan']; ?></span>
                            <div class="dropdown-message small"><?php echo $key['kronologi_kejadian']; ?></div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <!-- <a class="dropdown-item small" href="#">View all messages</a> -->
                    </div>
                    <?php
                        }
                     ?>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="modal" data-target="#exampleModal">
                        <i class="fa fa-fw fa-sign-out"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </nav>


    <!-- Body -->
    <div class="content-wrapper">
        <div class="container-fluid">

            <!-- Breadcrumbs-->
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="#">Kelola</a>
                </li>
                <li class="breadcrumb-item active"><?php echo $divisi; ?></li>
            </ol>

            <!-- DataTables Card-->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="fa fa-table"></i> Laporan Masuk
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Pelapor</th>
                            <th>No HP Pelapor</th>
                            <th>Kelas Pelapor</th>
                            <th>Nama Korban</th>
                            <th>No HP Korban</th>
                            <th>Kelas Korban</th>
                            <th>Nama Pelaku</th>
                            <th>No HP Pelaku</th>
                            <th>Tanggal Pengaduan</th>
                            <th>Tanggal Kejadian</th>
                            <th>Tempat Kejadian</th>
                            <th>Kategori Kekerasan</th>
                            <th>Subjek Pengaduan</th>
                            <th>Kronologi Kejadian</th>
                            <th>Bukti Kekerasan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>

<?php
// Ambil semua record dari tabel laporan

    
        $statement = $db->query("SELECT * FROM `laporan` ORDER BY id_laporan DESC");
        foreach ($statement as $key) {
        $mysqldate = $key['tanggal_pengaduan'];
        $phpdate = strtotime($mysqldate);
        $tanggal = date( 'd/m/Y', $phpdate);
        $status  = $key['status'];
        if($status == "Ditanggapi") {
            $style_status = "<p style=\"background-color:#009688;color:#fff;padding-left:2px;padding-right:2px;padding-bottom:2px;margin-top:16px;font-size:15px;font-style:italic;\">Ditanggapi</p>";
        } else {
            $style_status = "<p style=\"background-color:#FF9800;color:#fff;padding-left:2px;padding-right:2px;padding-bottom:2px;margin-top:16px;font-size:15px;font-style:italic;\">Menunggu</p>";
        }
    }
?>
                                <tr>
                                <td><?php echo $key['id_laporan']; ?></td>
                                <td><?php echo $key['nama_plp']; ?></td>
                                <td><?php echo $key['no_hp_plp']; ?></td>
                                <td><?php echo $key['kls_plp']; ?></td>
                                <td><?php echo $key['nama_krb']; ?></td>
                                <td><?php echo $key['no_hp_krb']; ?></td>
                                <td><?php echo $key['kls_krb']; ?></td>
                                <td><?php echo $key['nama_plk']; ?></td>
                                <td><?php echo $key['no_hp_plk']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($key['tanggal_pengaduan'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($key['tanggal_kejadian'])); ?></td>
                                <td><?php echo $key['tempat_kejadian']; ?></td>
                                <td><?php echo $key['kategori_kekerasan']; ?></td>
                                <td><?php echo $key['subjek_pengaduan']; ?></td>
                                <td><?php echo $key['kronologi_kejadian']; ?></td>
                                <td><?php echo $key['bukti_kekerasan']; ?></td>
                                <td><?php echo $key['status']; ?></td>
            <td>
                <button 
                    class="btn btn-primary btn-sm" 
                    data-bs-toggle="modal" 
                    data-bs-target="#ModalBalas<?php echo $key['id_laporan']; ?>">
                    Validasi
                </button>
                <button 
                    class="btn btn-success btn-sm" 
                    data-bs-toggle="modal" 
                    data-bs-target="#ModalPenanganan<?php echo $key['id_laporan']; ?>">
                    Penanganan
                </button>
                <button 
                    class="btn btn-danger btn-sm" 
                    data-bs-toggle="modal" 
                    data-bs-target="#ModalHapus<?php echo $key['id_laporan']; ?>">
                    Hapus
                </button>
            </td>
        </tr>

        <div class="modal fade" id="ModalBalas<?php echo $key['id_laporan']; ?>" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Validasi Laporan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">    
                <form method="post">
                    <div class="form-group">
                        <p><b>Nama Pelapor:</b></p>
                        <?php echo htmlspecialchars($key['nama_plp']); ?>
                        <hr>
                    </div>
                    <div class="form-group">
                        <p><b>Isi Laporan:</b></p>
                        <p>"<?php echo htmlspecialchars($key['kronologi_kejadian']); ?>"</p>
                        <hr>
                    </div>
                    <input type="hidden" name="id_laporan1" value="<?php echo htmlspecialchars($key['id_laporan']); ?>">
                    <div class="form-group">
                        <p><b>1. Apakah benar pelapor membuat laporan berdasarkan cerita korban?</b></p>
                        <textarea class="form-control" name="isi_tanggapan" required></textarea>
                    </div>
                    <div class="form-group">
                        <p><b>2. Ceritakan kronologis kejadian yang dialami korban</b></p>
                        <textarea class="form-control" name="cerita_real_krb" required></textarea>
                    </div>
                    <div class="form-group">
                        <p><b>3. Apakah Pelapor ingin melanjutkan kasusnya?</b></p>
                        <textarea class="form-control" name="kasus_penanganan" required></textarea>
                    </div>
                    <div class="form-group">
                        <p><b>4. Nama Pendamping</b></p>
                        <input type="text" class="form-control" name="nama_pendamping" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" name="Balas">Validasi</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="modalDetail" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetailLabel">Detail Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="detailContent">
                    <!-- Detail laporan akan ditampilkan di sini -->
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal Penanganan -->
<!-- Modal Penanganan -->
<div class="modal fade" id="ModalPenanganan<?php echo $key['id_laporan']; ?>" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Penanganan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="id_laporan2" value="<?php echo htmlspecialchars($key['id_laporan']); ?>">
                    
                    <div class="form-group">
                        <label for="jenis_penanganan"><b>Jenis Penanganan:</b></label>
                        <textarea class="form-control" name="jenis_penanganan" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="tanggal_penanganan"><b>Tanggal Penanganan:</b></label>
                        <input type="date" class="form-control" name="tanggal_penanganan" required>
                    </div>

                    <div class="form-group">
                        <label for="alamat_penanganan"><b>Alamat Penanganan:</b></label>
                        <textarea class="form-control" name="alamat_penanganan" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="nama_pendamping"><b>Nama Pendamping:</b></label>
                        <input type="text" class="form-control" name="nama_pendamping" required>
                    </div>

                    <div class="form-group">
                        <label for="nomor_hp_pendamping"><b>Nomor HP Pendamping:</b></label>
                        <input type="text" class="form-control" name="nomor_hp_pendamping" required>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" name="submit_penanganan">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal Hapus -->
<div class="modal fade" id="ModalHapus<?php echo $key['id_laporan']; ?>" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-sm " role="document">
                <div class="modal-content">
                    <div class="modal-header ">
                        <h5 class="modal-title text-center">Hapus Laporan</h5>
                    </div>
                    <div class="modal-body">
                        <p class="text-center">Hapus Pengaduan</p>
                        <p class="text-center">Dari <b><?php echo $key['nama_plp']; ?></b> ?</p>
                    </div>
                    <div class="modal-footer">
                        <form method="post">
                            <input type="hidden" name="id_laporan2" value="<?php echo $key['id_laporan']; ?>">
                            <input type="submit" class="btn btn-danger btn-sm card-shadow-2" name="Hapus" value="Hapus">
                            <button type="button" class="btn btn-close btn-sm card-shadow-2" data-dismiss="modal">Batal</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
       
    </tbody>
</table>
        <?php
            
        ?>
        
       
        <!-- Scroll to Top Button-->
        <a class="scroll-to-top rounded" href="#page-top">
            <i class="fa fa-angle-up"></i>
        </a>

        <!-- Logout Modal-->
        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Yakin Ingin Keluar?</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">Pilih "Logout" jika anda ingin mengakhiri sesi.</div>
                    <div class="modal-footer">
                        <button class="btn btn-close card-shadow-2 btn-sm" type="button" data-dismiss="modal">Batal</button>
                        <a class="btn btn-primary btn-sm card-shadow-2" href="logout">Logout</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Version Info Modal -->
        <!-- Modal -->
        <div class="modal fade" id="VersionModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Admin Versi</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h5 style="text-align : center;">V-6.0</h5>
                        <p style="text-align : center;">Copyright © SMP MUHAMMADIYAH 32 Jakarta</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-close card-shadow-2 btn-sm" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap core JavaScript-->
        <script src="vendor/jquery/jquery.min.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <!-- Core plugin JavaScript-->
        <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
        <!-- Page level plugin JavaScript-->
        <script src="vendor/datatables/jquery.dataTables.js"></script>
        <script src="vendor/datatables/dataTables.bootstrap4.js"></script>
        <!-- Custom scripts for all pages-->
        <script src="js/admin.js"></script>
        <!-- Custom scripts for this page-->
        <script src="js/admin-datatables.js"></script>
        </div>

        <!-- Bootstrap core JavaScript-->
        <script src="vendor/jquery/jquery.min.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <!-- Core plugin JavaScript-->
        <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
        <!-- Page level plugin JavaScript-->
        <script src="vendor/datatables/jquery.dataTables.js"></script>
        <script src="vendor/datatables/dataTables.bootstrap4.js"></script>
        <!-- Custom scripts for all pages-->
        <script src="js/admin.js"></script>
        <!-- Custom scripts for this page-->
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script src="js/admin-datatables.js"></script>

    </div>
    <!-- /.content-wrapper-->
</body>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
</html>
<script>
$(document).ready(function() {
    // Event handler untuk semua modal
    $('.modal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var id = button.data('id');
        
        // Reset form jika ada
        if(modal.find('form').length > 0) {
            modal.find('form')[0].reset();
        }
    });
});
document.addEventListener("DOMContentLoaded", function () {
    const modalButtons = document.querySelectorAll("[data-bs-toggle='modal']");
    modalButtons.forEach(button => {
        button.addEventListener("click", function () {
            const targetModal = document.querySelector(this.getAttribute("data-bs-target"));
            if (targetModal) {
                const modal = new bootstrap.Modal(targetModal);
                modal.show();
            }
        });
    });
});
</script>
