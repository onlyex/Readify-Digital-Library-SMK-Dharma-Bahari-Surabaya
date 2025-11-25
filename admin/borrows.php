<?php require_once __DIR__ . '/../config.php'; if(!isset($_SESSION['user'])||$_SESSION['user']['role']!=='admin'){ header('Location: ../index.php'); exit; } $page_title='Manage Borrows'; include '../inc/header.php';
if(isset($_GET['return'])){ $id=intval($_GET['return']); $b=$mysqli->query("SELECT * FROM borrows WHERE id=$id")->fetch_assoc(); if($b){ $mysqli->query("UPDATE borrows SET returned_at=NOW(), status='returned' WHERE id=$id"); $mysqli->query("UPDATE books SET stock=stock+1 WHERE id=".intval($b['book_id'])); } header('Location: borrows.php'); exit; }
// accept return requests from users
if(isset($_GET['accept']) && isset($_GET['id'])){ $id=intval($_GET['id']); $b=$mysqli->query("SELECT * FROM borrows WHERE id=$id")->fetch_assoc(); if($b && $b['status']=='return_requested'){ $mysqli->query("UPDATE borrows SET returned_at=NOW(), status='returned' WHERE id=$id"); $mysqli->query("UPDATE books SET stock=stock+1 WHERE id=".intval($b['book_id'])); } header('Location: borrows.php'); exit; }
if(isset($_GET['fine'])){ $id=intval($_GET['fine']); $amt=floatval($_GET['amt']); $mysqli->query("UPDATE borrows SET fine=$amt WHERE id=$id"); header('Location: borrows.php'); exit; }

// approve borrow requests (set to borrowed, decrement stock, set due date)
if(isset($_GET['approve'])){
	$id = intval($_GET['approve']);
	$r = $mysqli->query("SELECT * FROM borrows WHERE id=$id")->fetch_assoc();
	if($r && $r['status']=='requested'){
		// check stock
		$b = $mysqli->query("SELECT stock FROM books WHERE id=".intval($r['book_id']))->fetch_assoc();
		if($b && $b['stock']>0){
			$mysqli->query("UPDATE borrows SET status='borrowed', borrowed_at=NOW(), due_at=DATE_ADD(NOW(), INTERVAL 14 DAY) WHERE id=$id");
			$mysqli->query("UPDATE books SET stock=stock-1 WHERE id=".intval($r['book_id']));
		} else {
			// cannot approve if no stock
			$_SESSION['msg'] = 'Tidak bisa menyetujui: stok buku tidak cukup.';
		}
	}
	header('Location: borrows.php'); exit;
}

// fetch borrow records safely
$res = $mysqli->query('SELECT br.*, u.username, b.title FROM borrows br LEFT JOIN users u ON br.user_id=u.id LEFT JOIN books b ON br.book_id=b.id ORDER BY br.id DESC');
if(!$res){
	error_log('borrows query failed: '.$mysqli->error);
	$rows = [];
} else {
	$rows = $res->fetch_all(MYSQLI_ASSOC);
}
?>
<main class="container-fluid admin"><h1>Borrow Records</h1>
<table class="table"><thead><tr><th>ID</th><th>User</th><th>Book</th><th>Borrowed</th><th>Due</th><th>Returned</th><th>Status</th><th>Fine</th><th>Action</th></tr></thead><tbody>
<?php foreach($rows as $r){
	$id = intval($r['id']);
	echo '<tr data-id="'.$id.'"><td>'.intval($r['id']).'</td><td>'.esc($r['username']).'</td><td>'.esc($r['title']).'</td><td>'.esc($r['borrowed_at']).'</td><td>'.esc($r['due_at']).'</td><td>'.esc($r['returned_at']).'</td><td class="status-cell">'.esc($r['status']).'</td><td>'.esc($r['fine']).'</td><td class="action-cell">';
	if($r['status']=='requested') echo ' <button class="btn btn-approve" data-id="'.$id.'">Approve</button> ';
	if($r['status']=='borrowed') echo ' <a href="?return='.$r['id'].'">Mark Returned</a>';
	if($r['status']=='return_requested') echo ' <a href="?accept=1&id='.$r['id'].'" class="btn">Accept Return</a>';
	echo ' | <a href="?fine='.$r['id'].'&amt=5">Set Fine 5</a>';
	echo '</td></tr>';
} ?>
</tbody></table>
</main>
<script>
document.addEventListener('DOMContentLoaded', function(){
	document.querySelectorAll('.btn-approve').forEach(function(btn){
		btn.addEventListener('click', function(e){
			var id = this.dataset.id;
			if(!confirm('Setujui permintaan pinjam #'+id+'?')) return;
			fetch('approve_borrow.php', {
				method: 'POST',
				headers: {'Content-Type':'application/x-www-form-urlencoded'},
				body: 'id='+encodeURIComponent(id)
			}).then(r=>r.json()).then(function(j){
				if(j.ok){
					var tr = document.querySelector('tr[data-id="'+id+'"]');
					if(tr){ tr.querySelector('.status-cell').textContent = 'borrowed';
						tr.querySelector('.action-cell').innerHTML = '<a href="?return='+id+'">Mark Returned</a> | <a href="?fine='+id+'&amt=5">Set Fine 5</a>';
					}
					if(window.showToast) showToast('Permintaan disetujui');
				} else {
					alert('Error: '+j.msg);
				}
			}).catch(function(err){ alert('Network error'); });
		});
	});
});
</script>
<?php include '../inc/footer.php'; ?>