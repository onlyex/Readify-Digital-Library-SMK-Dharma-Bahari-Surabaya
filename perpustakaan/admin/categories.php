<?php
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
	header('Location: ../index.php');
	exit;
}

$page_title = 'Kelola Kategori';

// Ensure categories table has required columns (description, thumb_path)
$cols = [];
$colRes = $mysqli->query("SHOW COLUMNS FROM categories");
if($colRes){
	while($c = $colRes->fetch_assoc()) $cols[] = $c['Field'];
}
$alterErrors = '';
$toAlter = [];
if(!in_array('description', $cols)) $toAlter[] = "ADD COLUMN description TEXT NULL";
if(!in_array('thumb_path', $cols)) $toAlter[] = "ADD COLUMN thumb_path VARCHAR(255) NULL";
if(!empty($toAlter)){
	$sql = "ALTER TABLE categories " . implode(', ', $toAlter);
	if(!$mysqli->query($sql)){
		$alterErrors = $mysqli->error;
	}
}

// Handle create
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = isset($_POST['name']) ? trim($_POST['name']) : '';
	$description = isset($_POST['description']) ? trim($_POST['description']) : '';
	$error = '';

	// handle thumbnail upload (optional)
	$thumb_rel = '';
	if (!empty($_FILES['thumb']['tmp_name'])) {
		$uploadDir = __DIR__ . '/../uploads/categories/';
		if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
		$ext = pathinfo($_FILES['thumb']['name'], PATHINFO_EXTENSION);
		$fn = 'uploads/categories/' . time() . '_' . rand(1000, 9999) . '.' . $ext;
		if (move_uploaded_file($_FILES['thumb']['tmp_name'], __DIR__ . '/../' . $fn)) {
			$thumb_rel = ltrim($fn, '/');
		} else {
			$error = 'Gagal mengunggah thumbnail.';
		}
	}

	if ($name !== '' && $error === '') {
		// Use prepared statement to insert name & description
		$stmt = $mysqli->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
		if ($stmt) {
			$stmt->bind_param('ss', $name, $description);
			if ($stmt->execute()) {
				$cid = $mysqli->insert_id;
				// if we have uploaded thumb, update the record
				if ($thumb_rel) {
					$up = $mysqli->prepare('UPDATE categories SET thumb_path = ? WHERE id = ?');
					if ($up) {
						$up->bind_param('si', $thumb_rel, $cid);
						$up->execute();
						$up->close();
					}
				}
				$stmt->close();
				// redirect to avoid resubmission and show new category
				header('Location: categories.php?ok=1');
				exit;
			} else {
				$error = 'Gagal menyimpan kategori: ' . $stmt->error;
				$stmt->close();
			}
		} else {
			$error = 'Gagal menyiapkan query: ' . $mysqli->error;
		}
	} elseif ($name === '') {
		$error = 'Nama kategori wajib diisi.';
	}
}

// Handle delete
if (isset($_GET['delete'])) {
	$id = intval($_GET['delete']);
	$mysqli->query("DELETE FROM categories WHERE id=$id");
	header('Location: categories.php');
	exit;
}

// load categories for display
$cats = $mysqli->query('SELECT * FROM categories ORDER BY name')->fetch_all(MYSQLI_ASSOC);

// include header after POST handling to allow redirects
include '../inc/header.php';

?>
<main class="container-fluid admin">
	<h1>Kelola Kategori</h1>

	<?php if(!empty($alterErrors)): ?>
		<div class="panel" style="background:#fff4e6;border:1px solid #ffd5a5;color:#6a3b00;margin-bottom:8px;padding:10px;border-radius:8px">Notice: Gagal menambahkan kolom otomatis: <?php echo htmlspecialchars($alterErrors); ?></div>
	<?php endif; ?>

	<?php if(isset($_GET['ok'])): ?>
		<div class="panel"><strong>Sukses:</strong> Kategori berhasil ditambahkan.</div>
	<?php endif; ?>

	<?php if(!empty($error)): ?>
		<div class="panel" style="background:#ffecec;border:1px solid #f5c2c2;color:#7a1d1d;margin-bottom:8px;padding:10px;border-radius:8px">Error: <?php echo htmlspecialchars($error); ?></div>
	<?php endif; ?>

	<div class="panel">
		<form method="post" enctype="multipart/form-data" class="admin-form" style="max-width:900px">
			<input type="hidden" name="id" id="id">

			<label>Category Name
				<input class="form-control" name="name" id="name" placeholder="Nama kategori" required>
			</label>

			<label>Description
				<textarea class="form-control" name="description" id="description" placeholder="Deskripsi singkat kategori"></textarea>
			</label>

			<label>Thumbnail (opsional)
				<input type="file" class="form-control" name="thumb" accept="image/*">
			</label>

			<div style="display:flex;gap:10px;margin-top:8px">
				<button class="btn btn-primary" type="submit">Tambah Kategori</button>
				<button type="reset" class="btn btn-ghost">Reset</button>
			</div>
		</form>
	</div>

	<h2 style="margin-top:18px">Existing Categories</h2>
	<ul style="padding-left:18px;color:var(--text);">
		<?php foreach ($cats as $c) : ?>
			<li style="margin-bottom:8px">
				<?php if (!empty($c['thumb_path'])): ?>
					<img src="/<?php echo htmlspecialchars($c['thumb_path']); ?>" alt="" style="width:36px;height:46px;object-fit:cover;border-radius:4px;margin-right:8px;vertical-align:middle">
				<?php endif; ?>
				<?php echo esc($c['name']); ?> <a href="?delete=<?php echo $c['id']; ?>" class="danger">Delete</a>
			</li>
		<?php endforeach; ?>
	</ul>
</main>

<?php include '../inc/footer.php'; ?>