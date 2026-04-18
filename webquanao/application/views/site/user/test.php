<h3>📊 Thống kê 7 ngày qua:</h3>
<ul>
    <li>Người dùng đang hoạt động: <?= $data['activeUsers'] ?? 'N/A' ?></li>
    <li>Người dùng mới: <?= $data['newUsers'] ?? 'N/A' ?></li>
    <li>Số phiên truy cập: <?= $data['sessions'] ?? 'N/A' ?></li>
    <li>Phiên có tương tác: <?= $data['engagedSessions'] ?? 'N/A' ?></li>
    <li>Tỷ lệ thoát: <?= isset($data['bounceRate']) ? number_format($data['bounceRate'], 2) . '%' : 'N/A' ?></li>
    <li>Thời lượng trung bình mỗi phiên: <?= isset($data['averageSessionDuration']) ? round($data['averageSessionDuration'], 2) . 's' : 'N/A' ?></li>
</ul>

<h3>📅 Tổng người dùng trong tháng <?= date('m/Y') ?>:</h3>
<ul>
    <li>Tổng người dùng: <?= $dataMonth['totalUsers'] ?? 'N/A' ?></li>
    <li>Người dùng mới: <?= $dataMonth['newUsers'] ?? 'N/A' ?></li>
</ul>
