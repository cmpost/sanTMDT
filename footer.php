<?php /** Footer dùng chung cho các trang công khai. Cần $__settings từ header.php. */ ?>
<footer class="site">
  <div class="wrap">
    <div>
      <div class="fbrand">
        <div class="mark" style="width:36px;height:36px;border-radius:9px;"><svg viewBox="0 0 24 24" fill="none" style="width:20px;height:20px;"><path d="M3 7l9-4 9 4v10l-9 4-9-4V7z" stroke="#0A1E42" stroke-width="1.6" stroke-linejoin="round"/></svg></div>
        <b style="color:#fff;font-family:'Be Vietnam Pro';font-size:14px;">BƯU ĐIỆN CÀ MAU</b>
      </div>
      <p>Địa chỉ: <?= e($__settings['address'] ?? '') ?><br>
      Hotline: <?= e($__settings['hotline'] ?? '') ?> · Email: <?= e($__settings['email'] ?? '') ?></p>
    </div>
    <div>
      <h4>Chính sách</h4>
      <ul>
        <li>Chính sách đổi trả sản phẩm</li>
        <li>Chính sách bảo hành</li>
        <li>Chính sách vận chuyển &amp; giao nhận</li>
        <li>Chính sách thanh toán</li>
        <li>Bảo mật thông tin khách hàng</li>
      </ul>
    </div>
    <div>
      <h4>Về chúng tôi</h4>
      <ul>
        <li><a href="news.php">Tin tức &amp; sự kiện</a></li>
        <li>Giới thiệu Bưu điện Cà Mau</li>
        <li>Tuyển dụng</li>
        <li>Liên hệ hợp tác</li>
      </ul>
    </div>
    <div>
      <h4>Kết nối với chúng tôi</h4>
      <p>Fanpage: <?= e($__settings['fanpage'] ?? '') ?></p>
      <p>Hotline: <?= e($__settings['hotline'] ?? '') ?></p>
    </div>
  </div>
  <div class="foot-bottom">© <?= date('Y') ?> Bưu điện tỉnh Cà Mau — Thành viên Tổng công ty Bưu điện Việt Nam (Vietnam Post).</div>
</footer>
</body>
</html>
