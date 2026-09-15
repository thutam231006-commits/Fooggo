# Luong thanh toan FoodGo

Pham vi: Vi FoodGo gia lap, khong ket noi ngan hang, khong nap tien that.

1. Tao don: server tinh gia tu database, khoa ton kho va giu so luong mon. Don o trang thai `pending_payment`.
2. Han giu mon: 30 phut tu luc dat, hoac gio nhan mon neu som hon. Don cu chua co han dung `ordered_at + 30 phut`.
3. Thanh toan: kiem tra chu don, han giu mon, trang thai, khoa don va vi. Tru vi, luu giao dich duy nhat va chuyen `paid` trong cung transaction.
4. Retry: don da thanh toan/che bien/hoan tat voi giao dich thanh cong tra lai ket qua cu; khong tru tien lan hai.
5. Thieu so du: khong tru tien, khong tao giao dich thanh cong; khach co the thu lai trong han giu mon hoac huy.
6. Het han: huy don chua thanh toan va hoan ton kho mot lan. Khong tru vi.
7. Nhan vien tu choi: chi tu `paid`, bat buoc ly do. Kiem tra giao dich thanh cong va so tien khop, hoan vi/ton kho va danh dau `refunded` trong transaction.

## Scheduler

Production phai chay `php artisan schedule:run` moi phut. Khi phat trien Laragon, co the chay `php artisan schedule:work` trong terminal bang PHP 8.3.

Lenh thu cong: `php artisan orders:expire`. Lenh nay huy cac don chua thanh toan qua han va hoan ton kho, bao gom don cu.

Scheduler da duoc khai bao trong `routes/console.php`, nhung khong tu chay neu khong co worker/cron. Khong tu cau hinh Windows Task Scheduler trong dot thay doi nay.

## Gioi han trien khai

Day la vi noi bo gia lap, khong phai he thong thanh toan tien that. Ket noi cong thanh toan thuc te can thong tin merchant, webhook co chu ky, payment attempts, so cai giao dich vi va doi soat voi nha cung cap. Khong dung redirect tu trinh duyet lam bang chung da tra tien.
