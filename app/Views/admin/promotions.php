<?php
// app/Views/admin/promotions.php
$title = 'Quản Lý Khuyến Mãi';
require_once __DIR__ . '/layouts/header.php';
?>

<div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Mã Giảm Giá & Flash Sale</h2>
        <button onclick="openPromoModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            + Thêm Khuyến Mãi
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="p-4 font-semibold text-gray-600 text-sm">Mã Tặng</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm">Chương trình</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm">Sale / Loại</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm text-center">Thời gian</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm text-center">Lượt dùng</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm text-center">Trạng thái</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm text-center">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($promotions)): ?>
                    <?php foreach ($promotions as $p): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="p-4 text-sm font-bold text-orange-500"><?= htmlspecialchars($p['code']) ?></td>
                            <td class="p-4">
                                <div class="text-sm font-medium text-gray-800"><?= htmlspecialchars($p['name']) ?></div>
                                <div class="text-xs text-gray-500">
                                    Đơn từ <?= number_format($p['min_order_value'], 0, ',', '.') ?>đ 
                                    <?php if($p['max_discount_amount']): ?>
                                        - Giảm tối đa <?= number_format($p['max_discount_amount'], 0, ',', '.') ?>đ
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="p-4 text-sm text-blue-600 font-semibold">
                                <?= $p['discount_type'] === 'Percent' ? '-' . (float)$p['discount_value'] . '%' : '-' . number_format($p['discount_value'], 0, ',', '.') . 'đ' ?>
                            </td>
                            <td class="p-4 text-center">
                                <div class="text-xs text-gray-800">Bắt đầu: <?= date('d/m/Y', strtotime($p['start_date'])) ?></div>
                                <div class="text-xs text-red-500">K.thúc: <?= date('d/m/Y', strtotime($p['end_date'])) ?></div>
                            </td>
                            <td class="p-4 text-sm text-gray-600 text-center">
                                <span class="font-medium text-gray-900"><?= $p['used_count'] ?></span> / <?= $p['usage_limit'] ?: '∞' ?>
                                <?php if($p['usage_limit'] && $p['used_count'] >= $p['usage_limit']): ?>
                                    <div class="text-[10px] text-red-500 font-bold uppercase">Hết lượt</div>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-center">
                                <?php 
                                    $now = date('Y-m-d');
                                    $isExpired = $now > $p['end_date'];
                                    $isLimitReached = $p['usage_limit'] && $p['used_count'] >= $p['usage_limit'];
                                ?>
                                <?php if($isExpired): ?>
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-[10px] uppercase font-bold">Hết hạn</span>
                                <?php elseif($isLimitReached): ?>
                                    <span class="px-2 py-1 bg-red-100 text-red-600 rounded text-[10px] uppercase font-bold">Hết lượt</span>
                                <?php else: ?>
                                    <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                        <input type="checkbox" onchange="togglePromoStatus(<?= $p['id'] ?>)" <?= $p['status'] ? 'checked' : '' ?> class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-4 appearance-none cursor-pointer <?= $p['status'] ? 'border-green-500 translate-x-5' : 'border-gray-300' ?> transition-transform duration-200 ease-in-out"/>
                                        <label class="toggle-label block overflow-hidden h-5 rounded-full <?= $p['status'] ? 'bg-green-500' : 'bg-gray-300' ?> cursor-pointer transition-colors duration-200"></label>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-center">
                                <button onclick="editPromo(<?= $p['id'] ?>)" class="bg-blue-50 text-blue-600 p-2 rounded-lg hover:bg-blue-100 mr-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button onclick="deletePromo(<?= $p['id'] ?>)" class="bg-red-50 text-red-600 p-2 rounded-lg hover:bg-red-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="p-12 text-center text-gray-500 italic">Chưa có chương trình khuyến mãi nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Promotion -->
