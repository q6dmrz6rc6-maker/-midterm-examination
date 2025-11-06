<?php


session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$user = $_SESSION['user'];

$dataFile = __DIR__ . '/data/data.csv';

$error = '';

$students = [];
if (file_exists($dataFile)) {
    $f = fopen($dataFile, 'r');
    if ($f !== false) {
        while (($row = fgetcsv($f)) !== false) {
            if (!is_array($row) || count($row) < 4) continue;
            $row = array_map('trim', $row);
            if ($row[0] === '') continue;
            $students[] = $row;
        }
        fclose($f);
    }
    usort($students, fn($a, $b) => $a[0] <=> $b[0]);
}

if (($user['role'] ?? '') === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add
    if (isset($_POST['add'])) {
        $newID    = trim($_POST['studentID'] ?? '');
        $newName  = trim($_POST['name'] ?? '');
        $newSex   = trim($_POST['sex'] ?? '');
        $newBirth = trim($_POST['birth'] ?? '');

        if ($newID !== '' && $newName !== '' && $newSex !== '' && $newBirth !== '') {
            // prevent duplicate ID
            $exists = array_filter($students, fn($s) => $s[0] === $newID);
            if (empty($exists)) {
                $students[] = [$newID, $newName, $newSex, $newBirth];
            } else {
                $error = 'Student ID already exists.';
            }
            usort($students, fn($a, $b) => $a[0] <=> $b[0]);

            if ($error === '') {
                $tmp = $dataFile . '.tmp';
                $f = fopen($tmp, 'w');
                if ($f !== false) {
                    if (flock($f, LOCK_EX)) {
                        foreach ($students as $s) {
                            fputcsv($f, $s);
                        }
                        fflush($f);
                        flock($f, LOCK_UN);
                    }
                    fclose($f);
                    rename($tmp, $dataFile);
                }
            }
        } else {
            $error = 'All fields are required for adding.';
        }

        if ($error === '') {
            header('Location: dashboard.php');
            exit;
        }
    }

    
    if (isset($_POST['update'])) {
        $origID   = trim($_POST['orig_id'] ?? '');
        $newID    = trim($_POST['studentID'] ?? '');
        $newName  = trim($_POST['name'] ?? '');
        $newSex   = trim($_POST['sex'] ?? '');
        $newBirth = trim($_POST['birth'] ?? '');

        if ($origID === '' || $newID === '' || $newName === '' || $newSex === '' || $newBirth === '') {
            $error = 'All fields are required for update.';
        } else {
           
            if ($newID !== $origID) {
                foreach ($students as $s) {
                    if ($s[0] === $newID) {
                        $error = 'New Student ID already exists.';
                        break;
                    }
                }
            }

            if ($error === '') {
                foreach ($students as &$s) {
                    if ($s[0] === $origID) {
                        $s[0] = $newID;
                        $s[1] = $newName;
                        $s[2] = $newSex;
                        $s[3] = $newBirth;
                        break;
                    }
                }
                unset($s);
                usort($students, fn($a, $b) => $a[0] <=> $b[0]);

                $tmp = $dataFile . '.tmp';
                $f = fopen($tmp, 'w');
                if ($f !== false) {
                    if (flock($f, LOCK_EX)) {
                        foreach ($students as $s) {
                            fputcsv($f, $s);
                        }
                        fflush($f);
                        flock($f, LOCK_UN);
                    }
                    fclose($f);
                    rename($tmp, $dataFile);
                }

                header('Location: dashboard.php');
                exit;
            }
        }

        
        $editing = ['id' => $newID, 'name' => $newName, 'sex' => $newSex, 'birth' => $newBirth];
    }

    
    if (isset($_POST['delete'])) {
        $idToDelete = (string)($_POST['delete'] ?? '');
        $students = array_values(array_filter($students, fn($s) => $s[0] !== $idToDelete));

        $tmp = $dataFile . '.tmp';
        $f = fopen($tmp, 'w');
        if ($f !== false) {
            if (flock($f, LOCK_EX)) {
                foreach ($students as $s) {
                    fputcsv($f, $s);
                }
                fflush($f);
                flock($f, LOCK_UN);
            }
            fclose($f);
            rename($tmp, $dataFile);
        }

        header('Location: dashboard.php');
        exit;
    }
}


