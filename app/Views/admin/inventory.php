<?php 
$title = 'Quản Lý Nhập Kho (Inventory)';
require_once __DIR__ . '/layouts/header.php'; 
?>

<div class="grid grid-cols-3 gap-6">
    <!-- Left: List of Receipts -->
    <div class="col-span-2 space-y-6">
        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-800">Lịch sử Nhập Kho</h2>
                <button onclick="openReceiptModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    + Tạo Phiếu Nhập Mới
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="p-4 font-semibold text-gray-600 text-sm">Mã Phiếu</th>
                            <th class="p-4 font-semibold text-gray-600 text-sm">Nhà Cung Cấp</th>
                            <th class="p-4 font-semibold text-gray-600 text-sm">Tổng Tiền</th>
                            <th class="p-4 font-semibold text-gray-600 text-sm">Ngày Nhập</th>
                            <th class="p-4 font-semibold text-gray-600 text-sm">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($receipts)): ?>
                            <?php foreach($receipts as $r): ?>
                            <tr class="border-b hover:bg-gray-50 transition-colors">
                                <td class="p-4 font-bold text-blue-600">#<?= $r['receipt_code'] ?></td>
                                <td class="p-4 text-sm text-gray-700"><?= htmlspecialchars($r['supplier_name'] ?? 'N/A') ?></td>
                                <td class="p-4 text-sm font-bold text-red-600"><?= number_format($r['total_amount'], 0, ',', '.') ?>đ</td>
                                <td class="p-4 text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
                                <td class="p-4">
                                    <button onclick="viewReceipt(<?= $r['id'] ?>)" 
                                    class="text-blue-600 hover:underline text-sm">
                                    Chi tiết
                                    </button>
                                    <button onclick="deleteReceipt(<?= $r['id'] ?>)" 
                                    class="text-red-600 hover:underline text-sm">
                                    Xóa
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="p-4 text-center text-gray-500 italic">Chưa có thông tin nhập kho nào</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right: Inventory Logic Explanation -->
    <div class="col-span-1">
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 p-6 rounded-2xl text-white shadow-xl">
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                <i class="fas fa-info-circle"></i> VAI TRÒ CỦA KHO (INVENTORY)
            </h3>
            <div class="space-y-4 text-sm opacity-90 leading-relaxed">
                <p>
                    <strong>1. Cập nhật tồn kho chính xác:</strong> Số lượng hàng trong "Sản Phẩm" không nên tự điền bừa bãi. Khi bạn nhập hàng từ nhà cung cấp thông qua phiếu này, hệ thống sẽ tự động cộng tồn kho vào đúng biến thể giày đó.
                </p>
                <p>
                    <strong>2. Theo dõi giá vốn:</strong> Mỗi lần nhập hàng có thể có giá khác nhau. Việc lưu trữ phiếu nhập giúp bạn tính toán được lợi nhuận gộp chính xác sau này.
                </p>
                <p>
                    <strong>3. Minh bạch dòng hàng:</strong> Bạn luôn biết được ngày nào, ai đã nhập bao nhiêu đôi giày từ nhà cung cấp nào. Tránh thất thoát hàng hóa.
                </p>
                <div class="pt-4 border-t border-white/20">
                    <p class="text-[11px] italic">Hệ thống sử dụng **Database Triggers** để tự động cộng kho ngay khi phiếu nhập được lưu.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tạo Phiếu Nhập -->