<div id="promoModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-8 rounded-2xl w-full max-w-xl shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <h3 id="modalTitle" class="text-xl font-bold mb-6">Thêm Khuyến Mãi Mới</h3>
        
        <form id="promoForm" class="grid grid-cols-2 gap-4">
            <input type="hidden" name="id" id="promo_id">
            
            <div class="col-span-1">
                <label class="block text-sm font-semibold mb-1">Mã khuyến mãi *</label>
                <input type="text" name="code" id="promo_code" class="w-full border p-2.5 rounded-lg outline-none focus:ring-2 focus:ring-blue-500" placeholder="VD: SUMMER30" required>
            </div>
            
            <div class="col-span-1">
                <label class="block text-sm font-semibold mb-1">Tên chương trình *</label>
                <input type="text" name="name" id="promo_name" class="w-full border p-2.5 rounded-lg outline-none focus:ring-2 focus:ring-blue-500" placeholder="VD: Sale Hè Rực Rỡ" required>
            </div>

            <div class="col-span-1">
                <label class="block text-sm font-semibold mb-1">Loại giảm giá *</label>
                <select name="discount_type" id="promo_type" class="w-full border p-2.5 rounded-lg outline-none">
                    <option value="Percent">Phần trăm (%)</option>
                    <option value="Fixed Amount">Số tiền cố định (đ)</option>
                </select>
            </div>

            <div class="col-span-1">
                <label class="block text-sm font-semibold mb-1">Giá trị giảm *</label>
                <input type="number" step="0.01" name="discount_value" id="promo_value" class="w-full border p-2.5 rounded-lg outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="col-span-1">
                <label class="block text-sm font-semibold mb-1">Đơn hàng tối thiểu *</label>
                <input type="number" name="min_order_value" id="promo_min" class="w-full border p-2.5 rounded-lg outline-none focus:ring-2 focus:ring-blue-500" value="0">
            </div>

            <div class="col-span-1">
                <label class="block text-sm font-semibold mb-1">Giảm tối đa (Optional)</label>
                <input type="number" name="max_discount_amount" id="promo_max" class="w-full border p-2.5 rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="col-span-1">
                <label class="block text-sm font-semibold mb-1">Ngày bắt đầu *</label>
                <input type="date" name="start_date" id="promo_start" class="w-full border p-2.5 rounded-lg outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="col-span-1">
                <label class="block text-sm font-semibold mb-1">Ngày kết thúc *</label>
                <input type="date" name="end_date" id="promo_end" class="w-full border p-2.5 rounded-lg outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="col-span-1">
                <label class="block text-sm font-semibold mb-1">Giới hạn lượt dùng (Optional)</label>
                <input type="number" name="usage_limit" id="promo_limit" class="w-full border p-2.5 rounded-lg outline-none focus:ring-2 focus:ring-blue-500" placeholder="∞">
            </div>

            <div class="col-span-1 flex items-end pb-3">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="status" id="promo_status" value="1" checked class="w-5 h-5 text-blue-600 rounded">
                    <span class="text-sm font-semibold">Kích hoạt</span>
                </label>
            </div>

            <div class="col-span-2 flex justify-end gap-3 pt-4 border-t mt-4">
                <button type="button" onclick="closePromoModal()" class="px-6 py-2 border rounded-lg hover:bg-gray-50 transition-colors">Hủy</button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition-colors">Lưu lại</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPromoModal() {
        document.getElementById('promoForm').reset();
        document.getElementById('promo_id').value = '';
        document.getElementById('modalTitle').innerText = 'Thêm Khuyến Mãi Mới';
        document.getElementById('promoModal').classList.remove('hidden');
    }

    function closePromoModal() {
        document.getElementById('promoModal').classList.add('hidden');
    }

    function editPromo(id) {
        fetch(`/shop_giay_admin/public/?url=promotion/apiGet&id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const p = data.data;
                    document.getElementById('promo_id').value = p.id;
                    document.getElementById('promo_code').value = p.code;
                    document.getElementById('promo_name').value = p.name;
                    document.getElementById('promo_type').value = p.discount_type;
                    document.getElementById('promo_value').value = p.discount_value;
                    document.getElementById('promo_min').value = p.min_order_value;
                    document.getElementById('promo_max').value = p.max_discount_amount || '';
                    document.getElementById('promo_start').value = p.start_date.split(' ')[0];
                    document.getElementById('promo_end').value = p.end_date.split(' ')[0];
                    document.getElementById('promo_limit').value = p.usage_limit || '';
                    document.getElementById('promo_status').checked = p.status == 1;
                    
                    document.getElementById('modalTitle').innerText = 'Chỉnh sửa Khuyến Mãi';
                    document.getElementById('promoModal').classList.remove('hidden');
                }
            });
    }

    document.getElementById('promoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        // Handle checkbox status correctly
        if (!document.getElementById('promo_status').checked) {
            formData.delete('status');
        }

        fetch('/shop_giay_admin/public/?url=promotion/apiSave', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        });
    });

    function togglePromoStatus(id) {
        fetch(`/shop_giay_admin/public/?url=promotion/apiToggleStatus&id=${id}`, { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
    }

    function deletePromo(id) {
        if (!confirm('Bạn có chắc chắn muốn xóa mã giảm giá này không?')) return;
        
        fetch(`/shop_giay_admin/public/?url=promotion/apiDelete&id=${id}`, { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
    }
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
