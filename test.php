<?php
// === TẠO GHI CHÚ MỚI ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
  $content = trim($_POST['content']);
  if ($content !== '') {
    if (!is_dir('notes')) mkdir('notes', 0755, true);
    $id = uniqid("note_", true);
    $filename = "notes/{$id}.txt";
    file_put_contents($filename, $content);

    $rawLink = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/read.php?file=" . urlencode(basename($filename));

    // Rút gọn link bằng is.gd
    $short = @file_get_contents("https://is.gd/create.php?format=simple&url=" . urlencode($rawLink));
    $shortLink = $short ?: $rawLink;

    // Tạo ảnh QR
    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($shortLink);

    // Hiển thị kết quả
    $success = "
      ✅ Ghi chú đã lưu: <a href='$shortLink' target='_blank'>$shortLink</a><br>
      🧾 Link gốc: <small><a href='$rawLink' target='_blank'>$rawLink</a></small><br><br>
      🔲 Mã QR:<br>
      <img src='$qrUrl' alt='QR Code'>
    ";
  } else {
    $error = "⚠️ Nội dung không được rỗng.";
  }
}

// === XOÁ GHI CHÚ ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
  $file = 'notes/' . basename($_POST['delete_file']);
  if (file_exists($file)) {
    unlink($file);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Notepad PHP - Lưu TXT + QR + Link ngắn</title>
  <style>
    body { font-family: sans-serif; max-width: 800px; margin: 40px auto; background: #f4f4f4; padding: 20px; border-radius: 10px; }
    textarea { width: 100%; height: 200px; padding: 10px; font-size: 16px; border-radius: 8px; border: 1px solid #ccc; }
    button { padding: 10px 20px; font-size: 16px; margin-top: 10px; cursor: pointer; border: none; border-radius: 6px; background: #27ae60; color: white; }
    .result { margin: 15px 0; padding: 15px; background: #dff0d8; border-left: 5px solid #3c763d; border-radius: 6px; }
    .error  { margin: 15px 0; padding: 15px; background: #f2dede; border-left: 5px solid #a94442; border-radius: 6px; }
    h2 { margin-top: 40px; }
    ul { list-style: none; padding: 0; }
    li { margin-bottom: 8px; background: #fff; padding: 10px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
    form.inline { display: inline; }
    .note-link { max-width: 70%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  </style>
</head>
<body>

  <h1>📝 Notepad Online (PHP + TXT + QR)</h1>

  <?php if (!empty($success)): ?>
    <div class="result"> <?= $success ?> </div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="error"> <?= $error ?> </div>
  <?php endif; ?>

  <form method="POST">
    <textarea name="content" placeholder="Nhập ghi chú ở đây..."></textarea><br>
    <button type="submit">💾 Tạo ghi chú</button>
  </form>

  <h2>📋 Danh sách ghi chú (Admin)</h2>
  <ul>
    <?php
      if (is_dir('notes')) {
        $files = glob('notes/*.txt');
        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
        foreach ($files as $file):
          $name = basename($file);
          $date = date("Y-m-d H:i:s", filemtime($file));
          $link = $_SERVER['PHP_SELF'] . '?read_file=' . urlencode($name);
    ?>
    <li>
      <span class="note-link">
        <a href="<?= $link ?>" target="_blank"> <?= $name ?> </a> (<?= $date ?>)
      </span>
      <form method="POST" class="inline" onsubmit="return confirm('Bạn có chắc muốn xoá?')">
        <input type="hidden" name="delete_file" value="<?= $name ?>">
        <button type="submit">❌ Xoá</button>
      </form>
    </li>
    <?php endforeach;
      } else {
        echo "<li>Chưa có ghi chú nào.</li>";
      }
    ?>
  </ul>

<?php
// === READ FILE NỘI BỘ (GỘP SẴN) ===
if (isset($_GET['read_file'])) {
    $file = 'notes/' . basename($_GET['read_file']);
    if (file_exists($file)) {
        header('Content-Type: text/plain; charset=UTF-8');
        echo "\n========== NỘI DUNG GHI CHÚ =========\n\n";
        echo file_get_contents($file);
        exit;
    } else {
        header('Content-Type: text/plain; charset=UTF-8');
        echo "File không tồn tại.";
        exit;
    }
}
?>

</body>
</html>
