<?php

namespace Database\Seeders;

use App\Models\Food;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(['email' => 'customer@foodgo.test'], ['name' => 'Nguyễn Minh Anh', 'student_id' => 'SV001', 'phone' => '0901234567', 'role' => 'customer', 'wallet_balance' => 1000000, 'is_active' => true, 'password' => Hash::make('password')]);
        User::updateOrCreate(['email' => 'staff@foodgo.test'], ['name' => 'Trần Văn Bình', 'phone' => '0902345678', 'role' => 'staff', 'wallet_balance' => 0, 'is_active' => true, 'password' => Hash::make('password')]);
        User::updateOrCreate(['email' => 'admin@foodgo.test'], ['name' => 'Lê Thu Hà', 'phone' => '0903456789', 'role' => 'admin', 'wallet_balance' => 0, 'is_active' => true, 'password' => Hash::make('password')]);

        $foods = [
            ['name' => 'Cơm tấm sườn bì chả', 'category' => 'Cơm', 'description' => 'Cơm tấm ăn cùng sườn nướng, bì, chả trứng và nước mắm chua ngọt.', 'price' => 45000, 'stock' => 45, 'image_url' => 'https://tse1.mm.bing.net/th/id/OIP.0F18cFH0HG4Zl2A8k22ulQHaFL?r=0&rs=1&pid=ImgDetMain&o=7&rm=3'],
            ['name' => 'Cơm gà xối mỡ', 'category' => 'Cơm', 'description' => 'Đùi gà giòn da, cơm chiên thơm và rau ăn kèm.', 'price' => 40000, 'stock' => 40, 'image_url' => 'https://i.ytimg.com/vi/X4S1zPZCm50/maxresdefault.jpg'],
            ['name' => 'Cơm sườn nướng', 'category' => 'Cơm', 'description' => 'Sườn heo ướp mật ong nướng, dùng với cơm trắng và đồ chua.', 'price' => 38000, 'stock' => 50, 'image_url' => 'https://cdn11.dienmaycholon.vn/filewebdmclnew/public/userupload/files/suon-heo-nau-gi-ngon/suon-heo-nau-gi-ngon-8.jpg'],
            ['name' => 'Bún bò Huế', 'category' => 'Bún - Phở', 'description' => 'Nước dùng đậm vị, thịt bò, chả Huế và rau sống.', 'price' => 40000, 'stock' => 35, 'image_url' => 'https://tse3.mm.bing.net/th/id/OIP.0MyWpoPDIQaXAk5s94UY8AHaEC?r=0&rs=1&pid=ImgDetMain&o=7&rm=3'],
            ['name' => 'Phở bò tái', 'category' => 'Bún - Phở', 'description' => 'Phở bò nước trong, thịt tái mềm, hành và rau thơm.', 'price' => 42000, 'stock' => 30, 'image_url' => 'https://kanawa.vn/wp-content/uploads/2022/07/pho-bo-tai-rau-an-kem-da-dang.jpg'],
            ['name' => 'Mì xào bò', 'category' => 'Mì', 'description' => 'Mì xào với thịt bò và rau cải, chế biến khi nhận đơn.', 'price' => 38000, 'stock' => 32, 'image_url' => 'https://tse1.mm.bing.net/th/id/OIP.kF6CDX1YgKJj8pOuSuXQWgHaEK?r=0&rs=1&pid=ImgDetMain&o=7&rm=3'],
            ['name' => 'Bún thịt nướng', 'category' => 'Bún - Phở', 'description' => 'Bún tươi, thịt nướng, chả giò, rau sống và nước mắm.', 'price' => 35000, 'stock' => 40, 'image_url' => 'https://file.hstatic.net/200000700229/article/bun-thit-nuong-cha-gio-1_049ecb6eac20407ab13217579cdb1c73.jpg'],
            ['name' => 'Bánh mì thịt nướng', 'category' => 'Bánh mì', 'description' => 'Bánh mì giòn với thịt nướng, đồ chua, dưa leo và rau thơm.', 'price' => 25000, 'stock' => 60, 'image_url' => 'https://khietminhbakery.com/wp-content/uploads/2025/08/o-banh-mi-thit-nuong-giau-chat-xo-voi-nhieu-rau-xanh-dua-chuot-va-cu-cai-nhan-manh-loi-ich-keo-dai-cam-giac-no-va-ho-tro-kiem-soat-can-nang.jpg'],
            ['name' => 'Gỏi cuốn tôm thịt', 'category' => 'Ăn vặt', 'description' => 'Gỏi cuốn tôm thịt tươi, dùng kèm tương đậu. Giá tính theo cuốn.', 'price' => 12000, 'stock' => 80, 'image_url' => 'https://toplist.vn/images/800px/goi-cuon-tom-thit-508499.jpg'],
            ['name' => 'Khoai tây chiên', 'category' => 'Ăn vặt', 'description' => 'Khoai tây chiên vàng giòn, dùng kèm tương cà.', 'price' => 25000, 'stock' => 35, 'image_url' => 'https://inoxcnv.vn/wp-content/uploads/2024/11/Thiet-ke-chua-co-ten-45-1-1.jpg'],
            ['name' => 'Trà đào cam sả', 'category' => 'Đồ uống', 'description' => 'Trà đào thanh mát với cam tươi và hương sả.', 'price' => 25000, 'stock' => 50, 'image_url' => 'https://horecavn.com/wp-content/uploads/2024/05/huong-dan-cong-thuc-tra-dao-cam-sa-hut-khach-ngon-kho-cuong_20240526180626.jpg'],
            ['name' => 'Trà tắc', 'category' => 'Đồ uống', 'description' => 'Trà tắc chua ngọt, giải khát trong ngày.', 'price' => 15000, 'stock' => 70, 'image_url' => 'https://hoanghamobile.com/tin-tuc/wp-content/uploads/2025/06/cach-nau-tra-tac-768x402.jpg'],
            ['name' => 'Cà phê sữa đá', 'category' => 'Đồ uống', 'description' => 'Cà phê rang xay pha phin cùng sữa đặc.', 'price' => 18000, 'stock' => 50, 'image_url' => 'https://bizweb.dktcdn.net/100/270/753/files/cach-pha-cafe-rang-xay-2.jpg?v=1709288565912'],
            ['name' => 'Sữa tươi trân châu đường đen', 'category' => 'Đồ uống', 'description' => 'Sữa tươi mát lạnh với trân châu và đường đen.', 'price' => 30000, 'stock' => 40, 'image_url' => 'https://image-us.eva.vn/upload/3-2022/images/2022-08-26/image7-1661495870-335-width650height576.jpg'],
            ['name' => 'Nước suối', 'category' => 'Đồ uống', 'description' => 'Chai nước tinh khiết 500 ml.', 'price' => 10000, 'stock' => 100, 'image_url' => 'https://down-vn.img.susercontent.com/file/6c8ed8ebb5e8f863bfdc7b8355924121'],
            ['name' => 'Xôi gà', 'category' => 'Món sáng', 'description' => 'Xôi dẻo ăn cùng thịt gà xé, hành phi và nước sốt.', 'price' => 30000, 'stock' => 30, 'image_url' => 'https://tse1.mm.bing.net/th/id/OIP.F-sPCy9n0my1-FmUFsMEpAHaEK?r=0&rs=1&pid=ImgDetMain&o=7&rm=3'],
            ['name' => 'Mì Quảng', 'category' => 'Mì', 'description' => 'Mì vàng ươn, nước dùng đặc, thịt, tôm và rau sống.', 'price' => 40000, 'stock' => 28, 'image_url' => 'https://statics.vinpearl.com/mi-quang-quang-nam-3_1698931643.jpg'],
            ['name' => 'Bánh mì pâté trứng', 'category' => 'Bánh mì', 'description' => 'Bánh mì giòn với pâté, trứng ốp la và rau sống.', 'price' => 28000, 'stock' => 50, 'image_url' => 'https://blog.dktcdn.net/files/cach-lam-banh-mi-pate-trung-ngon-de-ban.jpg'],
            ['name' => 'Cháo gà', 'category' => 'Món sáng', 'description' => 'Cháo gà mền mịn, ăn kèm thịt gà xé và gừng sống.', 'price' => 28000, 'stock' => 32, 'image_url' => 'https://tse3.mm.bing.net/th/id/OIP.z3AfmW83b0bsSpBMytkM8wHaHa?r=0&rs=1&pid=ImgDetMain&o=7&rm=3'],
            ['name' => 'Bánh tráng nướng me', 'category' => 'Ăn vặt', 'description' => 'Bánh tráng nướng giòn, rắc me nóng sốt.', 'price' => 20000, 'stock' => 50, 'image_url' => 'https://bepxua.vn/wp-content/uploads/2022/08/lam-banh-trang-nuong.jpg'],
            ['name' => 'Mì vằn thắn', 'category' => 'Mì', 'description' => 'Mì vàng giòn, nước dùng thanh và thịt lợn xay.', 'price' => 35000, 'stock' => 40, 'image_url' => 'https://media-cdn-v2.laodong.vn/Storage/NewsPortal/2022/11/6/1113546/Phung-Gia-1.jpg'],
            ];

        foreach ($foods as $index => $food) {
            Food::updateOrCreate(['id' => $index + 1], $food + ['is_available' => true]);
        }
    }
}
