<?php 
$title = 'Quản Lý Khách Hàng';
require_once __DIR__ . '/layouts/header.php'; 
?>

<div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Danh sách Khách hàng</h2>
        <div class="flex gap-2">
            <input type="text" placeholder="Tìm kiếm SĐT, Tên..." class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Tìm</button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="p-4 font-semibold text-gray-600 text-sm">Họ và Tên</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm">Số điện thoại</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm">Hạng thành viên</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm">Tổng chi tiêu</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm">Điểm thưởng</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($customers)): ?>
                    <?php foreach($customers as $c): ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="p-4 text-sm font-medium text-gray-800"><?= htmlspecialchars($c['full_name']) ?></td>
                        <td class="p-4 text-sm text-gray-600"><?= htmlspecialchars($c['phone']) ?></td>
                        <td class="p-4">
                            <span class="px-2 py-1 text-xs font-bold rounded 
                                <?= $c['tier_id'] == 4 ? 'bg-purple-100 text-purple-700' /* VIP */ 
                                  : ($c['tier_id'] == 3 ? 'bg-yellow-100 text-yellow-700' /* Gold */
                                  : 'bg-gray-100 text-gray-600') ?>">
                                Hạng <?= $c['tier_id'] ?>
                            </span>
                        </td>
                        <td class="p-4 text-sm font-semibold text-green-600">
                            <?= number_format($c['total_spent'], 0, ',', '.') ?>đ
                        </td>
                        <td class="p-4 text-sm text-gray-600"><?= number_format($c['reward_points']) ?> pts</td>
                        <td class="p-4">
                            <button onclick="openEditModal(<?= $c['id'] ?>)" class="text-blue-600 hover:underline text-sm mr-2 font-medium">Sửa</button>
                            <button onclick="openHistoryModal(<?= $c['id'] ?>, '<?= htmlspecialchars($c['full_name']) ?>')" class="text-gray-500 hover:text-gray-700 text-sm font-medium">Lịch sử ĐH</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="p-4 text-center text-gray-500">Chưa có khách hàng nào</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Sửa Khách Hàng -->
<div id="editCustomerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all scale-95 opacity-0 duration-300" id="editModalContent">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-800">Cập nhật thông tin</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="editCustomerForm" class="space-y-4">
            <input type="hidden" id="edit_id" name="id">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Họ và Tên</label>
                <input type="text" id="edit_full_name" name="full_name" required class="w-full border border-gray-200 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Số điện thoại</label>
                <input type="text" id="edit_phone" name="phone" required class="w-full border border-gray-200 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                <input type="email" id="edit_email" name="email" class="w-full border border-gray-200 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Địa chỉ</label>
                <textarea id="edit_address" name="address" rows="3" class="w-full border border-gray-200 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="flex gap-3 mt-8">
                <button type="button" onclick="closeEditModal()" class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-600 rounded-xl font-bold hover:bg-gray-200 transition-all">Hủy</button>
                <button type="submit" class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Lịch sử Đơn hàng -->
<div id="historyModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 w-full max-w-4xl shadow-2xl transform transition-all scale-95 opacity-0 duration-300 overflow-hidden flex flex-col max-h-[90vh]" id="historyModalContent">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Lịch sử Đơn hàng</h3>
                <p id="historyCustomerName" class="text-sm text-gray-500"></p>
            </div>
            <button onclick="closeHistoryModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <div class="overflow-y-auto flex-1 pr-1 custom-scrollbar">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b sticky top-0">
                    <tr>
                        <th class="p-4 text-xs font-bold text-gray-500 uppercase">Mã Đơn</th>
                        <th class="p-4 text-xs font-bold text-gray-500 uppercase">Ngày đặt</th>
                        <th class="p-4 text-xs font-bold text-gray-500 uppercase">Trạng thái</th>
                        <th class="p-4 text-xs font-bold text-gray-500 uppercase text-right">Tổng tiền</th>
                    </tr>
                </thead>
                <tbody id="historyTableBody">
                    <!-- Data loaded via AJAX -->
                </tbody>
            </table>
            <div id="historyLoading" class="hidden py-10 text-center">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-4 text-gray-500">Đang tải lịch sử...</p>
            </div>
            <div id="historyEmpty" class="hidden py-10 text-center">
                <p class="text-gray-400 italic">Khách hàng chưa có đơn hàng nào.</p>
            </div>
        </div>
    </div>
</div>

<script>
function openEditModal(id) {
    const modal = document.getElementById('editCustomerModal');
    const content = document.getElementById('editModalContent');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);

    // Fetch data
    fetch(`?url=customer/apiGet/${id}`)
        .then(response => response.json())
        .then(res => {
            if (res.status === 'success') {
                const c = res.data;
                document.getElementById('edit_id').value = c.id;
                document.getElementById('edit_full_name').value = c.full_name;
                document.getElementById('edit_phone').value = c.phone;
                document.getElementById('edit_email').value = c.email || '';
                document.getElementById('edit_address').value = c.address || '';
            } else {
                alert(res.message);
                closeEditModal();
            }
        });
}

function closeEditModal() {
    const modal = document.getElementById('editCustomerModal');
    const content = document.getElementById('editModalContent');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }, 300);
}

document.getElementById('editCustomerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('edit_id').value;
    const formData = new FormData(this);

    fetch(`?url=customer/apiUpdate/${id}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(res => {
        if (res.status === 'success') {
            alert(res.message);
            location.reload();
        } else {
            alert(res.message);
        }
    });
});

function openHistoryModal(id, name) {
    const modal = document.getElementById('historyModal');
    const content = document.getElementById('historyModalContent');
    const tbody = document.getElementById('historyTableBody');
    const loading = document.getElementById('historyLoading');
    const empty = document.getElementById('historyEmpty');

    document.getElementById('historyCustomerName').textContent = `Khách hàng: ${name}`;
    tbody.innerHTML = '';
    loading.classList.remove('hidden');
    empty.classList.add('hidden');

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);

    fetch(`?url=customer/apiGetHistory/${id}`)
        .then(response => response.json())
        .then(res => {
            loading.classList.add('hidden');
            if (res.status === 'success') {
                if (res.data.length === 0) {
                    empty.classList.remove('hidden');
                } else {
                    res.data.forEach(order => {
                        const statusClass = getStatusClass(order.order_status);
                        const row = `
                            <tr class="border-b hover:bg-gray-50 transition-colors">
                                <td class="p-4 font-bold text-blue-600 text-sm">#${order.order_code}</td>
                                <td class="p-4 text-sm text-gray-600">${new Date(order.created_at).toLocaleDateString('vi-VN')}</td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider ${statusClass}">
                                        ${order.order_status}
                                    </span>
                                </td>
                                <td class="p-4 text-sm font-bold text-red-600 text-right">
                                    ${new Intl.NumberFormat('vi-VN').format(order.final_amount)}đ
                                </td>
                            </tr>
                        `;
                        tbody.insertAdjacentHTML('beforeend', row);
                    });
                }
            }
        });
}

function closeHistoryModal() {
    const modal = document.getElementById('historyModal');
    const content = document.getElementById('historyModalContent');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }, 300);
}

function getStatusClass(status) {
    switch(status) {
        case 'Pending': return 'bg-yellow-100 text-yellow-700';
        case 'Confirmed': return 'bg-blue-100 text-blue-700';
        case 'Shipping': return 'bg-indigo-100 text-indigo-700';
        case 'Completed': return 'bg-green-100 text-green-700';
        case 'Cancelled': return 'bg-red-100 text-red-700';
        default: return 'bg-gray-100 text-gray-700';
    }
}
</script>

<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
