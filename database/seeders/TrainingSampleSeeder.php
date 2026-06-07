<?php

namespace Database\Seeders;

use App\Models\TrainingSample;
use Illuminate\Database\Seeder;

class TrainingSampleSeeder extends Seeder
{
    public function run(): void
    {
        $samples = [
            ['text' => 'permainan mu bagus sekali malam ini', 'sentiment' => 'positif'],
            ['text' => 'bruno tampil hebat dan tim bermain solid', 'sentiment' => 'positif'],
            ['text' => 'saya senang melihat manchester united menang', 'sentiment' => 'positif'],
            ['text' => 'strategi pelatih sangat bagus dan efektif', 'sentiment' => 'positif'],
            ['text' => 'pertahanan rapi serangan tajam mantap', 'sentiment' => 'positif'],
            ['text' => 'mainnya keren dan penuh semangat', 'sentiment' => 'positif'],
            ['text' => 'tim ini menunjukkan progres yang sangat baik', 'sentiment' => 'positif'],
            ['text' => 'hasil pertandingan memuaskan fans united', 'sentiment' => 'positif'],
            ['text' => 'pemain muda tampil luar biasa hari ini', 'sentiment' => 'positif'],
            ['text' => 'akhirnya permainan mu enak ditonton', 'sentiment' => 'positif'],
            ['text' => 'manchester united jelek sekali dan mengecewakan', 'sentiment' => 'negatif'],
            ['text' => 'main kacau pertahanan buruk dan gampang ditembus', 'sentiment' => 'negatif'],
            ['text' => 'bruno egois dan permainan tim berantakan', 'sentiment' => 'negatif'],
            ['text' => 'saya kesal melihat mu kalah terus', 'sentiment' => 'negatif'],
            ['text' => 'pelatih salah strategi dan tidak jelas', 'sentiment' => 'negatif'],
            ['text' => 'serangan tumpul dan finishing sangat buruk', 'sentiment' => 'negatif'],
            ['text' => 'tim ini payah dan tidak ada perkembangan', 'sentiment' => 'negatif'],
            ['text' => 'permainan membosankan dan sangat buruk', 'sentiment' => 'negatif'],
            ['text' => 'lini tengah lemah dan sering kehilangan bola', 'sentiment' => 'negatif'],
            ['text' => 'hasil ini memalukan untuk klub sebesar united', 'sentiment' => 'negatif'],
            ['text' => 'saya menonton pertandingan ini tanpa ekspektasi', 'sentiment' => 'netral'],
            ['text' => 'komentar ini hanya membahas susunan pemain', 'sentiment' => 'netral'],
            ['text' => 'video ini menjelaskan kondisi tim saat ini', 'sentiment' => 'netral'],
            ['text' => 'saya menunggu laga berikutnya minggu depan', 'sentiment' => 'netral'],
            ['text' => 'berita transfer ini masih sebatas rumor', 'sentiment' => 'netral'],
            ['text' => 'jadwal pertandingan berikutnya sudah diumumkan', 'sentiment' => 'netral'],
            ['text' => 'saya baru melihat ringkasan pertandingan tadi', 'sentiment' => 'netral'],
            ['text' => 'statistik pertandingan cukup menarik untuk dibahas', 'sentiment' => 'netral'],
            ['text' => 'pemain ini kembali masuk skuad utama', 'sentiment' => 'netral'],
            ['text' => 'semoga video berikutnya membahas taktik lebih detail', 'sentiment' => 'netral'],
        ];

        TrainingSample::query()->delete();
        TrainingSample::query()->insert(array_map(
            fn (array $sample): array => [
                ...$sample,
                'source' => 'seed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            $samples
        ));
    }
}
