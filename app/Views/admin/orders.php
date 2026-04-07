<?php 
$title = 'Quản Lý Đơn Hàng';
require_once __DIR__ . '/layouts/header.php'; 

$currentStatus = $filters['status'] ?? 'All';
$searchKeyword = $filters['search'] ?? '';

// Giả định role từ session (Cần đồng bộ với AuthMiddleware)
$userRole = $_SESSION['user_role'] ?? 'Admin'; 
?>

<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Quản Lý Đơn Hàng</h2>
            <p class="text-sm text-gray-500">Xem, duyệt và theo dõi lộ trình đơn hàng hệ thống</p>
        </div>
        <div class="flex gap-3">
            <form action="" method="GET" class="flex gap-2" id="searchForm">
                <input type="hidden" name="url" value="admin/orders">
                <input type="hidden" name="status" value="<?= htmlspecialchars($currentStatus) ?>">
                <div class="relative">
<input 
id="searchInput"
type="text"
name="search"
placeholder="Mã đơn, tên, SĐT..."
value="<?= htmlspecialchars($searchKeyword) ?>"
onkeyup="handleSearchInput(this.value)"
class="pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 transition-all outline-none text-sm w-64">
<div id="searchSuggestions" 
class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-xl shadow-lg mt-1 hidden z-50">
</div>
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-blue-700 transition-all">Tìm</button>
                <?php if($searchKeyword): ?>
                    <a href="?url=admin/orders&status=<?= $currentStatus ?>" class="bg-gray-100 text-gray-500 px-4 py-2 rounded-xl text-sm font-bold hover:bg-gray-200 transition-all flex items-center">Bỏ lọc</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex gap-2 bg-white p-2 rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        <?php 
        $tabs = [
            'All' => 'Tất cả',
            'Pending' => 'Chờ duyệt',
            'Confirmed' => 'Đã duyệt',
            'Shipping' => 'Đang giao',
            'Completed' => 'Hoàn tất',
            'Cancelled' => 'Đã hủy'
        ];
        foreach($tabs as $val => $label):
            $active = ($currentStatus === $val) ? 'bg-blue-600 text-white' : 'text-gray-500 hover:bg-gray-50';
        ?>
            <a href="?url=admin/orders&status=<?= $val ?>&search=<?= urlencode($searchKeyword) ?>" 
               class="px-5 py-2 rounded-xl text-sm font-bold transition-all whitespace-nowrap <?= $active ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Table List -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider">Mã Đơn</th>
                    <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider">Khách hàng</th>
                    <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider">Tổng tiền</th>
                    <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider">Thanh toán</th>
                    <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider">Trạng thái</th>
                    <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider">Ngày đặt</th>
                    <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php if(!empty($orders)): ?>
                    <?php foreach($orders as $o): ?>
                    <tr class="hover:bg-blue-50/30 transition-colors group">
                        <td class="p-4">
                            <span class="font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-lg text-xs">#<?= htmlspecialchars($o['order_code'] ?? '') ?></span>
                        </td>
                        <td class="p-4 text-sm">
                            <div class="font-bold text-gray-800 text-left"><?= htmlspecialchars($o['customer_name'] ?? '') ?></div>
                            <div class="text-xs text-gray-400 text-left"><?= htmlspecialchars($o['customer_phone'] ?? '') ?></div>
                        </td>
                        <td class="p-4 text-sm font-black text-red-500">
                            <?= number_format($o['final_amount'] ?? 0, 0, ',', '.') ?>đ
                        </td>
                        <td class="p-4">
                            <?php if(($o['payment_status'] ?? '') == 'Paid'): ?>
                                <span class="bg-green-100 text-green-700 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase">Đã thanh toán</span>
                            <?php else: ?>
                                <span class="bg-yellow-100 text-yellow-700 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase">Chờ thanh toán</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4">
                            <?php 
                                $statusClasses = [
                                    'Pending' => 'bg-orange-100 text-orange-600',
                                    'Confirmed' => 'bg-blue-100 text-blue-600',
                                    'Shipping' => 'bg-purple-100 text-purple-600',
                                    'Completed' => 'bg-emerald-100 text-emerald-600',
                                    'Cancelled' => 'bg-rose-100 text-rose-600'
                                ];
                                $cls = $statusClasses[$o['order_status'] ?? 'Pending'] ?? 'bg-gray-100 text-gray-600';
                            ?>
                            <span class="<?= $cls ?> px-2.5 py-1 rounded-lg text-[11px] font-black uppercase tracking-tight">
                                <?= $o['order_status'] ?? 'N/A' ?>
                            </span>
                        </td>
                        <td class="p-4 text-xs text-gray-500">
                            <?= isset($o['created_at']) ? date('d/m/Y H:i', strtotime($o['created_at'])) : '' ?>
                        </td>
                        <td class="p-4 text-right flex justify-end gap-2">
                            <button onclick="viewOrderDetail(<?= $o['id'] ?>)" 
                                class="bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white p-2 rounded-xl transition-all h-10 w-24 flex items-center justify-center gap-1 font-bold text-xs uppercase shadow-sm">
                                <i class="fas fa-eye"></i> Xem
                            </button>
                            <?php if($userRole === 'Admin'): ?>
                                <button onclick="deleteOrder(<?= $o['id'] ?>)" 
                                    class="bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white p-2 rounded-xl transition-all h-10 w-10 flex items-center justify-center shadow-sm">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="p-10 text-center text-gray-400 italic">Không tìm thấy đơn hàng nào...</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Order Detail (Giữ nguyên component cũ nhưng bọc kỹ hơn) -->
