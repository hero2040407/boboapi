<?php
namespace BBExtend\fix;

class LevelFix
{
    
    /**
     * 根据每个级别的数据，返回一个数组，每个级别都需要的总经验
     * @return number[]
     */
    public static function get_all()
    {
        $arr  = self::get_every();
        $new = array();
        foreach ($arr as $k => $v) {
            if ($k == 1) {
                $new[$k] = $arr[$k]; 
            } else {
                $new[$k] = $arr[$k] + $new[$k - 1];
            }
        }
        return $new;
    }
    
    /**
     * 返回每个级别单独升级所需经验，由产品文档提供，好处是不用查表了！！
     * @return number[]
     */
    public static function get_every()
    {
        $arr = [
            1 => 0,
            2 => 80,
            3 => 95,
            4 => 105,
            5 => 120,
            6 => 140,
            7 => 155,
            8 => 175,
            9 => 190,
            10 => 210,
            11 => 235,
            12 => 255,
            13 => 280,
            14 => 305,
            15 => 330,
            16 => 355,
            17 => 380,
            18 => 410,
            19 => 440,
            20 => 470,
            21 => 500,
            22 => 535,
            23 => 570,
            24 => 605,
            25 => 640,
            26 => 675,
            27 => 715,
            28 => 755,
            29 => 795,
            30 => 835,
            31 => 875,
            32 => 920,
            33 => 965,
            34 => 1010,
            35 => 1055,
            36 => 1100,
            37 => 1150,
            38 => 1200,
            39 => 1250,
            40 => 1300,
            41 => 1355,
            42 => 1410,
            43 => 1465,
            44 => 1520,
            45 => 1575,
            46 => 1635,
            47 => 1690,
            48 => 1750,
            49 => 1815,
            50 => 1875,
            51 => 1940,
            52 => 2005,
            53 => 2070,
            54 => 2135,
            55 => 2200,
            56 => 2270,
            57 => 2340,
            58 => 2410,
            59 => 2480,
            60 => 2555,
            61 => 2630,
            62 => 2705,
            63 => 2780,
            64 => 2855,
            65 => 2935,
            66 => 3015,
            67 => 3095,
            68 => 3175,
            69 => 3255,
            70 => 3340,
            71 => 3425,
            72 => 3510,
            73 => 3595,
            74 => 3685,
            75 => 3770,
            76 => 3860,
            77 => 3950,
            78 => 4045,
            79 => 4135,
            80 => 4230,
            81 => 4325,
            82 => 4420,
            83 => 4520,
            84 => 4615,
            85 => 4715,
            86 => 4815,
            87 => 4915,
            88 => 5020,
            89 => 5125,
            90 => 5230,
            91 => 5335,
            92 => 5440,
            93 => 5545,
            94 => 5655,
            95 => 5765,
            96 => 5875,
            97 => 5990,
            98 => 6100,
            99 => 6215,
            100 => 6330,
        ];
        return $arr;
        
        
    }
    
}
