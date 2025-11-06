<?php


$file = __DIR__ . '/data/data.csv';


function readUsers() {
  global $file;
  $users = [];
  if (file_exists($file)) {
    $data = fopen($file, 'r');
    while (($row = fgetcsv($data)) !== false) {
      
      $users[] = [
        'student_id' => $row[0] ?? '',
        'name' => $row[1] ?? '',
        'sex' => $row[2] ?? '',
        'birth' => $row[3] ?? ''
      ];
    }
    fclose($data);
  }
  return $users;
}


function writeUsers($users) {
  global $file;
  $data = fopen($file . '.tmp', 'w');
  if ($data === false) return false;
  if (!flock($data, LOCK_EX)) { fclose($data); return false; }
  foreach ($users as $user) {
    fputcsv($data, [$user['student_id'], $user['name'], $user['sex'] ?? '', $user['birth'] ?? '']);
  }
  fflush($data);
  flock($data, LOCK_UN);
  fclose($data);
  return rename($file . '.tmp', $file);
}


if (isset($_POST['add'])) {
  $users = readUsers();
  $users[] = [
    'student_id' => trim($_POST['student_id'] ?? ''),
    'name' => trim($_POST['name'] ?? ''),
    'sex' => trim($_POST['sex'] ?? ''),
    'birth' => trim($_POST['birth'] ?? '')
  ];
  usort($users, fn($a,$b) => $a['student_id'] <=> $b['student_id']);
  writeUsers($users);
  header('Location: dashboard.php');
  exit;
}

if (isset($_POST['delete'])) {
  $id = trim($_POST['student_id'] ?? $_POST['delete'] ?? '');
  $users = array_filter(readUsers(), fn($u) => $u['student_id'] !== $id);
  writeUsers(array_values($users));
  header('Location: dashboard.php');
  exit;
}
?>