<div id="orderDetailModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden flex items-center justify-center z-[100] p-4 transition-all overflow-y-auto">
    <div class="bg-white rounded-3xl w-full max-w-5xl shadow-2xl relative animate-in fade-in zoom-in duration-300">
        <button onclick="closeDetailModal()" class="absolute -top-3 -right-3 bg-white text-gray-400 hover:text-rose-500 w-10 h-10 rounded-full shadow-lg flex items-center justify-center border border-gray-100 transition-all z-10">
            <i class="fas fa-times text-lg"></i>
        </button>

        <div class="grid grid-cols-12 gap-0 overflow-hidden rounded-3xl">
            <!-- Left Info -->
            <div class="col-span-12 lg:col-span-8 p-8 border-r border-gray-100 h-full overflow-y-auto max-h-[90vh]">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-black text-gray-800 uppercase tracking-tighter">Chi tiết đơn <span id="modalOrderCode" class="text-blue-600">#---</span></h3>
                    <div id="modalStatusBadge"></div>
                </div>

                <div class="grid grid-cols-2 gap-8 mb-8 text-left">
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest border-b pb-2 text-left">Thông tin khách hàng</h4>
                        <div class="space-y-1">
                            <p id="modalCustomerName" class="font-bold text-gray-800 text-lg text-left"></p>
                            <p id="modalCustomerPhone" class="text-gray-500 font-medium text-left"></p>
                            <p id="modalShippingAddress" class="text-sm text-gray-600 italic mt-2 text-left"></p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest border-b pb-2 text-left">Thanh toán & Ghi chú</h4>
                        <div class="space-y-1 text-sm text-left">
                            <p class="font-bold">Phương thức: <span id="modalPaymentMethod" class="text-gray-600 font-medium">COD</span></p>
                            <div class="flex items-center gap-2">
                                <p class="font-bold">Trạng thái TT: <span id="modalPaymentStatus" class="text-gray-600 font-medium"></span></p>
                                <button id="btnUpdatePayment" onclick="togglePaymentStatus()" class="text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded border border-blue-100 hover:bg-blue-600 hover:text-white transition-all font-bold">
                                    <i class="fas fa-edit"></i> Đổi
                                </button>
                            </div>
                            <p class="mt-2 text-gray-500 italic" id="modalOrderNote"></p>
                        </div>
                    </div>
                </div>

                <!-- Product List -->
                <div class="bg-gray-50/50 rounded-2xl border border-gray-100 overflow-hidden text-left mb-6">
                    <table class="w-full text-left">
                        <thead class="bg-white">
                            <tr>
                                <th class="p-4 text-xs font-bold text-gray-400 uppercase">Sản phẩm</th>
                                <th class="p-4 text-xs font-bold text-gray-400 uppercase text-center">SL</th>
                                <th class="p-4 text-xs font-bold text-gray-400 uppercase text-right">Đơn giá</th>
                                <th class="p-4 text-xs font-bold text-gray-400 uppercase text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody id="modalProductList"></tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end">
                    <div class="w-64 space-y-2">
                        <div class="flex justify-between text-gray-500 font-medium">
                            <span>Tạm tính:</span>
                            <span id="modalTotalAmount">0đ</span>
                        </div>
                        <div class="flex justify-between text-rose-500 font-medium">
                            <span>Chiết khấu:</span>
                            <span id="modalDiscountAmount">-0đ</span>
                        </div>
                        <div class="flex justify-between text-xl font-black text-gray-800 border-t pt-2">
                            <span>TỔNG CỘNG:</span>
                            <span id="modalFinalAmount" class="text-red-600 text-2xl">0đ</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Timeline & Action -->
            <div class="col-span-12 lg:col-span-4 bg-gray-50/30 p-8 h-full">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6 pb-2 border-b text-left">Lịch sử & Hành động</h4>
                
                <!-- Action Buttons -->
                <div id="modalActionArea" class="space-y-3 mb-8"></div>

                <!-- Timeline -->
                <div class="relative pl-6 space-y-6 before:content-[''] before:absolute before:left-[7px] before:top-2 before:bottom-0 before:w-0.5 before:bg-gray-200 text-left" id="modalTimeline"></div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentOrderId = null;
    let searchDebounceTimer = null;

    function handleSearchInput(val) {
        clearTimeout(searchDebounceTimer);
        const suggestionsBox = document.getElementById('searchSuggestions');
        if (val.length < 2) {
            suggestionsBox.innerHTML = '';
            suggestionsBox.classList.add('hidden');
            return;
        }

        searchDebounceTimer = setTimeout(() => {
            fetch(`/shop_giay_admin/public/?url=order/apiSearchSuggestions&keyword=${encodeURIComponent(val)}`)
                .then(res => res.json())
                .then(resp => {
                    if (resp.status === 'success' && resp.data.length > 0) {
                        suggestionsBox.innerHTML = '';
                        resp.data.forEach(item => {
                            const div = document.createElement('div');
                            div.className = "p-3 hover:bg-blue-50 cursor-pointer border-b border-gray-50 last:border-0 transition-colors";
                            div.innerHTML = `
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-xs font-bold text-blue-600">#${item.order_code}</p>
                                        <p class="text-sm font-bold text-gray-800">${item.customer_name}</p>
                                    </div>
                                    <p class="text-[10px] text-gray-400 font-bold">${item.customer_phone}</p>
                                </div>
                            `;
                            div.onclick = () => {
                                document.getElementById('searchInput').value = item.order_code;
                                document.getElementById('searchForm').submit();
                            };
                            suggestionsBox.appendChild(div);
                        });
                        suggestionsBox.classList.remove('hidden');
                    } else {
                        suggestionsBox.innerHTML = '';
                        suggestionsBox.classList.add('hidden');
                    }
                });
        }, 300);
    }

    // Đóng dropdown khi click ra ngoài
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#searchForm')) {
            document.getElementById('searchSuggestions').classList.add('hidden');
        }
    });

    function viewOrderDetail(id) {
        currentOrderId = id;
        fetch(`/shop_giay_admin/public/?url=order/apiDetail&id=${id}`)
            .then(res => res.json())
            .then(resp => {
                if(resp.status === 'success') {
                    const data = resp.data;
                    const order = data.order;
                    document.getElementById('modalOrderCode').innerText = '#' + (order.order_code || '');
                    document.getElementById('modalCustomerName').innerText = order.customer_name || '';
                    document.getElementById('modalCustomerPhone').innerText = order.customer_phone || '';
                    document.getElementById('modalShippingAddress').innerText = order.shipping_address || '';
                    document.getElementById('modalPaymentMethod').innerText = order.payment_method || 'COD';
                    document.getElementById('modalPaymentStatus').innerText = order.payment_status || '';
                    document.getElementById('modalOrderNote').innerText = order.note || 'Không có ghi chú';
                    document.getElementById('modalTotalAmount').innerText = Number(order.total_amount || 0).toLocaleString() + 'đ';
                    document.getElementById('modalDiscountAmount').innerText = '-' + Number(order.discount_amount || 0).toLocaleString() + 'đ';
                    document.getElementById('modalFinalAmount').innerText = Number(order.final_amount || 0).toLocaleString() + 'đ';

                    const prodTable = document.getElementById('modalProductList');
                    prodTable.innerHTML = '';
                    if(data.items) {
                        data.items.forEach(item => {
                            prodTable.innerHTML += `
                            <tr class="border-b border-gray-100 group">
                                <td class="p-4 text-left">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-white border border-gray-100 rounded-lg shrink-0 flex items-center justify-center shadow-sm">
                                            <i class="fas fa-shoe-prints text-gray-300"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-800 text-left">${item.product_name}</p>
                                            <p class="text-[10px] text-gray-400 font-bold uppercase text-left">Size: ${item.size} | Màu: ${item.color}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 text-center text-sm font-bold text-gray-600">${item.quantity}</td>
                                <td class="p-4 text-right text-sm font-medium text-gray-500">${Number(item.unit_price).toLocaleString()}đ</td>
                                <td class="p-4 text-right text-sm font-black text-gray-800">${Number(item.total_price).toLocaleString()}đ</td>
                            </tr>`;
                        });
                    }

                    const timeline = document.getElementById('modalTimeline');
                    timeline.innerHTML = '';
                    if(data.history) {
                        data.history.forEach(h => {
                            timeline.innerHTML += `
                            <div class="relative">
                                <div class="absolute -left-[23.5px] top-1 w-4 h-4 bg-white border-2 border-blue-600 rounded-full z-10 shadow-sm"></div>
                                <p class="text-[11px] font-black uppercase text-blue-600 tracking-tight">${h.status}</p>
                                <p class="text-xs font-bold text-gray-800">${h.note}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5 font-medium italic">${h.created_at} - ${h.user_name || 'Hệ thống'}</p>
                            </div>`;
                        });
                    }

                    const statusClasses = {
                        'Pending': 'bg-orange-100 text-orange-600',
                        'Confirmed': 'bg-blue-100 text-blue-600',
                        'Shipping': 'bg-purple-100 text-purple-600',
                        'Completed': 'bg-emerald-100 text-emerald-600',
                        'Cancelled': 'bg-rose-100 text-rose-600'
                    };
                    const badgeCls = statusClasses[order.order_status] || 'bg-gray-100 text-gray-600';
                    document.getElementById('modalStatusBadge').innerHTML = `<span class="${badgeCls} px-4 py-1.5 rounded-full text-[11px] font-black uppercase tracking-widest shadow-sm">${order.order_status}</span>`;

                    updateActionButtons(order.order_status);
                    document.getElementById('orderDetailModal').classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }
            });
    }

    function updateActionButtons(status) {
        const area = document.getElementById('modalActionArea');
        area.innerHTML = '';
        const btnClass = "w-full py-3 rounded-2xl font-black text-[11px] uppercase tracking-widest transition-all shadow-sm flex items-center justify-center gap-2";
        if(status === 'Pending') {
            area.innerHTML += `<button onclick="changeStatus('Confirmed', 'Duyệt đơn hàng')" class="${btnClass} bg-blue-600 text-white hover:bg-blue-700 shadow-blue-100 shadow-lg"><i class="fas fa-check"></i> Duyệt đơn</button>`;
            area.innerHTML += `<button onclick="changeStatus('Cancelled', 'Hủy đơn hàng bởi Admin')" class="${btnClass} bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white border border-rose-100"><i class="fas fa-ban"></i> Hủy đơn</button>`;
        } else if(status === 'Confirmed') {
            area.innerHTML += `<button onclick="changeStatus('Shipping', 'Bắt đầu giao hàng')" class="${btnClass} bg-purple-600 text-white hover:bg-purple-700 shadow-purple-100 shadow-lg"><i class="fas fa-truck"></i> Giao hàng</button>`;
        } else if(status === 'Shipping') {
            area.innerHTML += `<button onclick="changeStatus('Completed', 'Giao hàng thành công')" class="${btnClass} bg-emerald-600 text-white hover:bg-emerald-700 shadow-emerald-100 shadow-lg"><i class="fas fa-check-double"></i> Hoàn tất</button>`;
        } else {
            if (status === 'Completed') {
                area.innerHTML += `<a href="/shop_giay_admin/public/?url=order/printInvoice/${currentOrderId}" target="_blank" class="${btnClass} bg-blue-600 text-white hover:bg-blue-700 shadow-blue-100 shadow-lg"><i class="fas fa-print"></i> In hóa đơn</a>`;
            }
            area.innerHTML += `<div class="bg-gray-100/50 p-4 rounded-2xl text-center text-xs text-gray-400 font-bold uppercase tracking-widest italic border border-dashed border-gray-200">Luồng đơn đã kết thúc</div>`;
        }
    }

    function changeStatus(newStatus, defaultNote) {
        const note = prompt("Ghi chú thay đổi (không bắt buộc):", defaultNote);
        if(note === null) return;
        const formData = new FormData();
        formData.append('status', newStatus);
        formData.append('note', note);
        fetch(`/shop_giay_admin/public/?url=order/apiUpdateStatus&id=${currentOrderId}`, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(resp => {
            if(resp.status === 'success') {
                viewOrderDetail(currentOrderId);
            } else { alert('Lỗi: ' + resp.message); }
        });
    }

    function togglePaymentStatus() {
        const currentStatus = document.getElementById('modalPaymentStatus').innerText;
        const newStatus = (currentStatus === 'Paid') ? 'Pending' : 'Paid';
        const statusText = (newStatus === 'Paid') ? 'Đã thanh toán' : 'Chờ thanh toán';
        
        if(!confirm(`Xác nhận đổi trạng thái thanh toán thành: ${statusText}?`)) return;

        const formData = new FormData();
        formData.append('status', newStatus);
        formData.append('note', 'Cập nhật trạng thái thanh toán từ Admin');

        fetch(`/shop_giay_admin/public/?url=order/apiUpdatePaymentStatus&id=${currentOrderId}`, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(resp => {
            if(resp.status === 'success') {
                viewOrderDetail(currentOrderId);
            } else { alert('Lỗi: ' + resp.message); }
        });
    }

    function deleteOrder(id) {
        if(confirm('CẢNH BÁO: Bạn có chắc chắn muốn XÓA VĨNH VIỄN đơn hàng này và toàn bộ dữ liệu liên quan? Không thể hoàn tác!')) {
            fetch(`/shop_giay_admin/public/?url=order/apiDelete/${id}`, { method: 'POST' })
            .then(res => res.json())
            .then(resp => {
                if(resp.status === 'success') {
                    alert('Đã xóa đơn hàng thành công!');
                    location.reload();
                } else { alert('Lỗi: ' + resp.message); }
            });
        }
    }

    function closeDetailModal() {
        document.getElementById('orderDetailModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        location.reload();
    }
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
