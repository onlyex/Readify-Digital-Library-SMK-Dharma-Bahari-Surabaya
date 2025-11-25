<?php
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$page_title = 'Tambah / Edit Buku';

$msg = '';

// ========================
// HANDLE CREATE / UPDATE
// ========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = !empty($_POST['id']) ? intval($_POST['id']) : 0;
    $title = $mysqli->real_escape_string($_POST['title']);
    $author = $mysqli->real_escape_string($_POST['author']);
    $publisher = $mysqli->real_escape_string($_POST['publisher']);
    $year = intval($_POST['publication_year']);
    $isbn = $mysqli->real_escape_string($_POST['isbn']);
    $stock = intval($_POST['stock']);
    $desc = $mysqli->real_escape_string($_POST['description']);

    // UPLOAD COVER
    $cover_path = '';
    if (!empty($_FILES['cover']['tmp_name'])) {
        $uploadDirRel = 'uploads/covers/';
        $uploadDir = __DIR__ . '/../' . $uploadDirRel;
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
        $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
        $fnName = time() . '_' . rand(1000, 9999) . '.' . $ext;
        $destRel = $uploadDirRel . $fnName;
        $dest = __DIR__ . '/../' . $destRel;
        if (move_uploaded_file($_FILES['cover']['tmp_name'], $dest)) {
            // store path prefixed with app base so public URL resolves correctly
            $cover_path = '/perpustakaan/' . ltrim($destRel, '/');
        } else {
            $msg = 'Gagal mengunggah cover. Pastikan folder "uploads/covers/" dapat ditulisi oleh webserver.';
        }
    }

    // UPLOAD PDF
    $pdf_path = '';
    if (!empty($_FILES['pdf']['tmp_name'])) {
        $uploadPdfRel = 'uploads/pdfs/';
        $uploadPdfDir = __DIR__ . '/../' . $uploadPdfRel;
        if (!is_dir($uploadPdfDir)) @mkdir($uploadPdfDir, 0755, true);
        $ext = pathinfo($_FILES['pdf']['name'], PATHINFO_EXTENSION);
        $fnName = time() . '_' . rand(1000, 9999) . '.' . $ext;
        $destRel = $uploadPdfRel . $fnName;
        $dest = __DIR__ . '/../' . $destRel;
        if (move_uploaded_file($_FILES['pdf']['tmp_name'], $dest)) {
            $pdf_path = '/perpustakaan/' . ltrim($destRel, '/');
        } else {
            $msg = 'Gagal mengunggah file PDF. Pastikan folder "uploads/pdfs/" dapat ditulisi oleh webserver.';
        }
    }

    // UPDATE
    if ($id) {
        $sql = "UPDATE books SET 
            title='$title',
            author='$author',
            publisher='$publisher',
            publication_year=$year,
            isbn='$isbn',
            stock=$stock,
            description='$desc'";

        if ($cover_path)  $sql .= ", cover_path='$cover_path'";
        if ($pdf_path)    $sql .= ", pdf_path='$pdf_path'";

        $sql .= " WHERE id=$id";
        $mysqli->query($sql);

        // UPDATE CATEGORIES
        $mysqli->query("DELETE FROM book_category WHERE book_id=$id");
        if (!empty($_POST['categories'])) {
            foreach ($_POST['categories'] as $cid) {
                $cid = intval($cid);
                $mysqli->query("INSERT INTO book_category (book_id, category_id) VALUES ($id, $cid)");
            }
        }

    } else {
        // INSERT BARU
        $mysqli->query("INSERT INTO books 
            (title, author, publisher, publication_year, isbn, stock, description, cover_path, pdf_path, created_at)
            VALUES ('$title', '$author', '$publisher', $year, '$isbn', $stock, '$desc', '$cover_path', '$pdf_path', NOW())");

        $id = $mysqli->insert_id;

        if (!empty($_POST['categories'])) {
            foreach ($_POST['categories'] as $cid) {
                $cid = intval($cid);
                $mysqli->query("INSERT INTO book_category (book_id, category_id) VALUES ($id, $cid)");
            }
        }
    }

    $msg = 'Saved';
}

