<?php
require_once __DIR__ . '/config.php';
$page = 'catalog';
$page_title = 'Katalog Buku';
$base = '/perpustakaan/';
include __DIR__ . '/inc/header.php';

/* Helper Cover */
function get_cover_url($cover_path) {
    if (empty($cover_path)) {
        return '/perpustakaan/assets/img/book-placeholder.png';
    }

    $cover_path = trim($cover_path);

    if (strpos($cover_path, 'http://') === 0 || strpos($cover_path, 'https://') === 0) {
        return $cover_path;
    }

    $file = '/perpustakaan/uploads/covers/' . basename($cover_path);
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $file)) {
        return $file;
    }

    return '/perpustakaan/assets/img/book-placeholder.png';
}

/* Params */
$selected_cat = intval($_GET['category'] ?? 0);
$search_q     = trim($_GET['q'] ?? '');
$page_num     = max(1, intval($_GET['p'] ?? 1));
$per_page     = 16;
$offset       = ($page_num - 1) * $per_page;

/* Load categories */
$cats = [];
$res = $mysqli->query("SELECT id,name FROM categories ORDER BY name ASC");
if ($res) $cats = $res->fetch_all(MYSQLI_ASSOC);

/* Build base query */
$sql_base = "
    FROM books b
    LEFT JOIN book_category bc ON b.id = bc.book_id
    WHERE 1=1
";

if ($selected_cat > 0) {
    $sql_base .= " AND bc.category_id = $selected_cat";
}

if (!empty($search_q)) {
    $q = $mysqli->real_escape_string($search_q);
    $sql_base .= " AND (b.title LIKE '%$q%' OR b.author LIKE '%$q%')";
}

/* Count total */
$count = $mysqli->query("SELECT COUNT(DISTINCT b.id) AS c $sql_base")->fetch_assoc()['c'];
$total_books = intval($count);

/* Load books */
$sql_books = "
    SELECT DISTINCT b.id, b.title, b.author, b.publication_year, b.cover_path, b.pdf_path, b.stock
    $sql_base
    ORDER BY b.title ASC
    LIMIT $offset, $per_page
";

$books = $mysqli->query($sql_books)->fetch_all(MYSQLI_ASSOC);

$total_pages = ceil($total_books / $per_page);
?>

<!-- HERO -->
<div class="catalog-hero">
    <h1>Katalog Buku</h1>
    <p class="muted-text">Temukan dan jelajahi buku terbaik di perpustakaan kami</p>
</div>

<div class="catalog-container">

    <!-- SIDEBAR -->
    <aside class="catalog-sidebar">
        <div class="panel">
            <h3>Kategori</h3>

            <div class="category-list">
                <a href="/perpustakaan/catalog.php"
                   class="category-link <?php echo $selected_cat === 0 ? 'active' : ''; ?>">
                    Semua Kategori
                </a>

                <?php foreach ($cats as $c): ?>
                    <a href="/perpustakaan/catalog.php?category=<?php echo $c['id']; ?>"
                       class="category-link <?php echo $selected_cat == $c['id'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($c['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>

        </div>
    </aside>

    <!-- CONTENT -->
    <section class="catalog-content">

        <!-- SEARCH -->
        <div class="catalog-search-area">
            <form class="search-form" method="get" action="/perpustakaan/catalog.php">

                <?php if ($selected_cat > 0): ?>
                    <input type="hidden" name="category" value="<?php echo $selected_cat; ?>">
                <?php endif; ?>

                <input type="text" name="q" class="search-input"
                       placeholder="Cari judul atau penulis..."
                       value="<?php echo htmlspecialchars($search_q); ?>">

                <button class="btn btn-primary">Cari</button>

                <?php if (!empty($search_q)): ?>
                    <a href="/perpustakaan/catalog.php" class="btn btn-outline">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- HEADER -->
        <h2 class="catalog-title">Daftar Buku</h2>
        <p class="catalog-subtitle"><?php echo $total_books; ?> buku ditemukan</p>

        <!-- BOOK GRID -->
        <div class="books-grid">

            <?php if (empty($books)): ?>
                <div class="empty-state">
                    <h3>Tidak Ada Buku</h3>
                    <p>Coba kata kunci lain atau ubah kategori.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($books as $b): ?>
                <article class="book-item">

                    <!-- COVER -->
                    <div class="book-image-wrapper">
                        <img src="<?php echo get_cover_url($b['cover_path']); ?>"
                             alt="<?php echo htmlspecialchars($b['title']); ?>"
                             class="book-image">
                    </div>

                    <!-- INFO -->
                    <div class="book-info">
                        <h3 class="book-title"><?php echo htmlspecialchars($b['title']); ?></h3>
                        <p class="book-author">
                            <?php echo htmlspecialchars($b['author']); ?>
                        </p>
                    </div>

                    <!-- ACTIONS -->
                    <div class="book-actions">
                        <a href="/perpustakaan/book_view.php?id=<?php echo $b['id']; ?>"
                           class="btn btn-outline">Detail</a>

                        <?php if (!empty($b['pdf_path'])): ?>
                            <a href="<?php echo $b['pdf_path']; ?>" class="btn btn-outline" download>PDF</a>
                        <?php endif; ?>

                        <?php if (!empty($_SESSION['user'])): ?>
                            <?php if ($b['stock'] > 0): ?>
                                <form method="post" action="/perpustakaan/borrow.php">
                                    <input type="hidden" name="book_id" value="<?php echo $b['id']; ?>">
                                    <button class="btn btn-primary">Pinjam</button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-disabled" disabled>Habis</button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a class="btn btn-primary" href="/perpustakaan/login.php">Pinjam</a>
                        <?php endif; ?>

                    </div>
                </article>
            <?php endforeach; ?>

        </div>

        <!-- PAGINATION -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page_num > 1): ?>
                    <a class="btn btn-outline"
                       href="/perpustakaan/catalog.php?p=<?php echo $page_num - 1; ?>">← Sebelumnya</a>
                <?php endif; ?>

                <span>Halaman <?php echo $page_num; ?> dari <?php echo $total_pages; ?></span>

                <?php if ($page_num < $total_pages): ?>
                    <a class="btn btn-outline"
                       href="/perpustakaan/catalog.php?p=<?php echo $page_num + 1; ?>">Selanjutnya →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </section>

</div>

<?php include __DIR__ . '/inc/footer.php'; ?>
