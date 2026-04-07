<?php 
$title = 'Quản Lý Sản Phẩm';
require_once __DIR__ . '/layouts/header.php'; 
?>

<div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Danh sách Sản phẩm</h2>
        <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            + Thêm Sản Phẩm
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
           <thead>
<tr class="bg-gray-50 border-b border-gray-200 text-center">
    <th class="p-4 text-sm">ID</th>
    <th class="p-4 text-sm text-left">Sản phẩm</th>
    <th class="p-4 text-sm">Danh mục / NCC</th>
    <th class="p-4 text-sm">Giới tính</th>
    <th class="p-4 text-sm">Giá</th>
    <th class="p-4 text-sm">Kho</th>
    <th class="p-4 text-sm">Trạng thái</th>
    <th class="p-4 text-sm">Hành động</th>
</tr>
</thead>
            <tbody>
                <?php if(!empty($products)): ?>
                    <?php foreach($products as $p): ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors text-center">

    <!-- ID -->
    <td class="p-4 font-semibold text-gray-600">
        <?= $p['id'] ?>
    </td>

    <!-- Sản phẩm -->
    <td class="p-4 flex items-center gap-3 text-left">
        <img src="<?= $p['image_url'] ?? 'https://via.placeholder.com/50' ?>"
             class="w-12 h-12 object-cover rounded-lg shadow-sm border">
        <div>
            <div class="font-medium text-gray-800">
                <?= htmlspecialchars($p['name']) ?>
            </div>
            <?php if($p['is_featured']): ?>
                <div class="text-xs text-blue-500">★ Nổi bật</div>
            <?php endif; ?>
        </div>
    </td>

    <!-- Danh mục -->
    <td class="p-4 text-sm">
        <?= htmlspecialchars($p['category_name'] ?? 'N/A') ?>
        <div class="text-xs text-gray-400">
            <?= htmlspecialchars($p['supplier_name'] ?? '') ?>
        </div>
    </td>

    <!-- Giới tính -->
    <td class="p-4 text-sm">
        <?= $p['gender'] ?>
    </td>

    <!-- Giá -->
    <td class="p-4 font-bold text-red-600">
        <?= number_format($p['min_price'] ?? 0, 0, ',', '.') ?>đ
    </td>

    <!-- Kho -->
    <td class="p-4">
        <span class="<?= ($p['total_stock'] ?? 0) < 10 ? 'text-red-600 font-bold' : 'text-gray-600' ?>">
            <?= $p['total_stock'] ?? 0 ?>
        </span>
    </td>

    <!-- Trạng thái -->
    <td class="p-4">
        <span class="px-2 py-1 <?= $p['status'] == 'In Stock' ? 
            'bg-green-100 text-green-700' : 
            'bg-red-100 text-red-700' ?> rounded text-xs font-medium">
            <?= $p['status'] == 'Out of Stock' ? 'Hết hàng' : 'Còn hàng' ?>
        </span>
    </td>

    <!-- Hành động -->
    <td class="p-4">
        <button onclick="viewVariants(<?= $p['id'] ?>)"
            class="bg-gray-100 p-2 rounded hover:bg-gray-200 mr-1">
            <i class="fas fa-eye text-xs"></i>
        </button>

        <button onclick="editProduct(<?= $p['id'] ?>)"
            class="bg-blue-50 p-2 rounded hover:bg-blue-100 mr-1">
            <i class="fas fa-edit text-xs"></i>
        </button>

        <button onclick="deleteProduct(<?= $p['id'] ?>)"
            class="bg-red-50 p-2 rounded hover:bg-red-100">
            <i class="fas fa-trash text-xs"></i>
        </button>
    </td>
</tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="p-4 text-center text-gray-500">Chưa có dữ liệu sản phẩm</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 overflow-y-auto py-10">
    <div class="bg-white p-8 rounded-2xl w-[900px] shadow-2xl relative my-auto">
        <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times text-xl"></i>
        </button>
        <h3 class="text-2xl font-bold mb-6 text-gray-800 pb-4 border-b">Quản lý Sản phẩm & Kho vận</h3>
        
        <form id="productForm" class="grid grid-cols-3 gap-6" enctype="multipart/form-data">

            <!-- Left Side: Basic Info -->
             <input type="hidden" name="product_id" id="product_id">
            <div class="space-y-4 col-span-1 border-r pr-6">
                <h4 class="font-bold text-blue-600 text-sm uppercase">Thông tin cơ bản</h4>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tên sản phẩm *</label>
                    <input type="text" name="name" class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Danh mục</label>
                    <select name="category_id" class="w-full border border-gray-300 rounded-xl p-3 outline-none">
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Giới tính</label>
                        <select name="gender" class="w-full border border-gray-300 rounded-xl p-3 outline-none">
                            <option value="Men">Nam</option>
                            <option value="Women">Nữ</option>
                            <option value="Unisex">Unisex</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nổi bật</label>
                        <select name="is_featured" class="w-full border border-gray-300 rounded-xl p-3 outline-none">
                            <option value="0">Không</option>
                            <option value="1">Có </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Middle Side: Variant & Pricing (KHO) -->
           <div class="col-span-2 space-y-4">
    <h4 class="font-bold text-blue-600 text-sm uppercase">
        Danh sách biến thể (Size / Màu / Giá / Kho)
    </h4>

    <div class="overflow-x-auto">
        <table class="w-full text-sm border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2">Size</th>
                    <th class="p-2">Màu</th>
                    <th class="p-2">SKU</th>
                    <th class="p-2">Giá nhập</th>
                    <th class="p-2">Giá bán</th>
                    <th class="p-2">Tồn kho</th>
                    <th class="p-2">Xóa</th>
                </tr>
            </thead>
            <tbody id="variantTable"></tbody>
        </table>
    </div>

    <button type="button"
        onclick="addVariantRow()"
        class="bg-green-600 text-white px-4 py-2 rounded text-sm">
        + Thêm biến thể
    </button>
</div>

            <!-- Right Side: Multi-Images -->
            <div class="space-y-4 col-span-1">
    <h4 class="font-bold text-blue-600 text-sm uppercase">Thư viện Ảnh (Tải lên từ máy)</h4>
    <div class="space-y-4">
        <div class="bg-gray-50 p-3 rounded-lg border border-dashed border-gray-300">
            <label class="block text-xs font-bold text-gray-700 mb-2">Ảnh chính (Primary) *</label>
            <input type="file" name="primary_image" accept="image/*" 
                class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer"
                onchange="previewImg(this, 'prev1')" required>
            
            <div id="prev1" class="mt-2 h-24 w-24 hidden border-2 border-white shadow-sm rounded-lg overflow-hidden bg-gray-200">
                <img src="" class="w-full h-full object-cover">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 border-t pt-3">
            <div>
                <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Ảnh phụ 1</label>
                <input type="file" name="additional_images[]" accept="image/*" 
                    class="block w-full text-[10px] file:py-1 file:px-2 file:rounded file:border-0 file:bg-gray-100 cursor-pointer"
                    onchange="previewImg(this, 'prev2')">
                <div id="prev2" class="mt-2 h-16 w-16 hidden border rounded overflow-hidden">
                    <img src="" class="w-full h-full object-cover">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Ảnh phụ 2</label>
                <input type="file" name="additional_images[]" accept="image/*" 
                    class="block w-full text-[10px] file:py-1 file:px-2 file:rounded file:border-0 file:bg-gray-100 cursor-pointer"
                    onchange="previewImg(this, 'prev3')">
                <div id="prev3" class="mt-2 h-16 w-16 hidden border rounded overflow-hidden">
                    <img src="" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </div>
</div>

            <div class="col-span-3 pt-6 border-t flex justify-end gap-3">
                <button type="button" onclick="closeModal()" class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-medium hover:bg-gray-50 transition-all">Hủy</button>
                <button type="submit" class="px-8 py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all">LƯU SẢN PHẨM & KHO</button>
            </div>
        </form>
    </div>
