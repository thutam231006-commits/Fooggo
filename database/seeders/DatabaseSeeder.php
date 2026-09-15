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

        $imageUrls = [
            'https://lh3.googleusercontent.com/aida-public/AB6AXuBFGqOabuHzKpe-sqb3IKBnNAKrVmOIpsAi5vW1gIJhG28uuB0fkAt9lUKv-Xc27AF1gZfXe7Q3gBwtEZ9EJmhyCfjbCm4d13A6LuHEIyLEqKlx-wjnk026jbeqbASgwSDr7XKbSEsazVea9fUwjp6enLBDQ_l2Ps1oqZdiYuWYG69KJ4wYMcLCCwZ5idk7BHDoSPmDv3IcNyf7NChnzbJBXzrcLbzCcpBbQvkeYUmBzWD3-DI4VUGL',
            'https://lh3.googleusercontent.com/aida-public/AB6AXuC6-Lm0MAtWL6p3JNEWz4_VktTO9rBiTziR1jwk8kXj2jhMgfgBnU_SW5N2qO6OzFiUxFnVt36PmhvEPbE-Ct_8Z8d5g-G_nrjHmD_b_5W5x_Y8RCJFA97UWmgmtRkv8xc6VokXh93S50UH7FymxLTmKNnmTJ1rh53_A2wVpUQVlP3zWDqabnuXxVpsY0KK19WXq47hl8j3lIBsgoidUQud11qxXG-P7U1hYrd4grFzOpcOTVq5w-DY',
            'https://lh3.googleusercontent.com/aida-public/AB6AXuBFGqOabuHzKpe-sqb3IKBnNAKrVmOIpsAi5vW1gIJhG28uuB0fkAt9lUKv-Xc27AF1gZfXe7Q3gBwtEZ9EJmhyCfjbCm4d13A6LuHEIyLEqKlx-wjnk026jbeqbASgwSDr7XKbSEsazVea9fUwjp6enLBDQ_l2Ps1oqZdiYuWYG69KJ4wYMcLCCwZ5idk7BHDoSPmDv3IcNyf7NChnzbJBXzrcLbzCcpBbQvkeYUmBzWD3-DI4VUGL',
            'https://lh3.googleusercontent.com/aida-public/AB6AXuDeuRi3z4dDSQn0H5AIECoSyHEPu3D-3koKH6wYkSXxq19YRWy1eu9ZwdKwIg9K2KVrQCqSwTI0Fc6HHg6TJh_Og5U79yXqeqpdogM77sv6Mb1hOcEyKAsmLWIiU6C2wMx9Iq0KtyITPFhjT1ycDyLvfi1jRrP-XRyWC-2wb2KuKCWFx0cOPdu24YmRRaqNnK9SDqIaEOS-BQk2VmEaC31gFSktUipIrk_7ntpDjp-fyKmhNtdm5TwM',
            'https://lh3.googleusercontent.com/aida-public/AB6AXuDeuRi3z4dDSQn0H5AIECoSyHEPu3D-3koKH6wYkSXxq19YRWy1eu9ZwdKwIg9K2KVrQCqSwTI0Fc6HHg6TJh_Og5U79yXqeqpdogM77sv6Mb1hOcEyKAsmLWIiU6C2wMx9Iq0KtyITPFhjT1ycDyLvfi1jRrP-XRyWC-2wb2KuKCWFx0cOPdu24YmRRaqNnK9SDqIaEOS-BQk2VmEaC31gFSktUipIrk_7ntpDjp-fyKmhNtdm5TwM',
            'https://images.unsplash.com/photo-1563379926898-05f4575a45d8?auto=format&fit=crop&w=900&q=85',
            'https://lh3.googleusercontent.com/aida-public/AB6AXuC-5dlX8ZjrkfYqP5yvVDYXa6A9wAzQnATn0LJ_mBHI6wD0VQgSkjuQCBNLJSb2zhnar6vDFhKetwavEAffOg45pxcX5UFCx--j2PxiZc7FniiokWwH4vLGPdlvYZzmSG341MxCUJpYfjUINNZ7YVUgp_tFRHAoCf1aXjUG8hDpYXB44WKA2Qsgy7gNnG5SWJUz4VuWBYClQoIphWTqDkI0HKE8WljOPBRz2cIXVJ4hVL-6nvszBVaF',
            'https://lh3.googleusercontent.com/aida-public/AB6AXuBbiOv71RM8lN9vJMHAlfvPh_6IwWmdv0mIjdn505H9j3Kmgr1bqqcEWGH3s6i0LLUO9tUfIDkQhRieIR94b5KqxqiKSDW6AX3_m8yrnzlPhcvXI7n8BO5TXZAZWGEZQ5noyk_UanI1cCd-WGlhIei1VBotN6Tj8K8aozNHoxSctH9g1abNkzF_Se0HneS1hLwK2imdQ3LzPOFquQ86olX2MOCQH1_w6Y480QbXsNQ0_YqycxTf6wj0',
            'https://lh3.googleusercontent.com/aida-public/AB6AXuDab-3htGeGIouOprswWwbpe0b2DTFIXmfT53s2caIw9tWh2K07G0GBw2CF84OYMvZGS-46eu8Y0SRjVlWRT_ME-u9UrE9B1O9tXbVvlJuFuy-Q-LrVqP7iJgMAONxDOQ0dV6XoWNZLpiEGD4FNhaLmbY9NtBzQrCrUYS-FdrT4zj7oU9lDg9kq5iSkmw3bn9IiiCdDcfN6pPDMioRI9WXAsWr5UZdZQ2hXpMWQqSINWcgsTqAjoiqh',
            'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?auto=format&fit=crop&w=900&q=85',
            'https://lh3.googleusercontent.com/aida-public/AB6AXuD1eH_jLAImhPcdn8erRwzAOQG_RSUJimw_DzZVTdsPWFiVuoGiIW3kTYR9IR75LqH8l7L2hTvAxfH3yLTXWVEKrjuGVbM9yKB3qaDZdr3uODZpnzDmOvmIJidU8tEgUcIFSEzLhyRMU0UtkZBDJZvlKnppSMes49QLZShOHFSnvZ13KTXMEmpKOJQgZXsi69P1RL4_lCXL9Pl_piU4eB19ZyP4B97RhjXFbunVF4Fk99_vQaNMJ1XU',
            'https://lh3.googleusercontent.com/aida-public/AB6AXuD1eH_jLAImhPcdn8erRwzAOQG_RSUJimw_DzZVTdsPWFiVuoGiIW3kTYR9IR75LqH8l7L2hTvAxfH3yLTXWVEKrjuGVbM9yKB3qaDZdr3uODZpnzDmOvmIJidU8tEgUcIFSEzLhyRMU0UtkZBDJZvlKnppSMes49QLZShOHFSnvZ13KTXMEmpKOJQgZXsi69P1RL4_lCXL9Pl_piU4eB19ZyP4B97RhjXFbunVF4Fk99_vQaNMJ1XU',
            'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=900&q=85',
            'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=900&q=85',
            'https://images.unsplash.com/photo-1523362628745-0c100150b504?auto=format&fit=crop&w=900&q=85',
            'https://lh3.googleusercontent.com/aida-public/AB6AXuC6-Lm0MAtWL6p3JNEWz4_VktTO9rBiTziR1jwk8kXj2jhMgfgBnU_SW5N2qO6OzFiUxFnVt36PmhvEPbE-Ct_8Z8d5g-G_nrjHmD_b_5W5x_Y8RCJFA97UWmgmtRkv8xc6VokXh93S50UH7FymxLTmKNnmTJ1rh53_A2wVpUQVlP3zWDqabnuXxVpsY0KK19WXq47hl8j3lIBsgoidUQud11qxXG-P7U1hYrd4grFzOpcOTVq5w-DY',
        ];
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
            Food::updateOrCreate(['id' => $index + 1], $food + ['image_url' => $imageUrls[$index], 'is_available' => true]);
        }
    }
}
