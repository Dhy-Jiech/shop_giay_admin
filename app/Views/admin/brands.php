<?php 
$title = 'Quản Lý Thương Hiệu';
require_once __DIR__ . '/layouts/header.php'; 
?>

<div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Danh Mục Thương Hiệu</h2>
        <button onclick="openBrandModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            + Thêm Thương Hiệu
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php if(!empty($brands)): ?>
            <?php foreach($brands as $b): ?>
            <div class="bg-gray-50 border rounded-2xl p-6 flex flex-col items-center group hover:border-blue-300 transition-all hover:shadow-md">
                <div class="w-20 h-20 mb-4 bg-white rounded-xl overflow-hidden shadow-sm flex items-center justify-center p-2 border border-gray-100">
                    <?php if(!empty($b['logo'])): ?>
                        <img src="<?= htmlspecialchars($b['logo']) ?>" class="max-w-full max-h-full object-contain">
                    <?php else: ?>
                        <span class="text-2xl font-bold text-gray-300 uppercase"><?= substr($b['name'], 0, 1) ?></span>
                    <?php endif; ?>
                </div>
                <h3 class="font-bold text-gray-800 text-center mb-1"><?= htmlspecialchars($b['name']) ?></h3>
                <span class="px-2 py-1 <?= $b['status'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?> rounded text-[10px] uppercase font-bold mb-4">
                    <?= $b['status'] ? 'Đang hoạt động' : 'Tạm dừng' ?>
                </span>
                <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button onclick="editBrand(<?= $b['id'] ?>)"
    class="bg-blue-50 text-blue-600 p-2 rounded-lg hover:bg-blue-100">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteBrand(<?= $b['id'] ?>)"
    class="bg-red-50 text-red-600 p-2 rounded-lg hover:bg-red-100">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full py-12 text-center text-gray-500 italic">Chưa có thương hiệu nào được tạo.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Brand -->
<div id="brandModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-8 rounded-2xl w-[400px] shadow-2xl relative">
        <h3 class="text-xl font-bold mb-6">Thêm Thương Hiệu Mới</h3>
        <form id="brandForm" class="space-y-4" enctype="multipart/form-data">
            <input type="hidden" name="id" id="brand_id">
            <div>
                <label class="block text-sm font-semibold mb-1">Tên thương hiệu *</label>
                <input type="text" name="name" class="w-full border p-3 rounded-lg outline-none focus:ring-2 focus:ring-blue-500" placeholder="VD: Nike, Adidas..." required>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Logo Thương Hiệu *</label>
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:bg-gray-50 transition-colors cursor-pointer relative">
                    <input type="file" name="logo" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" onchange="previewImg(this, 'brandPrev')">
                    <div id="brandPrev" class="hidden mx-auto h-20 w-20 border rounded-lg overflow-hidden bg-white mb-2">
                        <img src="" class="w-full h-full object-contain">
                    </div>
                    <div id="uploadPrompt">
                        <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 mb-2"></i>
                        <p class="text-xs text-gray-500">Kéo thả hoặc nhấp để tải ảnh logo</p>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Trạng thái</label>
                <select name="status" class="w-full border p-3 rounded-lg outline-none">
                    <option value="1">Kinh doanh</option>
                    <option value="0">Ngừng kinh doanh</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeBrandModal()" class="px-6 py-2 border rounded-lg">Hủy</button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-bold">Lưu lại</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openBrandModal() { document.getElementById('brandModal').classList.remove('hidden'); }
    function closeBrandModal() { document.getElementById('brandModal').classList.add('hidden'); }

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

    document.getElementById('brandForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const id = document.getElementById('brand_id').value;

    const url = id 
    ? `/shop_giay_admin/public/?url=brand/apiUpdate&id=${id}`
    : '/shop_giay_admin/public/?url=brand/apiStore';

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
    function editBrand(id) {

    fetch(`/shop_giay_admin/public/?url=brand/apiGet&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {

                const brand = data.data;

                document.getElementById('brand_id').value = brand.id;
                document.querySelector('input[name="name"]').value = brand.name;
                document.querySelector('select[name="status"]').value = brand.status;

                if (brand.logo) {
                    const preview = document.getElementById('brandPrev');
                    const imgTag = preview.querySelector('img');
                    imgTag.src = brand.logo;
                    preview.classList.remove('hidden');
                    document.getElementById('uploadPrompt').classList.add('hidden');
                }

                openBrandModal();
            }
        })
        .catch(err => console.error(err));
}
function deleteBrand(id) {
    if (!confirm('Bạn có chắc muốn xóa thương hiệu này?')) return;

    fetch(`/shop_giay_admin/public/?url=brand/apiDelete&id=${id}`, {
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