// ========================
// HANDLE DELETE (sebelum header.php!)
// ========================
if (isset($_GET['delete'])) {
    $did = intval($_GET['delete']);
    $mysqli->query("DELETE FROM books WHERE id=$did");
    $mysqli->query("DELETE FROM book_category WHERE book_id=$did");

    // Redirect aman, karena belum ada output HTML sama sekali
    header('Location: books.php');
    exit;
}

// ========================
// BARU SEKARANG PANGGIL HEADER (AMAN)
// ========================
include '../inc/header.php';

// load categories & books
$cats = $mysqli->query('SELECT * FROM categories ORDER BY name')->fetch_all(MYSQLI_ASSOC);
$books = $mysqli->query('SELECT b.* FROM books b ORDER BY b.title')->fetch_all(MYSQLI_ASSOC);
?>

<?php if ($msg) echo '<p class="success">' . esc($msg) . '</p>'; ?>

<main class="container-fluid admin">
    <h1>Tambah / Edit Buku</h1>

    <div class="panel">
        <form method="post" enctype="multipart/form-data" class="admin-form">
            <input type="hidden" name="id" id="id">

            <label>Title
                <input class="form-control" name="title" id="title" required placeholder="Judul buku">
            </label>

            <label>Author
                <input class="form-control" name="author" id="author" placeholder="Nama penulis">
            </label>

            <label>Publisher
                <input class="form-control" name="publisher" id="publisher" placeholder="Penerbit">
            </label>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <label>Publication Year
                    <input class="form-control" name="publication_year" id="publication_year" type="number" placeholder="2024">
                </label>
                <label>ISBN
                    <input class="form-control" name="isbn" id="isbn" placeholder="ISBN (opsional)">
                </label>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <label>Stock
                    <input class="form-control" name="stock" id="stock" type="number" value="1">
                </label>

                <label>Categories
                    <div class="cats">
                        <?php foreach ($cats as $c): ?>
                            <label><input type="checkbox" name="categories[]" value="<?= $c['id'] ?>"> <?= esc($c['name']) ?></label>
                        <?php endforeach; ?>
                    </div>
                </label>
            </div>

            <label>Cover
                <input type="file" class="form-control" name="cover" accept="image/*">
            </label>

            <label>PDF (optional)
                <input type="file" class="form-control" name="pdf" accept="application/pdf">
            </label>

            <label>Description
                <textarea class="form-control" name="description" id="description" placeholder="Sinopsis singkat buku"></textarea>
            </label>

            <div style="display:flex;gap:10px;margin-top:6px">
                <button class="btn btn-primary" type="submit">Save</button>
                <button type="button" class="btn btn-ghost"
                    onclick="document.getElementById('id').value='';document.querySelector('.admin-form').reset();">
                    Clear
                </button>
            </div>
        </form>
    </div>

    <h2 style="margin-top:24px">Existing Books</h2>
    <div class="list">
        <?php foreach ($books as $b): ?>
            <div class="book-row">
                <div class="left">
                    <?php if (!empty($b['cover_path'])): ?>
                        <img class="small-thumb" src="<?= esc($b['cover_path']) ?>">
                    <?php endif; ?>
                    <div>
                        <strong><?= esc($b['title']) ?></strong><br>
                        <small style="color:var(--muted)"><?= esc($b['author']) ?></small>
                    </div>
                </div>

                <div class="actions">
                    <a href="?delete=<?= $b['id'] ?>" class="danger">Delete</a>
                    <button class="btn small edit" data-book='<?= json_encode($b) ?>'>Edit</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<script>
document.querySelectorAll('.edit').forEach(btn => btn.addEventListener('click', () => {
    const b = JSON.parse(btn.dataset.book);
    document.getElementById('id').value = b.id;
    document.getElementById('title').value = b.title;
    document.getElementById('author').value = b.author;
    document.getElementById('publisher').value = b.publisher;
    document.getElementById('publication_year').value = b.publication_year;
    document.getElementById('isbn').value = b.isbn;
    document.getElementById('stock').value = b.stock;
    document.getElementById('description').value = b.description;
}));
</script>

<?php include '../inc/footer.php'; ?>