$editing = $editing ?? null;
if (($user['role'] ?? '') === 'admin' && isset($_GET['edit']) && !isset($editing)) {
    $editID = trim((string)($_GET['edit'] ?? ''));
    foreach ($students as $s) {
        if ($s[0] === $editID) {
            $editing = ['id' => $s[0], 'name' => $s[1], 'sex' => $s[2], 'birth' => $s[3]];
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Dashboard - Classroom Manager</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="style.css">
  <script src="script.js" defer></script>
</head>
<body>
  <header>
    <h2>Classroom Members</h2>
    <p>Logged in as <strong><?= htmlspecialchars($user['username'] ?? $user['name'] ?? '') ?></strong> (<?= htmlspecialchars($user['role'] ?? '') ?>)</p>
    <a href="logout.php">Logout</a>
  </header>

  <div class="container">
    <?php if (($user['role'] ?? '') === 'admin'): ?>
    <section class="form-section" aria-label="<?= $editing ? 'Edit member' : 'Add member' ?>">
      <h3><?= $editing ? 'Edit member' : 'Add members' ?></h3>

      <?php if ($error): ?>
        <div style="color:var(--danger);margin-bottom:8px"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($editing): ?>
      <form method="post" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <!-- Allow editing ID now -->
        <input type="text" name="studentID" value="<?= htmlspecialchars($editing['id']) ?>" required>
        <input type="text" name="name" value="<?= htmlspecialchars($editing['name']) ?>" required>
        <select name="sex" required>
          <option value="">Sex</option>
          <option value="Male" <?= $editing['sex'] === 'Male' ? 'selected' : '' ?>>Male</option>
          <option value="Female" <?= $editing['sex'] === 'Female' ? 'selected' : '' ?>>Female</option>
        </select>
        <input type="date" name="birth" value="<?= htmlspecialchars($editing['birth']) ?>" required>
        <input type="hidden" name="orig_id" value="<?= htmlspecialchars($editing['id']) ?>">
        <button type="submit" name="update" class="btn">Update</button>
        <a class="btn secondary" href="dashboard.php">Cancel</a>
      </form>
      <?php else: ?>
      <form method="post" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <input type="text" name="studentID" placeholder="Student ID" required>
        <input type="text" name="name" placeholder="Name" required>
        <select name="sex" required>
          <option value="">Sex</option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
        </select>
        <input type="date" name="birth" required>
        <button type="submit" name="add" class="btn">Add</button>
      </form>
      <?php endif; ?>
    </section>
    <?php endif; ?>

    <section class="table-wrap" aria-live="polite">
      <table>
        <thead>
          <tr>
            <th>ID</th><th>Name</th><th>Sex</th><th>Birth</th>
            <?php if (($user['role'] ?? '') === 'admin'): ?><th>Action</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($students)): ?>
            <tr><td colspan="<?= ($user['role'] ?? '') === 'admin' ? 5 : 4 ?>" class="placeholder">No members</td></tr>
          <?php else: ?>
            <?php foreach ($students as $s): ?>
              <tr>
                <td><?= htmlspecialchars($s[0]) ?></td>
                <td><?= htmlspecialchars($s[1]) ?></td>
                <td><?= htmlspecialchars($s[2]) ?></td>
                <td><?= htmlspecialchars($s[3]) ?></td>
                <?php if (($user['role'] ?? '') === 'admin'): ?>
                <td class="actions">
                  <form method="post" style="display:inline;">
                    <button name="delete" value="<?= htmlspecialchars($s[0]) ?>" class="btn danger" onclick="return confirm('Delete?')">Delete</button>
                  </form>
                  <a class="btn secondary" href="dashboard.php?edit=<?= urlencode($s[0]) ?>">Edit</a>
                </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </div>
</body>
</html>