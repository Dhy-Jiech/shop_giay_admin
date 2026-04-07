<?php 
$title = 'Quản Lý Danh Mục';
require_once __DIR__ . '/layouts/header.php'; 
?>

<div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Cây Danh Mục Sản Phẩm</h2>
        <button onclick="openCatModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            + Thêm Danh Mục
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="p-4 font-semibold text-gray-600 text-sm">ID</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm">Tên Danh Mục / Slug</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm">Danh Mục Cha</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm">Trạng thái</th>
                    <th class="p-4 font-semibold text-gray-600 text-sm">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($categories)): ?>
                    <?php foreach($categories as $c): ?>
                    <tr class="border-b hover:bg-gray-50 transition-colors">
                        <td class="p-4 text-sm text-gray-500">#<?= $c['id'] ?></td>
                        <td class="p-4">
                            <div class="font-bold text-indigo-700"><?= htmlspecialchars($c['name']) ?></div>
                            <div class="text-[10px] text-gray-400 font-mono"><?= $c['slug'] ?></div>
                        </td>
                        <td class="p-4">
                            <span class="<?= $c['parent_id'] ? 'text-gray-700' : 'text-blue-500 font-bold italic' ?>">
                                <?= $c['parent_id'] ? 'Cấp con' : 'Danh mục gốc' ?>
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-1 <?= $c['status'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?> rounded text-[10px] uppercase font-bold">
                                <?= $c['status'] ? 'Hoạt động' : 'Tạm khóa' ?>
                            </span>
                        </td>
                        <td class="p-4">
                            <button onclick="editCategory(<?= $c['id'] ?>)" class="bg-indigo-50 text-indigo-600 p-2 rounded hover:bg-indigo-100 mr-2">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteCategory(<?= $c['id'] ?>)" class="bg-red-50 text-red-600 p-2 rounded hover:bg-red-100">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="p-8 text-center text-gray-500 italic">Chưa có danh mục nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Category -->
<div id="catModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-8 rounded-2xl w-[450px] shadow-2xl relative">
        <h3 id="modalTitle" class="text-xl font-bold mb-6">Thêm Danh Mục Mới</h3>
        <form id="catForm" class="space-y-4">
            <input type="hidden" name="id" id="catId">
            <div>
                <label class="block text-sm font-semibold mb-1">Tên danh mục *</label>
                <input type="text" name="name" class="w-full border p-3 rounded-lg outline-none focus:ring-2 focus:ring-indigo-500" placeholder="VD: Giày Sneaker, Giày Chạy..." required>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Danh mục cha</label>
                <select name="parent_id" class="w-full border p-3 rounded-lg outline-none">
                    <option value="">-- Không có (Danh mục gốc) --</option>
                    <?php foreach($categories as $pc): ?>
                        <?php if(!$pc['parent_id']): ?>
                        <option value="<?= $pc['id'] ?>"><?= htmlspecialchars($pc['name']) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Trạng thái</label>
                <select name="status" class="w-full border p-3 rounded-lg outline-none">
                    <option value="1">Hiển thị</option>
                    <option value="0">Ẩn</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeCatModal()" class="px-6 py-2 border rounded-lg">Hủy</button>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-bold">Lưu lại</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCatModal() { 
        document.getElementById('modalTitle').innerText = 'Thêm Danh Mục Mới';
        document.getElementById('catForm').reset();
        document.getElementById('catId').value = '';
        document.getElementById('catModal').classList.remove('hidden'); 
    }
    function closeCatModal() { document.getElementById('catModal').classList.add('hidden'); }

    function editCategory(id) {
        fetch(`/shop_giay_admin/public/?url=category/apiGet&id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const cat = data.data;
                    document.getElementById('modalTitle').innerText = 'Chỉnh Sửa Danh Mục';
                    document.getElementById('catId').value = cat.id;
                    document.querySelector('input[name="name"]').value = cat.name;
                    document.querySelector('select[name="parent_id"]').value = cat.parent_id || '';
                    document.querySelector('select[name="status"]').value = cat.status;
                    document.getElementById('catModal').classList.remove('hidden');
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Không thể lấy thông tin danh mục!');
            });
    }

    function deleteCategory(id) {
        if (!confirm('Bạn có chắc chắn muốn xóa danh mục này? Hệ thống sẽ báo lỗi nếu danh mục đang có sản phẩm.')) return;

        fetch(`/shop_giay_admin/public/?url=category/apiDelete&id=${id}`, { method: 'POST' })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.status === 'success') {
                location.reload();
            }
        })
        .catch(err => {
            console.error(err);
            alert('Có lỗi xảy ra khi kết nối máy chủ!');
        });
    }

    document.getElementById('catForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const catId = document.getElementById('catId').value;
        const url = catId 
            ? `/shop_giay_admin/public/?url=category/apiUpdate&id=${catId}` 
            : '/shop_giay_admin/public/?url=category/apiStore';
        
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.status === 'success') {
                location.reload();
            }
        })
        .catch(err => {
            console.error(err);
            alert('Có lỗi xảy ra khi gửi dữ liệu!');
        });
    });
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
