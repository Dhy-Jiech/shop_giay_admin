<?php 
$title = 'Quản Lý Collection';
require_once __DIR__ . '/layouts/header.php'; 
?>

<div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Danh Sách Collection</h2>
        <button onclick="openCollectionModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            + Thêm Collection
        </button>
    </div>

    <!-- Danh sách Collection -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php if(!empty($collections)): ?>
            <?php foreach($collections as $col): ?>
            <div class="bg-gray-50 border rounded-2xl p-6 flex flex-col items-center group hover:border-blue-300 transition-all hover:shadow-md relative">
                
                <div class="w-full h-32 mb-4 bg-gray-200 rounded-xl overflow-hidden shadow-sm flex items-center justify-center border border-gray-100">
                    <?php if(!empty($col['banner_image'])): ?>
                        <img src="<?= htmlspecialchars('/shop_giay_admin/public' . $col['banner_image']) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <span class="text-gray-400 text-sm">No Image</span>
                    <?php endif; ?>
                </div>

                <h3 class="font-bold text-gray-800 text-center mb-1 text-lg"><?= htmlspecialchars($col['name']) ?></h3>
                <p class="text-xs text-gray-500 text-center mb-3 line-clamp-2"><?= htmlspecialchars($col['description'] ?? '') ?></p>
                
                <div class="text-sm text-gray-600 mb-2">
                    <span class="font-semibold"><?= $col['product_count'] ?? 0 ?></span> Sản phẩm
                </div>

                <span class="px-3 py-1 cursor-pointer <?= $col['status'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?> rounded-full text-xs font-bold mb-4" onclick="toggleStatus(<?= $col['id'] ?>)">
                    <?= $col['status'] ? 'Kích hoạt' : 'Tạm ẩn' ?>
                </span>
                
                <div class="text-xs text-gray-400 mb-4">
                    <?php if($col['start_date']) echo date('d/m/Y', strtotime($col['start_date'])); ?> 
                    - 
                    <?php if($col['end_date']) echo date('d/m/Y', strtotime($col['end_date'])); ?>
                </div>

                <div class="flex gap-2">
                    <button onclick="openProductModal(<?= $col['id'] ?>)" title="Quản lý sản phẩm"
                        class="bg-purple-50 text-purple-600 p-2 rounded-lg hover:bg-purple-100 transition-colors tooltip">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </button>
                    <!-- Chú ý: Edit / Delete có thể dùng Modal hoặc API -->
                    <button onclick="editCollection(<?= htmlspecialchars(json_encode($col)) ?>)" title="Chỉnh sửa"
                        class="bg-blue-50 text-blue-600 p-2 rounded-lg hover:bg-blue-100 transition-colors tooltip">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </button>
                    <form action="/shop_giay_admin/public/?url=collection/delete/<?= $col['id'] ?>" method="POST" onsubmit="return confirm('Bạn có chắc muốn xoá Collection này?');" class="inline">
                        <button type="submit" title="Xoá"
                            class="bg-red-50 text-red-600 p-2 rounded-lg hover:bg-red-100 transition-colors tooltip">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full py-12 text-center text-gray-500 italic">Chưa có Collection nào.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Thêm/Sửa Collection -->
