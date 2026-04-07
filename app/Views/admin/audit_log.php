<?php 
$title = 'Nhật Ký Hệ Thống (Audit Logs)';
require_once __DIR__ . '/layouts/header.php'; 
?>

<div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
            <i class="fas fa-history text-gray-400"></i> Lịch sử hoạt động của nhân viên
        </h2>
        <div class="flex gap-2">
            <input type="text" placeholder="Tìm kiếm hành động..." class="border p-2 rounded-lg text-sm outline-none focus:ring-1">
            <button class="bg-gray-100 px-4 py-2 rounded-lg text-sm hover:bg-gray-200"><i class="fas fa-filter"></i> Lọc</button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="p-4 font-semibold text-gray-600 text-sm">Thời gian</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm">Người thực hiện</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm">Hành động</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm">Bảng dữ liệu</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm">IP Address</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm">Dữ liệu chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($logs)): ?>
                    <?php foreach($logs as $log): ?>
                    <tr class="border-b hover:bg-gray-50 transition-colors text-sm">
                        <td class="p-4 text-gray-500 font-mono"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                        <td class="p-4">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 bg-blue-100 text-blue-600 text-[10px] rounded-full flex items-center justify-center font-bold uppercase">
                                    <?= substr($log['by_user'] ?? 'SYS', 0, 1) ?>
                                </span>
                                <span class="font-medium"><?= htmlspecialchars($log['by_user'] ?? 'Hệ thống') ?></span>
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                <?= str_contains($log['action'], 'CREATE') ? 'bg-green-100 text-green-700' : 
                                   (str_contains($log['action'], 'UPDATE') ? 'bg-yellow-100 text-yellow-700' : 
                                   (str_contains($log['action'], 'DELETE') ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600')) ?>">
                                <?= $log['action'] ?>
                            </span>
                        </td>
                        <td class="p-4 font-mono text-xs text-blue-500"><?= $log['table_name'] ?> (#<?= $log['record_id'] ?>)</td>
                        <td class="p-4 text-gray-400 font-mono text-xs"><?= $log['ip_address'] ?></td>
                        <td class="p-4">
                            <button onclick='showLogData(<?= json_encode($log) ?>)' class="text-blue-600 hover:underline">Chi tiết JSON</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="p-12 text-center text-gray-500 italic">Hệ thống chưa ghi nhận log nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Log Detail -->
<div id="logModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-8 rounded-2xl w-[700px] shadow-2xl relative max-h-[80vh] overflow-y-auto">
        <h3 class="text-xl font-bold mb-4 border-b pb-2">Chi tiết thay đổi dữ liệu</h3>
        <div id="logDetailContent" class="space-y-4">
            <!-- Dynamic Content -->
        </div>
        <div class="mt-6 flex justify-end">
            <button onclick="closeLogModal()" class="bg-gray-800 text-white px-6 py-2 rounded-lg">Đóng</button>
        </div>
    </div>
</div>

<script>
    function showLogData(log) {
        let content = `
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-red-50 rounded-xl">
                    <h4 class="text-xs font-bold text-red-600 mb-2 font-mono uppercase">Dữ liệu Cũ</h4>
                    <pre class="text-[10px] font-mono break-all whitespace-pre-wrap">${log.old_data || 'N/A'}</pre>
                </div>
                <div class="p-4 bg-green-50 rounded-xl">
                    <h4 class="text-xs font-bold text-green-600 mb-2 font-mono uppercase">Dữ liệu Mới</h4>
                    <pre class="text-[10px] font-mono break-all whitespace-pre-wrap">${log.new_data || 'N/A'}</pre>
                </div>
            </div>
        `;
        document.getElementById('logDetailContent').innerHTML = content;
        document.getElementById('logModal').classList.remove('hidden');
    }
    function closeLogModal() { document.getElementById('logModal').classList.add('hidden'); }
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
