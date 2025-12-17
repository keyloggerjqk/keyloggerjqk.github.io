<?php
if (isset($_GET['file'])) {
    $file = 'notes/' . basename($_GET['file']);
    if (file_exists($file)) {
        header('Content-Type: text/plain; charset=UTF-8');
        echo file_get_contents($file);
    } else {
        echo "File không tồn tại.";
    }
}
?>
