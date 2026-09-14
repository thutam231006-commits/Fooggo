# FoodGo REST API

## Phan tich yeu cau

- Khach hang: dang ky/dang nhap, xem thuc don, dat mon, thanh toan bang Vi FoodGo, theo doi don va danh gia.
- Nhan vien: xem don da thanh toan, chuyen trang thai `preparing`, `ready`, `completed` hoac `rejected`.
- Quan tri vien: CRUD mon an (xoa la ngung ban), bao cao doanh thu va mon ban chay.
- Du lieu can tinh dung va an toan: validation, phan quyen theo vai tro, transaction cho ton kho va thanh toan, khong thanh toan hai lan.

## Endpoint chinh

Tat ca endpoint co tien to `/api/v1`. `POST /auth/register`, `POST /auth/login` tra ve `token`; gui token qua `Authorization: Bearer <token>`. Dang nhap nhan truong `login` (email hoac ma sinh vien) va `password`. Dang ky yeu cau `password_confirmation`.

Token tra ve chi hien thi mot lan; database luu ban bam SHA-256. Cac token cu duoc nang cap tu dong khi su dung. Endpoint dang nhap va dang ky gioi han 10 request/phut/IP.

`GET /foods`, `GET /foods/{id}`; admin dung `POST|PUT/PATCH|DELETE /foods` de CRUD.

`GET /cart`, `POST /cart/items`, `PATCH|DELETE /cart/items/{id}` de quan ly gio hang luu trong database.

Khach hang dung `POST /orders`, `PATCH|DELETE /orders/{id}`; tao don voi `{pickup_slot, items:[{food_id, quantity}]}` hoac bo `items` de dat tu gio hang. Moi vai tro da dang nhap dung `GET /orders` va `GET /orders/{id}` theo pham vi quyen. `POST /orders/{id}/payment` thanh toan Vi FoodGo. Nhan vien/admin dung `PATCH /orders/{id}/status`.

`GET /foods/{id}/reviews`; khach hang dung `POST /foods/{id}/reviews` voi `{order_id, rating, content}`. Admin dung CRUD `/users`, `GET /reports/revenue` va `GET /reports/best-sellers`; hai bao cao nhan `from`, `to` theo dinh dang ngay.

## Trang web

- `/register`: dang ky tai khoan khach hang.
- `/login`: dang nhap bang email hoac ma sinh vien.
- `/dashboard`: bang dieu khien theo vai tro khach hang, nhan vien hoac quan tri vien.
- `/customer/dashboard`: don hang, vi va gio hang cua khach.
- `/staff/dashboard`: hang doi va xu ly trang thai don.
- `/admin/dashboard`: tong quan quan tri.
- `/admin/foods`: quan ly thuc don.
- `/admin/users`: tao va quan ly tai khoan.
- `/admin/reports`: doanh thu va mon ban chay.
- `/foods/{id}`: xem mon va them mon vao gio.
- `/cart`: cap nhat gio, chon gio nhan va tao don.
- `/orders/{id}`: xem don, thanh toan Vi FoodGo, theo doi trang thai va danh gia mon da hoan tat.

## Chay local

1. Tao database MySQL `foodgo`, sau do sua `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=foodgo
DB_USERNAME=root
DB_PASSWORD=
```

Co the dung SQLite mac dinh neu da co `database/database.sqlite`.

2. Chay `php artisan key:generate`, `php artisan migrate --seed`, sau do `php artisan serve`.

Voi Laragon cua du an nay, nen chon PHP 8.3.30. PHP 8.5.5 hien tai chua tuong thich voi ban Carbon trong lockfile.

Tai khoan seed deu co mat khau `password`: `customer@foodgo.test`, `staff@foodgo.test`, `admin@foodgo.test`.

## Gia dinh bo sung

- Gio hang duoc luu trong hai bang `carts` va `cart_items`; moi tai khoan co mot gio hang.
- Token API luu truc tiep trong users de project chay khong can Sanctum; voi production nen thay bang Laravel Sanctum/OAuth.
- Tu choi don da thanh toan se tu dong hoan tien vao Vi FoodGo va hoan lai ton kho.
- `DELETE /foods/{id}` la soft-disable (`is_available=false`) de bao toan lich su don hang.