</div>
<div id="variantViewModal" 
     class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">

    <div class="bg-white p-8 rounded-2xl w-[700px] shadow-2xl">
        <h3 class="text-xl font-bold mb-4">Danh sách biến thể</h3>

        <table class="w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2">Size</th>
                    <th class="p-2">Màu</th>
                    <th class="p-2">SKU</th>
                    <th class="p-2">Giá bán</th>
                    <th class="p-2">Tồn kho</th>
                </tr>
            </thead>
            <tbody id="variantViewTable"></tbody>
        </table>

        <div class="text-right mt-4">
            <button onclick="closeVariantView()" 
                class="px-4 py-2 bg-gray-600 text-white rounded">
                Đóng
            </button>
        </div>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('productForm').reset();
    document.getElementById('product_id').value = '';
    document.getElementById('variantTable').innerHTML = '';
    document.getElementById('productModal').classList.remove('hidden');
    document.querySelectorAll('#prev1,#prev2,#prev3').forEach(p=>{
    p.classList.add('hidden')
})
}    
function closeModal() { document.getElementById('productModal').classList.add('hidden'); }
    
    function previewImg(input, prevId) {
        const preview = document.getElementById(prevId);
        if(input.value) {
            preview.classList.remove('hidden');
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.classList.add('hidden');
        }
    }

    document.getElementById('productForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const productId = document.getElementById('product_id').value;

    let url = '/shop_giay_admin/public/?url=product/apiStore';

    if (productId) {
        url = `/shop_giay_admin/public/?url=product/apiUpdate/${productId}`;
    }

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Lưu thành công');
            location.reload();
        } else {
            alert(data.message);
        }
    });
});

    function deleteProduct(id) {
        if(confirm('Xóa sản phẩm này sẽ xóa toàn bộ biến thể và kho liên quan. Bạn chắc chắn?')) {
            fetch(`/shop_giay_admin/public/?url=product/apiDelete/${id}`, { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') location.reload();
                else alert(data.message);
            });
        }
    }

    function previewImg(input, previewId) {
    const previewContainer = document.getElementById(previewId);
    const imgTag = previewContainer.querySelector('img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            imgTag.src = e.target.result; // Gán dữ liệu ảnh vào thẻ img
            previewContainer.classList.remove('hidden'); // Hiển thị khung chứa
        }

        reader.readAsDataURL(input.files[0]); // Đọc tệp dưới dạng URL base64
    } else {
        imgTag.src = "";
        previewContainer.classList.add('hidden');
    }
}
function addVariantRow(variant = null) {
    const table = document.getElementById('variantTable');

    const row = `
    <tr class="border-b">
        <td>
            <input type="hidden" name="variants[id][]" value="${variant?.id ?? ''}">
            <input name="variants[size][]" class="border p-2 w-full rounded" 
                value="${variant?.size ?? ''}" required>
        </td>

        <td>
            <input name="variants[color][]" class="border p-2 w-full rounded" 
                value="${variant?.color ?? ''}" required>
        </td>

        <td>
            <input name="variants[sku][]" class="border p-2 w-full rounded" 
                value="${variant?.sku ?? ''}">
        </td>

        <td>
            <input type="number" name="variants[import_price][]" 
                class="border p-2 w-full rounded" 
                value="${variant?.import_price ?? 0}">
        </td>

        <td>
            <input type="number" name="variants[sale_price][]" 
                class="border p-2 w-full rounded" 
                value="${variant?.sale_price ?? 0}">
        </td>

        <td>
            <input type="number" name="variants[stock][]" 
                class="border p-2 w-full rounded" 
                value="${variant?.stock_quantity ?? 0}">
        </td>

        <td class="text-center">
            <button type="button"
                onclick="this.closest('tr').remove()"
                class="text-red-600 font-bold">
                X
            </button>
        </td>
    </tr>
    `;

    table.insertAdjacentHTML('beforeend', row);
}
function editProduct(id) {
    fetch(`/shop_giay_admin/public/?url=product/apiGetDetail&id=${id}`)
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {

            const p = data.data;

            openModal();
           

            document.getElementById('product_id').value = p.id;
            document.querySelector('[name="name"]').value = p.name ?? '';
            document.querySelector('[name="category_id"]').value = p.category_id ?? '';
            document.querySelector('[name="gender"]').value = p.gender ?? 'Men';
            document.querySelector('[name="is_featured"]').value = p.is_featured ?? 0;

            document.getElementById('variantTable').innerHTML = '';

            if (p.variants && p.variants.length > 0) {
                p.variants.forEach(v => addVariantRow(v));
            }
             if (p.images && p.images.length > 0) {

    const primary = p.images.find(img => img.is_primary == 1);

    if (primary) {
        const preview = document.getElementById('prev1');
        const img = preview.querySelector('img');

        img.src = primary.image_url;
        preview.classList.remove('hidden');
    }

    const others = p.images.filter(img => img.is_primary == 0);

    if (others[0]) {
        const preview2 = document.getElementById('prev2');
        preview2.querySelector('img').src = others[0].image_url;
        preview2.classList.remove('hidden');
    }

    if (others[1]) {
        const preview3 = document.getElementById('prev3');
        preview3.querySelector('img').src = others[1].image_url;
        preview3.classList.remove('hidden');
    }
    }
        }
    });
}
function viewVariants(id) {
    fetch(`/shop_giay_admin/public/?url=product/apiGetDetail&id=${id}`)
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {

            const table = document.getElementById('variantViewTable');
            table.innerHTML = '';

            data.data.variants.forEach(v => {
                table.innerHTML += `
                    <tr class="border-b">
                        <td class="p-2">${v.size}</td>
                        <td class="p-2">${v.color}</td>
                        <td class="p-2">${v.sku}</td>
                        <td class="p-2 text-red-600 font-bold">
                            ${Number(v.sale_price).toLocaleString()}đ
                        </td>
                        <td class="p-2">${v.stock_quantity}</td>
                    </tr>
                `;
            });

            document.getElementById('variantViewModal')
                .classList.remove('hidden');
        }
    });
}

function closeVariantView() {
    document.getElementById('variantViewModal')
        .classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