<div id="collectionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-8 rounded-2xl w-full max-w-2xl shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <h3 id="modalTitle" class="text-xl font-bold mb-6">Thêm Collection Mới</h3>
        
        <form id="collectionForm" action="/shop_giay_admin/public/?url=collection/store" method="POST" enctype="multipart/form-data" class="space-y-4">
            
            <div>
                <label class="block text-sm font-semibold mb-1">Tên Collection *</label>
                <input type="text" name="name" id="col_name" class="w-full border p-3 rounded-lg outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            
            <div>
                <label class="block text-sm font-semibold mb-1">Mô tả</label>
                <textarea name="description" id="col_desc" rows="3" class="w-full border p-3 rounded-lg outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Ngày bắt đầu</label>
                    <input type="date" name="start_date" id="col_start" class="w-full border p-3 rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Ngày kết thúc</label>
                    <input type="date" name="end_date" id="col_end" class="w-full border p-3 rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Ảnh Banner</label>
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:bg-gray-50 transition-colors cursor-pointer relative">
                    <input type="file" name="banner_image" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" onchange="previewImg(this, 'colPrev')">
                    <div id="colPrev" class="hidden mx-auto h-32 w-full border rounded-lg overflow-hidden bg-white mb-2">
                        <img src="" class="w-full h-full object-cover">
                    </div>
                    <div id="uploadPromptC">
                        <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 mb-2"></i>
                        <p class="text-xs text-gray-500">Kéo thả hoặc nhấp để tải ảnh lên (Tỷ lệ 16:9 hoặc ngang)</p>
                    </div>
                </div>
            </div>

            <div>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="status" id="col_status" value="1" checked class="w-5 h-5 text-blue-600 rounded">
                    <span class="text-sm font-semibold">Kích hoạt ngay</span>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t mt-6">
                <button type="button" onclick="closeCollectionModal()" class="px-6 py-2 border rounded-lg hover:bg-gray-50 transition-colors">Hủy</button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition-colors">Lưu lại</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Quản Lý Sản Phẩm trong Collection -->
<div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-2xl w-full max-w-4xl shadow-2xl relative flex flex-col max-h-[90vh]">
        <h3 class="text-xl font-bold mb-4">Chọn sản phẩm cho Collection</h3>
        
        <div class="mb-4">
            <input type="text" id="searchProduct" placeholder="Tìm kiếm sản phẩm theo tên..." class="w-full border p-3 rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex-1 overflow-auto border rounded-xl bg-gray-50 p-4">
            <div id="productList" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <!-- Danh sách sản phẩm render qua JS -->
                <div class="text-center text-gray-500 w-full col-span-full py-4">Đang tải dữ liệu...</div>
            </div>
        </div>

        <div class="flex justify-between items-center mt-6 pt-4 border-t">
            <div class="text-sm text-gray-600">Đã chọn: <strong id="selectedCount">0</strong> sản phẩm</div>
            <div class="flex gap-3">
                <button type="button" onclick="closeProductModal()" class="px-6 py-2 border rounded-lg hover:bg-gray-50 transition-colors">Hủy</button>
                <button type="button" onclick="saveProducts()" class="px-6 py-2 bg-purple-600 text-white rounded-lg font-bold hover:bg-purple-700 transition-colors">Lưu danh sách</button>
            </div>
        </div>
    </div>
</div>

