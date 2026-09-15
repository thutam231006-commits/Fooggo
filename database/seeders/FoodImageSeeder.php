<?php

namespace Database\Seeders;

use App\Models\Food;
use Illuminate\Database\Seeder;

class FoodImageSeeder extends Seeder
{
    public function run(): void
    {
        $images = [
            'Cơm tấm sườn bì chả' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBFGqOabuHzKpe-sqb3IKBnNAKrVmOIpsAi5vW1gIJhG28uuB0fkAt9lUKv-Xc27AF1gZfXe7Q3gBwtEZ9EJmhyCfjbCm4d13A6LuHEIyLEqKlx-wjnk026jbeqbASgwSDr7XKbSEsazVea9fUwjp6enLBDQ_l2Ps1oqZdiYuWYG69KJ4wYMcLCCwZ5idk7BHDoSPmDv3IcNyf7NChnzbJBXzrcLbzCcpBbQvkeYUmBzWD3-DI4VUGL',
            'Cơm gà xối mỡ' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuC6-Lm0MAtWL6p3JNEWz4_VktTO9rBiTziR1jwk8kXj2jhMgfgBnU_SW5N2qO6OzFiUxFnVt36PmhvEPbE-Ct_8Z8d5g-G_nrjHmD_b_5W5x_Y8RCJFA97UWmgmtRkv8xc6VokXh93S50UH7FymxLTmKNnmTJ1rh53_A2wVpUQVlP3zWDqabnuXxVpsY0KK19WXq47hl8j3lIBsgoidUQud11qxXG-P7U1hYrd4grFzOpcOTVq5w-DY',
            'Cơm sườn nướng' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBFGqOabuHzKpe-sqb3IKBnNAKrVmOIpsAi5vW1gIJhG28uuB0fkAt9lUKv-Xc27AF1gZfXe7Q3gBwtEZ9EJmhyCfjbCm4d13A6LuHEIyLEqKlx-wjnk026jbeqbASgwSDr7XKbSEsazVea9fUwjp6enLBDQ_l2Ps1oqZdiYuWYG69KJ4wYMcLCCwZ5idk7BHDoSPmDv3IcNyf7NChnzbJBXzrcLbzCcpBbQvkeYUmBzWD3-DI4VUGL',
            'Bún bò Huế' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDeuRi3z4dDSQn0H5AIECoSyHEPu3D-3koKH6wYkSXxq19YRWy1eu9ZwdKwIg9K2KVrQCqSwTI0Fc6HHg6TJh_Og5U79yXqeqpdogM77sv6Mb1hOcEyKAsmLWIiU6C2wMx9Iq0KtyITPFhjT1ycDyLvfi1jRrP-XRyWC-2wb2KuKCWFx0cOPdu24YmRRaqNnK9SDqIaEOS-BQk2VmEaC31gFSktUipIrk_7ntpDjp-fyKmhNtdm5TwM',
            'Phở bò tái' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDeuRi3z4dDSQn0H5AIECoSyHEPu3D-3koKH6wYkSXxq19YRWy1eu9ZwdKwIg9K2KVrQCqSwTI0Fc6HHg6TJh_Og5U79yXqeqpdogM77sv6Mb1hOcEyKAsmLWIiU6C2wMx9Iq0KtyITPFhjT1ycDyLvfi1jRrP-XRyWC-2wb2KuKCWFx0cOPdu24YmRRaqNnK9SDqIaEOS-BQk2VmEaC31gFSktUipIrk_7ntpDjp-fyKmhNtdm5TwM',
            'Bún thịt nướng' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuC-5dlX8ZjrkfYqP5yvVDYXa6A9wAzQnATn0LJ_mBHI6wD0VQgSkjuQCBNLJSb2zhnar6vDFhKetwavEAffOg45pxcX5UFCx--j2PxiZc7FniiokWwH4vLGPdlvYZzmSG341MxCUJpYfjUINNZ7YVUgp_tFRHAoCf1aXjUG8hDpYXB44WKA2Qsgy7gNnG5SWJUz4VuWBYClQoIphWTqDkI0HKE8WljOPBRz2cIXVJ4hVL-6nvszBVaF',
            'Bánh mì thịt nướng' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBbiOv71RM8lN9vJMHAlfvPh_6IwWmdv0mIjdn505H9j3Kmgr1bqqcEWGH3s6i0LLUO9tUfIDkQhRieIR94b5KqxqiKSDW6AX3_m8yrnzlPhcvXI7n8BO5TXZAZWGEZQ5noyk_UanI1cCd-WGlhIei1VBotN6Tj8K8aozNHoxSctH9g1abNkzF_Se0HneS1hLwK2imdQ3LzPOFquQ86olX2MOCQH1_w6Y480QbXsNQ0_YqycxTf6wj0',
            'Gỏi cuốn tôm thịt' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDab-3htGeGIouOprswWwbpe0b2DTFIXmfT53s2caIw9tWh2K07G0GBw2CF84OYMvZGS-46eu8Y0SRjVlWRT_ME-u9UrE9B1O9tXbVvlJuFuy-Q-LrVqP7iJgMAONxDOQ0dV6XoWNZLpiEGD4FNhaLmbY9NtBzQrCrUYS-FdrT4zj7oU9lDg9kq5iSkmw3bn9IiiCdDcfN6pPDMioRI9WXAsWr5UZdZQ2hXpMWQqSINWcgsTqAjoiqh',
            'Trà đào cam sả' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuD1eH_jLAImhPcdn8erRwzAOQG_RSUJimw_DzZVTdsPWFiVuoGiIW3kTYR9IR75LqH8l7L2hTvAxfH3yLTXWVEKrjuGVbM9yKB3qaDZdr3uODZpnzDmOvmIJidU8tEgUcIFSEzLhyRMU0UtkZBDJZvlKnppSMes49QLZShOHFSnvZ13KTXMEmpKOJQgZXsi69P1RL4_lCXL9Pl_piU4eB19ZyP4B97RhjXFbunVF4Fk99_vQaNMJ1XU',
            'Trà tắc' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuD1eH_jLAImhPcdn8erRwzAOQG_RSUJimw_DzZVTdsPWFiVuoGiIW3kTYR9IR75LqH8l7L2hTvAxfH3yLTXWVEKrjuGVbM9yKB3qaDZdr3uODZpnzDmOvmIJidU8tEgUcIFSEzLhyRMU0UtkZBDJZvlKnppSMes49QLZShOHFSnvZ13KTXMEmpKOJQgZXsi69P1RL4_lCXL9Pl_piU4eB19ZyP4B97RhjXFbunVF4Fk99_vQaNMJ1XU',
            'Xôi gà' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuC6-Lm0MAtWL6p3JNEWz4_VktTO9rBiTziR1jwk8kXj2jhMgfgBnU_SW5N2qO6OzFiUxFnVt36PmhvEPbE-Ct_8Z8d5g-G_nrjHmD_b_5W5x_Y8RCJFA97UWmgmtRkv8xc6VokXh93S50UH7FymxLTmKNnmTJ1rh53_A2wVpUQVlP3zWDqabnuXxVpsY0KK19WXq47hl8j3lIBsgoidUQud11qxXG-P7U1hYrd4grFzOpcOTVq5w-DY',
        ];

        foreach ($images as $name => $url) {
            Food::where('name', $name)->update(['image_url' => $url]);
        }
    }
}
