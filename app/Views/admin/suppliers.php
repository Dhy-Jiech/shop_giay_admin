<?php 
$title = 'Quản Lý Nhà Cung Cấp';
require_once __DIR__ . '/layouts/header.php'; 
?>

<div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Danh Mục Nhà Cung Cấp</h2>
        <button onclick="openSupplierModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            + Thêm Nhà Cung Cấp
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php if(!empty($suppliers)): ?>
            <?php foreach($suppliers as $s): ?>
            <div class="bg-gray-50 border rounded-2xl p-6 flex flex-col items-center group hover:border-blue-300 transition-all hover:shadow-md">
                <div class="w-20 h-20 mb-4 bg-white rounded-xl overflow-hidden shadow-sm flex items-center justify-center p-2 border border-gray-100">
                    <?php if(!empty($s['logo'])): ?>
                        <img src="<?= htmlspecialchars($s['logo']) ?>" class="max-w-full max-h-full object-contain">
                    <?php else: ?>
                        <span class="text-2xl font-bold text-gray-300 uppercase"><?= substr($s['name'], 0, 1) ?></span>
                    <?php endif; ?>
                </div>
                <h3 class="font-bold text-gray-800 text-center mb-1"><?= htmlspecialchars($s['name']) ?></h3>
                <p class="text-xs text-gray-500 mb-1"><?= htmlspecialchars($s['phone'] ?? '') ?></p>
                <span class="px-2 py-1 <?= $s['status'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?> rounded text-[10px] uppercase font-bold mb-4">
                    <?= $s['status'] ? 'Đang hoạt động' : 'Tạm dừng' ?>
                </span>
                <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button onclick="editSupplier(<?= $s['id'] ?>)"
                        class="bg-blue-50 text-blue-600 p-2 rounded-lg hover:bg-blue-100">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteSupplier(<?= $s['id'] ?>)"
                        class="bg-red-50 text-red-600 p-2 rounded-lg hover:bg-red-100">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full py-12 text-center text-gray-500 italic">Chưa có nhà cung cấp nào được tạo.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Supplier -->
<div id="supplierModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-8 rounded-2xl w-[450px] shadow-2xl relative">
        <h3 class="text-xl font-bold mb-6" id="modalTitle">Thêm Nhà Cung Cấp Mới</h3>
        <form id="supplierForm" class="space-y-4" enctype="multipart/form-data">
            <input type="hidden" name="id" id="supplier_id">
            <div>
                <label class="block text-sm font-semibold mb-1">Tên nhà cung cấp *</label>
                <input type="text" name="name" class="w-full border p-3 rounded-lg outline-none focus:ring-2 focus:ring-blue-500" placeholder="VD: Công ty TNHH ABC..." required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Người liên hệ</label>
                    <input type="text" name="contact_name" class="w-full border p-3 rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Số điện thoại</label>
                    <input type="text" name="phone" class="w-full border p-3 rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Email</label>
                <input type="email" name="email" class="w-full border p-3 rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Địa chỉ</label>
                <textarea name="address" class="w-full border p-3 rounded-lg outline-none focus:ring-2 focus:ring-blue-500" rows="2"></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Logo / Ảnh đại diện</label>
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:bg-gray-50 transition-colors cursor-pointer relative">
                    <input type="file" name="logo" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" onchange="previewImg(this, 'suppPrev')">
                    <div id="suppPrev" class="hidden mx-auto h-20 w-20 border rounded-lg overflow-hidden bg-white mb-2">
                        <img src="" class="w-full h-full object-contain">
                    </div>
                    <div id="uploadPrompt">
                        <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 mb-2"></i>
                        <p class="text-xs text-gray-500">Kéo thả hoặc nhấp để tải ảnh</p>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Trạng thái</label>
                <select name="status" class="w-full border p-3 rounded-lg outline-none">
                    <option value="1">Hợp tác</option>
                    <option value="0">Tạm dừng</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeSupplierModal()" class="px-6 py-2 border rounded-lg">Hủy</button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-bold">Lưu lại</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openSupplierModal() { 
        document.getElementById('supplierForm').reset();
        document.getElementById('supplier_id').value = '';
        document.getElementById('modalTitle').innerText = 'Thêm Nhà Cung Cấp Mới';
        document.getElementById('suppPrev').classList.add('hidden');
        document.getElementById('uploadPrompt').classList.remove('hidden');
        document.getElementById('supplierModal').classList.remove('hidden'); 
    }
    function closeSupplierModal() { document.getElementById('supplierModal').classList.add('hidden'); }

    function previewImg(input, previewId) {
        const previewContainer = document.getElementById(previewId);
        const imgTag = previewContainer.querySelector('img');
        const prompt = document.getElementById('uploadPrompt');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imgTag.src = e.target.result;
                previewContainer.classList.remove('hidden');
                if (prompt) prompt.classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            imgTag.src = "";
            previewContainer.classList.add('hidden');
            if (prompt) prompt.classList.remove('hidden');
        }
    }

    document.getElementById('supplierForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const id = document.getElementById('supplier_id').value;

    const url = id 
    ? `/shop_giay_admin/public/?url=supplier/apiUpdate&id=${id}`
    : '/shop_giay_admin/public/?url=supplier/apiStore';

    fetch(url, {
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
    function editSupplier(id) {
    fetch(`/shop_giay_admin/public/?url=supplier/apiGet&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const s = data.data;
                document.getElementById('supplier_id').value = s.id;
                document.querySelector('input[name="name"]').value = s.name;
                document.querySelector('input[name="contact_name"]').value = s.contact_name || '';
                document.querySelector('input[name="phone"]').value = s.phone || '';
                document.querySelector('input[name="email"]').value = s.email || '';
                document.querySelector('textarea[name="address"]').value = s.address || '';
                document.querySelector('select[name="status"]').value = s.status;
                document.getElementById('modalTitle').innerText = 'Chỉnh Sửa Nhà Cung Cấp';

                if (s.logo) {
                    const preview = document.getElementById('suppPrev');
                    const imgTag = preview.querySelector('img');
                    imgTag.src = s.logo;
                    preview.classList.remove('hidden');
                    document.getElementById('uploadPrompt').classList.add('hidden');
                }

                document.getElementById('supplierModal').classList.remove('hidden');
            }
        });
}
function deleteSupplier(id) {
    if (!confirm('Bạn có chắc muốn xóa nhà cung cấp này?')) return;

    fetch(`/shop_giay_admin/public/?url=supplier/apiDelete&id=${id}`, {
        method: 'POST'
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.status === 'success') {
            location.reload();
        }
    });
}
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
