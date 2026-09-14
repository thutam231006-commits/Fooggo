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
            ['name' => 'Cơm tấm sườn bì chả', 'category' => 'Cơm', 'description' => 'Cơm tấm ăn cùng sườn nướng, bì, chả trứng và nước mắm chua ngọt.', 'price' => 45000, 'stock' => 45],
            ['name' => 'Cơm gà xối mỡ', 'category' => 'Cơm', 'description' => 'Đùi gà giòn da, cơm chiên thơm và rau ăn kèm.', 'price' => 40000, 'stock' => 40],
            ['name' => 'Cơm sườn nướng', 'category' => 'Cơm', 'description' => 'Sườn heo ướp mật ong nướng, dùng với cơm trắng và đồ chua.', 'price' => 38000, 'stock' => 50],
            ['name' => 'Bún bò Huế', 'category' => 'Bún - Phở', 'description' => 'Nước dùng đậm vị, thịt bò, chả Huế và rau sống.', 'price' => 40000, 'stock' => 35],
            ['name' => 'Phở bò tái', 'category' => 'Bún - Phở', 'description' => 'Phở bò nước trong, thịt tái mềm, hành và rau thơm.', 'price' => 42000, 'stock' => 30],
            ['name' => 'Mì xào bò', 'category' => 'Mì', 'description' => 'Mì xào với thịt bò và rau cải, chế biến khi nhận đơn.', 'price' => 38000, 'stock' => 32],
            ['name' => 'Bún thịt nướng', 'category' => 'Bún - Phở', 'description' => 'Bún tươi, thịt nướng, chả giò, rau sống và nước mắm.', 'price' => 35000, 'stock' => 40],
            ['name' => 'Bánh mì thịt nướng', 'category' => 'Bánh mì', 'description' => 'Bánh mì giòn với thịt nướng, đồ chua, dưa leo và rau thơm.', 'price' => 25000, 'stock' => 60],
            ['name' => 'Gỏi cuốn tôm thịt', 'category' => 'Ăn vặt', 'description' => 'Gỏi cuốn tôm thịt tươi, dùng kèm tương đậu. Giá tính theo cuốn.', 'price' => 12000, 'stock' => 80],
            ['name' => 'Khoai tây chiên', 'category' => 'Ăn vặt', 'description' => 'Khoai tây chiên vàng giòn, dùng kèm tương cà.', 'price' => 25000, 'stock' => 35],
            ['name' => 'Trà đào cam sả', 'category' => 'Đồ uống', 'description' => 'Trà đào thanh mát với cam tươi và hương sả.', 'price' => 25000, 'stock' => 50],
            ['name' => 'Trà tắc', 'category' => 'Đồ uống', 'description' => 'Trà tắc chua ngọt, giải khát trong ngày.', 'price' => 15000, 'stock' => 70],
            ['name' => 'Cà phê sữa đá', 'category' => 'Đồ uống', 'description' => 'Cà phê rang xay pha phin cùng sữa đặc.', 'price' => 18000, 'stock' => 50],
            ['name' => 'Sữa tươi trân châu đường đen', 'category' => 'Đồ uống', 'description' => 'Sữa tươi mát lạnh với trân châu và đường đen.', 'price' => 30000, 'stock' => 40],
            ['name' => 'Nước suối', 'category' => 'Đồ uống', 'description' => 'Chai nước tinh khiết 500 ml.', 'price' => 10000, 'stock' => 100],
            ['name' => 'Xôi gà', 'category' => 'Món sáng', 'description' => 'Xôi dẻo ăn cùng thịt gà xé, hành phi và nước sốt.', 'price' => 30000, 'stock' => 30],
        ];

        foreach ($foods as $index => $food) {
            Food::updateOrCreate(['id' => $index + 1], $food + ['is_available' => true]);
        }
    }
}