<script>
    // --- Utils ---
    function previewImg(input, previewId) {
        const previewContainer = document.getElementById(previewId);
        const imgTag = previewContainer.querySelector('img');
        const prompt = document.getElementById('uploadPromptC');
        
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

    function toggleStatus(id) {
        fetch(`/shop_giay_admin/public/?url=collection/toggleStatus/${id}`, { method:'POST' })
        .then(r => r.json())
        .then(res => {
            if(res.success) location.reload();
            else alert(res.message);
        });
    }

    // --- Modal Collection ---
    function openCollectionModal() { 
        document.getElementById('collectionForm').reset();
        document.getElementById('collectionForm').action = '/shop_giay_admin/public/?url=collection/store';
        document.getElementById('modalTitle').innerText = 'Thêm Collection Mới';
        
        document.getElementById('colPrev').classList.add('hidden');
        document.getElementById('uploadPromptC').classList.remove('hidden');
        
        document.getElementById('collectionModal').classList.remove('hidden'); 
    }
    
    function closeCollectionModal() { 
        document.getElementById('collectionModal').classList.add('hidden'); 
    }

    function editCollection(colStr) {
        const col = typeof colStr === 'string' ? JSON.parse(colStr) : colStr;
        
        document.getElementById('collectionForm').action = '/shop_giay_admin/public/?url=collection/update/' + col.id;
        document.getElementById('modalTitle').innerText = 'Chỉnh sửa Collection';
        
        document.getElementById('col_name').value = col.name;
        document.getElementById('col_desc').value = col.description || '';
        document.getElementById('col_start').value = col.start_date || '';
        document.getElementById('col_end').value = col.end_date || '';
        document.getElementById('col_status').checked = col.status == 1;

        if (col.banner_image) {
            document.getElementById('colPrev').querySelector('img').src = '/shop_giay_admin/public' + col.banner_image;
            document.getElementById('colPrev').classList.remove('hidden');
            document.getElementById('uploadPromptC').classList.add('hidden');
        } else {
            document.getElementById('colPrev').classList.add('hidden');
            document.getElementById('uploadPromptC').classList.remove('hidden');
        }

        document.getElementById('collectionModal').classList.remove('hidden');
    }

    // --- Modal Product Selection ---
    let currentCollectionId = null;
    let allProductsData = [];
    let selectedProducts = new Set();

    function openProductModal(id) {
        currentCollectionId = id;
        document.getElementById('productModal').classList.remove('hidden');
        document.getElementById('productList').innerHTML = '<div class="text-center text-gray-500 w-full col-span-full py-4">Đang tải dữ liệu...</div>';
        
        fetch(`/shop_giay_admin/public/?url=collection/getProductsData/${id}`)
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                allProductsData = res.allProducts;
                selectedProducts = new Set(res.selectedProductIds.map(Number));
                renderProducts();
            } else {
                alert("Lỗi tải dữ liệu sản phẩm");
                closeProductModal();
            }
        });
    }

    function closeProductModal() {
        document.getElementById('productModal').classList.add('hidden');
        currentCollectionId = null;
    }

    function toggleProductSelection(productId) {
        if (selectedProducts.has(productId)) {
            selectedProducts.delete(productId);
        } else {
            selectedProducts.add(productId);
        }
        updateSelectedCount();
        renderProducts(); // Rerender to show selected style
    }

    function updateSelectedCount() {
        document.getElementById('selectedCount').innerText = selectedProducts.size;
    }

    function renderProducts() {
        const container = document.getElementById('productList');
        const searchTerm = document.getElementById('searchProduct').value.toLowerCase();
        
        container.innerHTML = '';
        
        const filtered = allProductsData.filter(p => p.name.toLowerCase().includes(searchTerm));
        
        if (filtered.length === 0) {
            container.innerHTML = '<div class="text-center text-gray-500 w-full col-span-full py-4">Không tìm thấy sản phẩm nào.</div>';
            return;
        }

        filtered.forEach(p => {
            const isSelected = selectedProducts.has(Number(p.id));
            const card = document.createElement('div');
            card.className = `p-4 border rounded-xl cursor-pointer transition-all ${isSelected ? 'bg-purple-50 border-purple-500 shadow-sm' : 'bg-white hover:border-blue-300'}`;
            card.onclick = () => toggleProductSelection(Number(p.id));
            
            card.innerHTML = `
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 mt-1">
                        <input type="checkbox" ${isSelected ? 'checked' : ''} class="w-5 h-5 text-purple-600 rounded" onclick="event.stopPropagation(); toggleProductSelection(${p.id})">
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm line-clamp-2">${p.name}</h4>
                        <div class="text-xs text-gray-500 mt-1">ID: ${p.id}</div>
                    </div>
                </div>
            `;
            container.appendChild(card);
        });

        updateSelectedCount();
    }

    document.getElementById('searchProduct').addEventListener('input', renderProducts);

    function saveProducts() {
        if (!currentCollectionId) return;

        const productIds = Array.from(selectedProducts);
        
        fetch(`/shop_giay_admin/public/?url=collection/syncProducts/${currentCollectionId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ productIds: productIds })
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                alert("Đã lưu thành công!");
                location.reload();
            } else {
                alert("Lỗi: " + res.message);
            }
        });
    }

</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
