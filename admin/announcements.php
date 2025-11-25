<?php require_once __DIR__ . '/../config.php'; if(!isset($_SESSION['user'])||$_SESSION['user']['role']!=='admin'){ header('Location: ../index.php'); exit; } $page_title='Buat Pengumuman'; include '../inc/header.php';
if($_SERVER['REQUEST_METHOD']==='POST'){ $title=$mysqli->real_escape_string($_POST['title']); $content=$mysqli->real_escape_string($_POST['content']); if(!empty($_POST['id'])){ $id=intval($_POST['id']); $mysqli->query("UPDATE announcements SET title='$title',content='$content' WHERE id=$id"); } else { $mysqli->query("INSERT INTO announcements (title,content,created_at) VALUES ('$title','$content',NOW())"); } header('Location: announcements.php'); exit; }
if(isset($_GET['delete'])){ $id=intval($_GET['delete']); $mysqli->query("DELETE FROM announcements WHERE id=$id"); header('Location: announcements.php'); exit; }
$ann = $mysqli->query('SELECT * FROM announcements ORDER BY created_at DESC')->fetch_all(MYSQLI_ASSOC);
?>
<main class="container-fluid admin">
	<h1>Buat Pengumuman</h1>
	<div class="panel">
		<form method="post" class="admin-form" style="max-width:900px">
			<input type="hidden" name="id" id="id">
			<label>Title
				<input class="form-control" name="title" id="title" placeholder="Judul pengumuman">
			</label>
			<label>Content
				<textarea class="form-control" name="content" id="content" placeholder="Tulis pengumuman di sini..."></textarea>
			</label>
			<div style="display:flex;gap:10px">
				<button class="btn btn-primary" type="submit">Simpan</button>
				<button type="reset" class="btn btn-ghost">Bersihkan</button>
			</div>
		</form>
	</div>

	<h2 style="margin-top:18px">Existing Announcements</h2>
	<ul style="padding-left:18px;color:var(--text);">
		<?php foreach($ann as $a) echo '<li style="margin-bottom:8px"><strong>'.esc($a['title']).'</strong> - '.esc($a['created_at']).' <a href="?delete='.$a['id'].'" class="danger">Delete</a></li>'; ?>
	</ul>
</main>
<?php include '../inc/footer.php'; ?>