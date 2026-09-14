# FoodGo - Ma trận kiểm thử

## Chạy tự động

Trên Laragon, dùng PHP 8.3:

```powershell
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' vendor\bin\phpunit
```

Kết quả chuẩn hiện tại: **44 tests, 254 assertions**.

## Phạm vi kiểm thử

| Tệp kiểm thử | Số test | Phạm vi |
|---|---:|---|
| `BusinessValidationTest.php` | 14 | Validation đăng ký, tồn kho, thông báo hết sản phẩm, giỏ rỗng, giờ nhận, số dư, thanh toán lặp, trạng thái sai, rate limit, API chưa xác thực |
| `FoodGoApiTest.php` | 7 | Luồng REST API đặt món, thanh toán, hoàn tiền, phân quyền, đăng nhập mã sinh viên, đánh giá, báo cáo |
| `WebAuthenticationTest.php` | 6 | Đăng ký, đăng nhập session, tài khoản khóa, dashboard theo vai trò, CSRF 419, đăng xuất |
| `WebOrderingTest.php` | 7 | Chọn nhiều món, giỏ hàng, đặt món, thanh toán, hủy đơn, hoàn tồn kho, đánh giá món |
| `WebStaffOrderTest.php` | 4 | Nhìn thấy đơn mới, nhận/từ chối, lý do, hoàn tiền, chuỗi trạng thái chế biến |
| `WebAdminTest.php` | 6 | CRUD món, tạo/sửa/khóa tài khoản, tự bảo vệ admin, báo cáo doanh thu và món bán chạy |

## Luồng nghiệm thu thủ công

### Khách hàng

1. Đăng ký tại `/register` và đăng nhập.
2. Chọn ít nhất hai món, kiểm tra tổng giỏ hàng.
3. Chọn giờ nhận, đặt đơn và thanh toán Ví FoodGo.
4. Kiểm tra số dư chỉ bị trừ một lần.
5. Theo dõi các trạng thái do nhân viên cập nhật.
6. Đánh giá món sau khi đơn hoàn tất.

### Nhân viên

1. Đăng nhập tài khoản nhân viên và mở `/staff/dashboard`.
2. Xác nhận đơn chưa thanh toán chỉ được xem.
3. Với đơn đã thanh toán, thử nhận đơn hoặc từ chối kèm lý do.
4. Chuyển lần lượt `Đang chuẩn bị` → `Sẵn sàng nhận` → `Hoàn tất`.
5. Nếu từ chối, kiểm tra ví khách và tồn kho được hoàn lại.

### Quản trị viên

1. Đăng nhập và mở `/admin/dashboard`.
2. Thêm, sửa, ngừng bán rồi bán lại một món.
3. Tạo tài khoản nhân viên, sửa vai trò và khóa/mở tài khoản.
4. Kiểm tra admin không thể tự hạ quyền hoặc tự khóa.
5. Chọn khoảng ngày báo cáo; đối chiếu doanh thu chỉ gồm đơn hoàn tất.

## Tài khoản seed

Mật khẩu chung: `password`.

| Vai trò | Tài khoản |
|---|---|
| Khách hàng | `customer@foodgo.test` |
| Nhân viên | `staff@foodgo.test` |
| Quản trị | `admin@foodgo.test` |