<div id="receiptModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-8 rounded-2xl w-[600px] shadow-2xl relative">
        <h3 class="text-xl font-bold mb-6">Tạo Phiếu Nhập Hàng Mới</h3>
        <form id="receiptForm" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold mb-1">Nhà cung cấp</label>
                <select name="supplier_id" class="w-full border p-3 rounded-lg outline-none focus:ring-2 focus:ring-green-500">
                    <?php if(!empty($suppliers)): ?>
                        <?php foreach($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Mã Phiếu (Tự sinh nếu trống)</label>
                <input type="text" value="Tự sinh khi lưu" disabled class="border p-2 bg-gray-100">
            </div>
            <div class="p-4 bg-gray-50 rounded-xl border border-dashed text-center">
                <div class="border rounded-xl p-4">
    <h4 class="font-semibold mb-3">Danh sách sản phẩm nhập</h4>

    <table class="w-full text-sm" id="itemsTable">
        <thead>
            <tr class="bg-gray-50">
                <th class="p-2">Sản phẩm</th>
                <th class="p-2">Size</th>
                <th class="p-2">Màu</th>
                <th class="p-2">Số lượng</th>
                <th class="p-2">Giá nhập</th>
                <th></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <button type="button"
        onclick="addItemRow()"
        class="mt-3 text-green-600 font-bold">
        + Thêm sản phẩm
    </button>
</div>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeReceiptModal()" class="px-6 py-2 border rounded-lg">Đóng</button>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg font-bold">Lưu Phiếu</button>
            </div>
        </form>
    </div>
</div>
<!-- Modal Chi tiết -->
            <div id="detailModal" 
            class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">

            <div class="bg-white p-6 rounded-xl w-[700px]">

            <h3 class="text-lg font-bold mb-4">
            Chi tiết phiếu nhập
            </h3>

            <table class="w-full text-sm border">

            <thead class="bg-gray-100">
            <tr>
            <th class="p-2">Sản phẩm</th>
            <th class="p-2">Size</th>
            <th class="p-2">Màu</th>
            <th class="p-2">Số lượng</th>
            <th class="p-2">Giá nhập</th>
            </tr>
            </thead>

            <tbody id="detailTable"></tbody>

            </table>

            <div class="text-right mt-4">
            <button onclick="closeDetailModal()" 
            class="px-4 py-2 border rounded">
            Đóng
            </button>
            </div>

            </div>
            </div>

<script>
    function openReceiptModal() { document.getElementById('receiptModal').classList.remove('hidden'); }
    function closeReceiptModal() { document.getElementById('receiptModal').classList.add('hidden'); }
    
    let itemIndex = 0;
    
const products = <?= json_encode($products ?? []) ?>;

function addItemRow() {

    const row = `
    <tr>
    <td>
    <select name="items[${itemIndex}][product_id]" onchange="loadVariants(this,${itemIndex})" class="border p-2">
    <option value="">Chọn sản phẩm</option>
    ${products.map(p=>`<option value="${p.id}">${p.name}</option>`).join('')}
    </select>
    </td>

    <td>
    <select name="items[${itemIndex}][size]" class="border p-2"></select>
    </td>

    <td>
    <select name="items[${itemIndex}][color]" class="border p-2"></select>
    </td>

    <td>
    <input type="number" name="items[${itemIndex}][quantity]" class="border p-2 w-20">
    </td>

    <td>
    <input type="number" name="items[${itemIndex}][import_price]" class="border p-2 w-24">
    </td>

    <td>
    <button type="button" onclick="this.closest('tr').remove()">❌</button>
    </td>
    </tr>
    `;

document.querySelector("#itemsTable tbody").insertAdjacentHTML("beforeend",row);

itemIndex++;
}   
function loadVariants(select,index){

        const productId = select.value;

        fetch(`/shop_giay_admin/public/?url=product/getVariants&product_id=${productId}`)
        .then(res=>res.json())
        .then(data=>{

        let sizeSelect=document.querySelector(`[name="items[${index}][size]"]`);
        let colorSelect=document.querySelector(`[name="items[${index}][color]"]`);

        sizeSelect.innerHTML="";
        colorSelect.innerHTML="";

        let sizes=new Set();
        let colors=new Set();

        data.data.forEach(v=>{
        sizes.add(v.size);
        colors.add(v.color);
        });

        sizes.forEach(s=>{
        sizeSelect.innerHTML+=`<option value="${s}">${s}</option>`;
        });

        colors.forEach(c=>{
        colorSelect.innerHTML+=`<option value="${c}">${c}</option>`;
        });

        });

        }
document.getElementById('receiptForm').addEventListener('submit', function(e){

        e.preventDefault();

        if(!confirm("Xác nhận nhập kho? Sau khi lưu sẽ không thể chỉnh sửa.")){
        return;
        }

        const formData = new FormData(this);

        fetch('/shop_giay_admin/public/?url=inventory/apiImport',{
        method:'POST',
        body:formData
        })
        .then(res=>res.text())   // đọc text trước
        .then(text=>{

        try{

        let data = JSON.parse(text);

        alert(data.message);

        if(data.status=='success'){
        location.reload();
        }

        }catch(e){

        console.error("Server error:",text);
        alert("Lỗi server. Kiểm tra console.");

        }

        });

        });

            function viewReceipt(id){

                    fetch(`/shop_giay_admin/public/?url=inventory/getReceipt&id=${id}`)
                    .then(res=>res.json())
                    .then(data=>{

                    let html="";

                    data.items.forEach(i=>{
                    html+=`
                    <tr>
                    <td class="p-2">${i.product_name}</td>
                    <td class="p-2">${i.size}</td>
                    <td class="p-2">${i.color}</td>
                    <td class="p-2">${i.quantity}</td>
                    <td class="p-2">${Number(i.import_price).toLocaleString()}đ</td>
                    </tr>
                    `;
                    });

                    document.getElementById("detailTable").innerHTML=html;

                    document.getElementById("detailModal").classList.remove("hidden");

                    });

                }
        function closeDetailModal(){
        document.getElementById("detailModal").classList.add("hidden");
        }
function deleteReceipt(id){

if(!confirm("Xóa phiếu nhập này?")) return;

fetch(`/shop_giay_admin/public/?url=inventory/deleteReceipt&id=${id}`)
.then(res=>res.json())
.then(data=>{

alert(data.message);

if(data.status=='success'){
location.reload();
}

});

}
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
