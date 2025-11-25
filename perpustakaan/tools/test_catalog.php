<?php
// tools/test_catalog.php
// Direct test: fetch catalog and check for book cards
require_once __DIR__ . '/../inc/config.php';

echo "Testing catalog.php book rendering\n\n";

// Get books from DB (same query as catalog.php now uses)
$res = $mysqli->query("SELECT id, title, author, publication_year, cover_path FROM books ORDER BY title ASC LIMIT 200");
$books = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

echo "Books found: " . count($books) . "\n";
if(!empty($books)){
  foreach($books as $b){
    echo "- ID {$b['id']}: {$b['title']} by {$b['author']} ({$b['publication_year']})\n";
    echo "  cover_path: {$b['cover_path']}\n";
  }
}
echo "\nIf 3 books appear above, then catalog.php should also show them.\n";
echo "Open: http://localhost/perpustakaan/catalog.php in your browser to verify.\n";
?>