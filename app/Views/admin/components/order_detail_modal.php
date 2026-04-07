<!-- Order Detail Modal Shared Component -->
<div id="orderDetailModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden z-[200] items-center justify-center p-4">
    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-6xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <div class="grid grid-cols-12 h-[80vh]">
            <!-- Left Info Pane -->
            <div class="col-span-12 lg:col-span-8 p-10 overflow-y-auto">
                <div class="flex justify-between items-start mb-10">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-3xl font-black text-gray-800 tracking-tighter" id="modalOrderCode">#ORD-00000</h3>
                            <div id="modalStatusBadge"></div>
                        </div>
                        <p class="text-sm text-gray-400 font-bold uppercase tracking-widest flex items-center gap-2">
                            <i class="far fa-calendar-alt"></i> Chi tiết đơn hàng hệ thống
                        </p>
                    </div>
                    <button onclick="closeDetailModal()" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-gray-50 text-gray-400 hover:text-rose-600 hover:rotate-90 transition-all shadow-sm">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-8 mb-10">
                    <div class="bg-blue-50/50 p-6 rounded-[2rem] border border-blue-100/50">
                        <h4 class="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-4">Thông tin khách hàng</h4>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-sm text-blue-600">
                                    <i class="fas fa-user text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase">Họ và tên</p>
                                    <p class="font-bold text-gray-800" id="modalCustomerName">...</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-sm text-blue-600">
                                    <i class="fas fa-phone text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase">Số điện thoại</p>
                                    <p class="font-bold text-gray-800" id="modalCustomerPhone">...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-indigo-50/50 p-6 rounded-[2rem] border border-indigo-100/50">
                        <h4 class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-4">Giao hàng & Thanh toán</h4>
                        <div class="space-y-3">
                            <p class="text-xs font-bold text-gray-700 flex gap-2"><i class="fas fa-map-marker-alt text-indigo-400"></i> <span id="modalShippingAddress">...</span></p>
                            <div class="flex gap-4">
                                <p class="text-xs font-bold text-gray-700 flex gap-2"><i class="fas fa-credit-card text-indigo-400"></i> <span id="modalPaymentMethod">COD</span></p>
                                <p class="text-xs font-bold text-emerald-600 flex gap-2"><i class="fas fa-shield-check"></i> <span id="modalPaymentStatus">...</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Table -->
                <div class="mb-10">
                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 ml-2">Sản phẩm đã đặt</h4>
                    <table class="w-full">
                        <thead>
                            <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">
                                <th class="pb-4 text-left pl-4">Sản phẩm</th>
                                <th class="pb-4 text-center">SL</th>
                                <th class="pb-4 text-right">Đơn giá</th>
                                <th class="pb-4 text-right pr-4">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody id="modalProductList"></tbody>
                    </table>
                </div>

                <!-- Notes -->
                <div class="bg-gray-50/80 p-6 rounded-2xl border border-dashed border-gray-200">
                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 italic">Ghi chú từ khách hàng:</h4>
                    <p class="text-sm text-gray-600 italic font-medium" id="modalOrderNote">...</p>
                </div>

                <div class="mt-8 flex justify-end gap-10">
                    <div class="text-right space-y-2">
                        <div class="flex justify-between w-64 text-sm font-bold text-gray-500">
                            <span>Tạm tính:</span>
                            <span id="modalTotalAmount">0đ</span>
                        </div>
                        <div class="flex justify-between w-64 text-sm font-bold text-rose-500">
                            <span>Chiết khấu:</span>
                            <span id="modalDiscountAmount">0đ</span>
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
