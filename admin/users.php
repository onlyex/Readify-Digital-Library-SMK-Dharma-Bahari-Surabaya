<?php require_once __DIR__ . '/../config.php'; if(!isset($_SESSION['user'])||$_SESSION['user']['role']!=='admin'){ header('Location: ../index.php'); exit; } $page_title='Kelola Pengguna'; include '../inc/header.php';
if(isset($_GET['promote'])){
	$id = intval($_GET['promote']);
	$res = $mysqli->query("SELECT role FROM users WHERE id=$id");
	$target = $res ? $res->fetch_assoc() : null;
	if($target && $target['role'] !== 'admin'){
		$mysqli->query("UPDATE users SET role='admin' WHERE id=$id");
	}
	header('Location: users.php'); exit;
}
if(isset($_GET['delete'])){
	$id = intval($_GET['delete']);
	$res = $mysqli->query("SELECT role FROM users WHERE id=$id");
	$target = $res ? $res->fetch_assoc() : null;
	// prevent deleting admins and prevent deleting yourself
	if($target && $target['role'] !== 'admin' && $id !== intval($_SESSION['user']['id'])){
		$mysqli->query("DELETE FROM users WHERE id=$id");
	}
	header('Location: users.php'); exit;
}

$users = $mysqli->query('SELECT id,email,username,role,created_at FROM users ORDER BY created_at DESC')->fetch_all(MYSQLI_ASSOC);
?>
<main class="container-fluid admin">
	<h1>Kelola Pengguna</h1>
	<div class="panel">
		<table class="table">
			<thead>
				<tr><th>ID</th><th>Email</th><th>Username</th><th>Role</th><th>Joined</th><th>Action</th></tr>
			</thead>
			<tbody>
				<?php foreach($users as $u){
					$actions = '';
					// only show "Jadikan Admin" for non-admin users
					if($u['role'] !== 'admin'){
						$actions .= '<a class="btn btn-ghost" href="?promote='.$u['id'].'">Jadikan Admin</a> ';
					}
					// only allow delete for non-admins and not yourself
					if($u['role'] !== 'admin' && intval($u['id']) !== intval($_SESSION['user']['id'])){
						$actions .= '<a class="btn btn-ghost" href="?delete='.$u['id'].'">Hapus</a>';
					}
					echo '<tr><td>'.intval($u['id']).'</td><td>'.esc($u['email']).'</td><td>'.esc($u['username']).'</td><td>'.esc($u['role']).'</td><td>'.esc($u['created_at']).'</td><td>'.$actions.'</td></tr>';
				} ?>
			</tbody>
		</table>
	</div>
</main>
<?php include '../inc/footer.php'; ?>