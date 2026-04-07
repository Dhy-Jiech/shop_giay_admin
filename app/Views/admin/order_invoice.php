<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Hóa Đơn' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
            body { background-color: white; }
            .print-container { border: none; box-shadow: none; width: 100%; max-width: 100%; margin: 0; padding: 0; }
        }
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
    </style>
</head>
<body class="py-10">
    <div class="max-w-4xl mx-auto bg-white p-10 rounded-2xl shadow-sm border border-gray-100 print-container">
        <!-- Header -->
        <div class="flex justify-between items-start border-b-2 border-gray-100 pb-8 mb-8">
            <div>
                <h1 class="text-3xl font-black text-blue-600 mb-2">ĐỚ HA</h1>
                <p class="text-gray-500 text-sm">Cửa hàng giày thể thao & phụ kiện</p>
                <div class="mt-4 text-sm text-gray-600">
                    <p><i class="fas fa-map-marker-alt mr-2"></i> 123 Đường ABC, Quận XYZ, TP.HCM</p>
                    <p><i class="fas fa-phone mr-2"></i> Hotline: 0123 456 789</p>
                    <p><i class="fas fa-globe mr-2"></i> Website: www.doha.com</p>
                </div>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-bold text-gray-800 uppercase mb-2">Hóa Đơn Bán Hàng</h2>
                <p class="text-gray-500 font-bold">Mã đơn: <span class="text-blue-600">#<?= $order['order_code'] ?></span></p>
                <p class="text-gray-500 text-sm">Ngày lập: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-2 gap-10 mb-10">
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 border-b pb-2">Thông tin khách hàng</h3>
                <div class="space-y-1">
                    <p class="font-bold text-gray-800 text-lg"><?= $order['customer_name'] ?></p>
                    <p class="text-gray-600"><?= $order['customer_phone'] ?></p>
                    <p class="text-gray-500 text-sm italic mt-2"><?= $order['shipping_address'] ?></p>
                </div>
            </div>
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 border-b pb-2">Thông tin thanh toán</h3>
                <div class="space-y-1 text-sm">
                    <p class="font-bold text-gray-800">Phương thức: <span class="text-gray-600 font-medium"><?= $order['payment_method'] ?></span></p>
                    <p class="font-bold text-gray-800">Trạng thái: 
                        <?php if ($order['payment_status'] == 'Paid'): ?>
                            <span class="text-green-600 font-bold uppercase">Đã thanh toán</span>
                        <?php else: ?>
                            <span class="text-orange-600 font-bold uppercase">Chờ thanh toán</span>
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($order['note'])): ?>
                        <p class="mt-2 text-gray-500 italic">Ghi chú: <?= $order['note'] ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Product Table -->
        <div class="border border-gray-100 rounded-xl overflow-hidden mb-8">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="p-4 text-xs font-bold text-gray-400 uppercase">Sản phẩm</th>
                        <th class="p-4 text-xs font-bold text-gray-400 uppercase text-center">SL</th>
                        <th class="p-4 text-xs font-bold text-gray-400 uppercase text-right">Đơn giá</th>
                        <th class="p-4 text-xs font-bold text-gray-400 uppercase text-right">Thành tiền</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="p-4">
                            <div>
                                <p class="font-bold text-gray-800"><?= $item['product_name'] ?></p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase">Size: <?= $item['size'] ?> | Màu: <?= $item['color'] ?></p>
                            </div>
                        </td>
                        <td class="p-4 text-center font-bold text-gray-600"><?= $item['quantity'] ?></td>
                        <td class="p-4 text-right text-gray-500"><?= number_format($item['unit_price'], 0, ',', '.') ?>đ</td>
                        <td class="p-4 text-right font-bold text-gray-800"><?= number_format($item['total_price'], 0, ',', '.') ?>đ</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="flex justify-end mb-12">
            <div class="w-72 space-y-3">
                <div class="flex justify-between text-gray-500 font-medium">
                    <span>Tạm tính:</span>
                    <span><?= number_format($order['total_amount'], 0, ',', '.') ?>đ</span>
                </div>
                <div class="flex justify-between text-rose-500 font-medium pb-2 border-b border-gray-100">
                    <span>Chiết khấu:</span>
                    <span>-<?= number_format($order['discount_amount'], 0, ',', '.') ?>đ</span>
                </div>
                <div class="flex justify-between text-xl font-black text-gray-800 pt-2">
                    <span>TỔNG CỘNG:</span>
                    <span class="text-red-600"><?= number_format($order['final_amount'], 0, ',', '.') ?>đ</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="grid grid-cols-2 gap-10 text-center mt-20 italic text-sm text-gray-400">
            <div>
                <p class="mb-20">Người mua hàng</p>
                <p class="font-bold"><?= $order['customer_name'] ?></p>
            </div>
            <div>
                <p class="mb-20">Người lập hóa đơn</p>
                <p class="font-bold">Nhân viên bán hàng</p>
            </div>
        </div>

        <!-- Action Button -->
        <div class="mt-10 pt-10 border-t border-gray-100 flex justify-center gap-4 no-print">
            <button onclick="window.print()" class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 transition-all flex items-center gap-2 shadow-lg shadow-blue-100">
                <i class="fas fa-print"></i> In hóa đơn
            </button>
            <button onclick="window.close()" class="bg-gray-100 text-gray-600 px-8 py-3 rounded-xl font-bold hover:bg-gray-200 transition-all">
                Đóng
            </button>
        </div>
    </div>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</body>
</html>
