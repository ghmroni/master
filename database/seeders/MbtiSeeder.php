<?php

namespace Database\Seeders;

use App\Models\MbtiMaster;
use App\Models\MbtiSoal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MbtiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MbtiSoal::truncate();

        ## SOAL
        $csvFile = fopen(base_path('database/csv/mbti_soal.csv'), 'r');
        $firstline = true;
        while (($data = fgetcsv($csvFile, 2000, ';')) !== false) {
            if (! $firstline) {
                MbtiSoal::create([
                    'id'         => $data['0'],
                    'nomor_soal' => $data['0'],
                    'soal'       => $data['1'],
                    'pilihan_a'  => $data['2'],
                    'pilihan_b'  => $data['3'],
                    'target_a'   => $data['4'],
                    'target_b'   => $data['5'],
                ]);
            }
            $firstline = false;
        }
        fclose($csvFile);

        ## ANALISIS
        $dataAnalisis = [
            [
                'type_kepribadian' => 'ISTJ',
                'deskripsi'        =>  json_encode(['Serius, tenang, stabil & damai.', 'Senang pada fakta, logis, obyektif, praktis & realistis.', 'Berorientasi pada tugas, tekun, teratur, menepati janji, dapat diandalkan & bertanggung jawab.', 'Pendengar yang baik, setia, hanya mau berbagi dengan orang dekat.', 'Memegang aturan, standar & prosedur dengan teguh.']),
                'saran'            =>  json_encode(['Belajarlah memahami perasaan & kebutuhan orang lain.', 'Kurangi keinginan untuk mengontrol orang lain atau memerintah mereka untuk menegakkan aturan.', 'Lihatlah lebih banyak sisi positif pada orang lain atau hal lainnya.', 'Terbukalah terhadap perubahan.']),
                'profesi'          => 'Bidang Manajemen, Polisi, Intelijen, Hakim, Pengacara, Dokter, Akuntan (Staf Keuangan), Programmer atau yang berhubungan dengan IT, System Analys, Pemimpin Militer',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'type_kepribadian' => 'ISFJ',
                'deskripsi'        =>  json_encode(['Penuh pertimbangan, hati-hati, teliti dan akurat.', 'Serius, tenang, stabil namun sensitif.', 'Ramah, perhatian pada perasaan & kebutuhan orang lain, setia, kooperatif, pendengar yang baik.', 'Punya kemampuan mengorganisasi, detail, teliti, sangat bertanggungjawab & bisa diandalkan.']),
                'saran'            =>  json_encode(['Lihat lebih dalam, lebih antusias, & lebih semangat.', 'Belajarlah mengatakan ”tidak”. Jangan menyenangkan semua orang atau Anda dianggap plin plan.', 'Jangan terjebak zona nyaman dan rutinitas.', 'Cobalah hal baru. Ada banyak hal menyenangkan yang mungkin belum pernah Anda coba.']),
                'profesi'          => 'Architect, Interior Designer, Perawat, Administratif, Designer, Child Care, Konselor, Back Office Manager, Penjaga Toko / Perpustakaan, Dunia Perhotelan.',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'type_kepribadian' => 'ISTP',
                'deskripsi'        =>  json_encode(['Tenang, pendiam, cenderung kaku, dingin, hati-hati, penuh pertimbangan.', 'Logis, rasional, kritis, obyektif, mampu mengesampingkan perasaan.', 'Mampu menghadapi perubahan mendadak dengan cepat dan tenang.', 'Percaya diri, tegas dan mampu menghadapi perbedaan maupun kritik.', 'Mampu menganalisa, mengorganisir, & mendelegasikan.', 'Problem solver yang baik terutama untuk masalah teknis & keadaan mendadak.']),
                'saran'            =>  json_encode(['Observasilah kehidupan sosial, apa yang membuat orang marah, cinta, senang, termotivasi & terapkan pada hubungan Anda.', 'Belajarlah untuk mengenali perasaan Anda dan mengekspresikannya.', 'Jadilah orang yang lebih terbuka, keluar dari zona nyaman, eksplorasi ide baru, dan berdiskusi dengan orang lain.', 'Jangan mencari-cari kesalahan orang hanya untuk menyelesaikan masalahnya.', 'Jangan menyimpan informasi yang harusnya dibagi dan belajarlah mempercayakan tanggungjawab pada orang lain.']),
                'profesi'          => 'Polisi, Ahli Forensik, Programmer, Ahli Komputer, System Analyst, Teknisi, Insinyur, Mekanik, Pilot, Atlit, Entrepreneur',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'type_kepribadian' => 'ISFP',
                'deskripsi'        =>  json_encode(['Berpikiran simpel & praktis, fleksibel, sensitif, ramah, tidak menonjolkan diri, rendah hati pada kemampuannya.', 'Menghindari konflik, tidak memaksakan pendapat atau nilai-nilainya pada orang lain.', 'Biasanya tidak mau memimpin tetapi menjadi pengikut dan pelaksana yang setia.', 'Seringkali santai menyelesaikan sesuatu, karena sangat menikmati apa yang terjadi saat ini.', 'Menunjukkan perhatian lebih banyak melalui tindakan dibandingkan kata-kata.']),
                'saran'            =>  json_encode(['Jangan takut pada penolakan dan konflik. Anda tidak perlu menyenangkan semua orang.', 'Cobalah untuk mulai memikirkan dampak jangka panjang dari keputusan-keputusan kecil di hari ini.', 'Asah dan kembangkan sisi kreatifitas dan seni dalam diri Anda sebagai modal bagus dalam diri Anda.', 'Cobalah untuk lebih terbuka dan mengekspresikan perasaan Anda.']),
                'profesi'          => 'Seniman, Designer, Pekerja Sosial, Konselor, Psikolog, Guru, Aktor, Bidang Hospitality',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'type_kepribadian' => 'INFJ',
                'deskripsi'        =>  json_encode(['Perhatian, empati, sensitif & berkomitmen terhadap sebuah hubungan.', 'Sukses karena ketekunan, originalitas dan keinginan kuat untuk melakukan apa saja yang diperlukan termasuk memberikan yg terbaik dalam pekerjaan.', 'Idealis, perfeksionis, memegang teguh prinsip.', 'Visioner, penuh ide, kreatif, suka merenung dan inspiring.', 'Biasanya diikuti dan dihormati karena kejelasan visi serta dedikasi pada hal-hal baik.']),
                'saran'            =>  json_encode(['Seimbangkan cara pandang Anda. Jangan hanya melihat sisi negatif & resiko. Namun, lihatlah sisi positif dan peluangnya.', 'Bersabarlah, jangan mudah marah dan menyalahkan orang lain atau situasi.', 'Rileks dan jangan terus menerus berfikir atau menyelesaikan tanggungjawab.']),
                'profesi'          => 'Pengajar, Psikolog, Dokter, Konselor, Pekerja Sosial, Fotografer, Seniman, Designer, Child Care.',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'type_kepribadian' => 'INTJ',
                'deskripsi'        =>  json_encode(['Visioner, punya perencanaan praktis, & biasanya memiliki ide-ide original serta dorongan kuat untuk mencapainya.', 'Mandiri dan percaya diri.', 'Punya kemampuan analisa yang bagus serta menyederhanakan sesuatu yang rumit dan abstrak menjadi sesuatu yang praktis, mudah difahami & dipraktekkan.', 'Skeptis, kritis, logis, menentukan (determinatif) dan kadang keras kepala.', 'Punya keinginan untuk berkembang serta selalu ingin lebih maju dari orang lain.', 'Kritik & konflik tidak menjadi masalah berarti.']),
                'saran'            =>  json_encode(['Belajarlah mengungkapkan emosi & perasaan Anda.', 'Cobalah untuk lebih terbuka pada dunia luar, banyak bergaul, banyak belajar, banyak membaca, mengunjungi banyak tempat, eksplorasi hal baru, & memperluas wawasan.', 'Hindari perdebatan tidak penting.', 'Belajarlah untuk berempati, memberi perhatian dan lebih peka terhadap orang lain.']),
                'profesi'          => 'Peneliti, Ilmuwan, Insinyur, Teknisi, Pengajar, Profesor, Dokter, Research & Development, Business Analyst, System Analyst, Pengacara, Hakim, Programmers, Posisi Strategis dalam organisasi.',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'type_kepribadian' => 'INFP',
                'deskripsi'        =>  json_encode(['Sangat perhatian dan peka dengan perasaan orang lain.', 'Penuh dengan antusiasme dan kesetiaan, tapi biasanya hanya untuk orang dekat.', 'Peduli pada banyak hal. Cenderung mengambil terlalu banyak dan menyelesaikan sebagian.', 'Cenderung idealis dan perfeksionis.', 'Berpikir win-win solution, mempercayai dan mengoptimalkan orang lain.']),
                'saran'            =>  json_encode(['Belajarlah menghadapi kritik. Jika baik maka kritik itu bisa membangun Anda, namun jika tidak abaikan saja. Jangan ragu pula untuk bertanya dan minta saran.', 'Belajarlah untuk bersikap tegas. Jangan selalu berperasaan dan menyenangkan orang dengan tindakan baik. Bertindak baik itu berbeda dengan bertindak benar.', 'Jangan terlalu menyalahkan diri dan bersikap terlalu keras pada diri sendiri. Kegagalan adalah hal biasa dan semua orang pernah mengalaminya.', 'Jangan terlalu baik pada orang lain tapi melupakan diri sendiri. Anda juga punya tanggungjawab untuk berbuat baik pada diri sendiri.']),
                'profesi'          => 'Penulis, Sastrawan, Konselor, Psikolog, Pengajar, Seniman, Rohaniawan, Bidang Hospitality',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'type_kepribadian' => 'INTP',
                'deskripsi'        =>  json_encode(['Sangat menghargai intelektualitas dan pengetahuan. Menikmati hal-hal teoritis dan ilmiah. Senang memecahkan masalah dengan logika dan analisa.', 'Diam dan menahan diri. Lebih suka bekerja sendiri.', 'Cenderung kritis, skeptis, mudah curiga dan pesimis.', 'Tidak suka memimpin dan bisa menjadi pengikut yang tidak banyak menuntut.', 'Cenderung memiliki minat yang jelas. Membutuhkan karir dimana minatnya bisa berkembang dan bermanfaat.', 'Jika menemukan sesuatu yang menarik minatnya, ia akan sangat serius dan antusias menekuninya.']),
                'saran'            =>  json_encode(['Belajarlah membangun hubungan dengan orang lain.', 'Belajar berempati, mendengar aktif, memberi perhatian dan bertukar pendapat.', 'Relaks. Jangan terlalu banyak berfikir. Nikmati hidup Anda tanpa harus bertanya mengapa dan bagaimana.', 'Cobalah menemukan satu ide, merencanakan dan mewujudkannya.', 'Jangan terlalu sering berganti-ganti ide tetapi tidak satupun yang terwujud.']),
                'profesi'          => 'Ilmuwan, Fotografer, Programmer, Ahli komputer, System Analyst, Penulis Buku Teknis, Ahli Forensik, Jaksa, Pengacara, Teknisi',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'type_kepribadian' => 'ESTP',
                'deskripsi'        =>  json_encode(['Spontan, Aktif, Enerjik, Cekatan, Cepat, Sigap, Antusias, Fun dan penuh variasi.', 'Komunikator, asertif, to the point, ceplas-ceplos, berkarisma, punya interpersonal skill yang baik.', 'Baik dalam pemecahan masalah langsung di tempat. Mampu menghadapi masalah, konflik dan kritik. Tidak khawatir, menikmati apapun yang terjadi.', 'Cenderung untuk menyukai sesuatu yang mekanistis, kegiatan bersama dan olahraga.', 'Mudah beradaptasi, toleran, pada umumnya konservatif tentang nilai-nilai.', 'Tidak suka penjelasan terlalu panjang. Paling baik dalam hal-hal nyata yang dapat dilakukan.']),
                'saran'            =>  json_encode(['Belajarlah memahami perasaan dan pemikiran orang lain terutama saat bicara dengan mereka.', 'Belajarlah untuk sabar, menikmati proses, tidak semua hal bisa dicapai dengan cepat.', 'Sesekali luangkan waktu untuk merenung dan merencanakan masa depan Anda.', 'Cobalah untuk mencatat pengamatan-pengamatan Anda termasuk detailnya.']),
                'profesi'          => 'Marketing, Sales, Polisi, Entrepreneur, Pialang Saham, Technical Support',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'type_kepribadian' => 'ESFP',
                'deskripsi'        =>  json_encode(['Outgoing, easygoing, mudah berteman, bersahabat, sangat sosial, ramah, hangat, & menyenangkan.', 'Optimis, ceria, antusias, fun, menghibur, suka menjadi perhatian.', 'Punya interpersonal skill yang baik, murah hati, mudah simpatik dan mengenali perasaan orang lain.', 'Menghindari konflik dan menjaga keharmonisan suatu hubungan.', 'Mengetahui apa yang terjadi di sekelilingnya dan ikut serta dalam kegiatan tersebut.', 'Sangat baik dalam keadaan yang membutuhkan common sense, tindakan cepat dan ketrampilan praktis.']),
                'saran'            =>  json_encode(['Jangan terburu-buru dalam mengambil keputusan. Belajarlah untuk fokus dan tidak mudah berubah-ubah terutama untuk hal yang penting.', 'Jangan menyenangkan semua orang. Begitu pula sebaliknya, tidak semua orang bisa menyenangkan Anda.', 'Belajarlah menghadapi kritik dan konflik. Jangan lari.', 'Anda punya kecenderungan meterialistis. Hati-hati, tidak semua hal bisa diukur dengan materi ataupun uang.']),
                'profesi'          => 'Entertainer, Seniman, Marketing, Konselor, Designer, Tour Guide, Bidang Anak-anak, Bidang Hospitality',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'type_kepribadian' => 'ENFP',
                'deskripsi'        =>  json_encode(['Ramah, hangat, enerjik, optimis, antusias, semangat tinggi, fun.', 'Imaginatif, penuh ide, kreatif, inovatif.', 'Mampu beradaptasi dengan beragam situasi dan perubahan.', 'Pandai berkomunikasi, senang bersosialisasi & membawa suasana positif.', 'Mudah membaca perasaan dan kebutuhan orang lain.']),
                'saran'            =>  json_encode(['Belajarlah untuk fokus, disiplin, tegas dan konsisten', 'Belajarlah untuk menghadapi konflik dan kritik.', 'Pikirkan kebutuhan diri sendiri. Jangan melupakannya karena terlalu peduli pada kebutuhan orang lain.', 'Jangan terlalu boros. Belajarlah untuk mengelola keuangan sedikit demi sedikit.']),
                'profesi'          => 'Konselor, Psikolog, Entertainer, Pengajar, Motivator, Presenter, Reporter, MC, Seniman, Hospitality',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'type_kepribadian' => 'ENTP',
                'deskripsi'        =>  json_encode(['Gesit, kreatif, inovatif, cerdik, logis, baik dalam banyak hal.', 'Banyak bicara dan punya kemampuan debat yang baik. Bisa berargumentasi untuk senang-senang saja tanpa merasa bersalah.', 'Fleksibel. Punya banyak cara untuk memecahkan masalah dan tantangan.', 'Kurang konsisten. Cenderung untuk melakukan hal baru yang menarik hati setelah melakukan sesuatu yang lain.', 'Punya keinginan kuat untuk mengembangkan diri.']),
                'saran'            =>  json_encode(['Cobalah untuk win-win solution. Jangan ingin menang sendiri.', 'Belajarlah untuk disiplin dan konsisten.', 'Hindari perdebatan tidak penting.', 'Belajarlah untuk sedikit waspada. Seimbangkan cara pandang Anda agar tidak terlalu optimis dan mengambil resiko yang tidak realistis.', 'Belajarlah untuk memberi perhatian pada perasaan orang lain.']),
                'profesi'          => 'Pengacara, Psikolog, Konsultan, Ilmuwan, Aktor,Marketing, Programmer, Fotografer',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'type_kepribadian' => 'ESTJ',
                'deskripsi'        =>  json_encode(['Praktis, realistis, berpegang pada fakta, dengan dorongan alamiah untuk bisnis dan mekanistis.', 'Sangat sistematis, procedural dan terencana.', 'Disiplin dan pekerja keras.', 'Konservatif dan cenderung kaku.', 'Tidak tertarik pada subject yang tidak berguna baginya, tapi dapat menyesuaikan diri jika diperlukan.', 'Senang mengorganisir sesuatu. Bisa menjadi administrator yang baik jika mereka ingat untuk memperhatikan perasaan dan perspektif orang lain.']),
                'saran'            =>  json_encode(['Kurangi keinginan untuk mengontrol dan memaksa orang lain.', 'Belajarlah untuk mengontrol emosi dan amarah Anda.', 'Cobalah untuk introspeksi diri dan meluangkan waktu sejenak untuk merenung.', 'Belajarlah untuk lebih sabar dan rendah hati', 'Belajarlah untuk memahami orang lain.']),
                'profesi'          => 'Militer, Manajer, Polisi, Hakim, Pengacara, Guru, Sales, Auditor, Akuntan, System Analyst',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'type_kepribadian' => 'ESFJ',
                'deskripsi'        =>  json_encode(['Hangat, banyak bicara, populer, dilahirkan untuk bekerjasama, suportif dan anggota kelompok yang aktif.', 'Membutuhkan keseimbangan dan baik dalam menciptakan harmoni.', 'Selalu melakukan sesuatu yang manis bagi orang lain. Kerja dengan baik dalam situasi yang mendukung dan memujinya.', 'Santai, easy going, sederhana, tidak berfikir panjang.', 'Teliti dan rajin merawat apa yang ia miliki.']),
                'saran'            =>  json_encode(['Jangan mengorbankan diri hanya untuk menyenangkan orang lain.', 'Jangan mengukur harga diri Anda dari perlakuan, penghargaan dan pujian orang lain.', 'Mintalah pertimbangan orang lain dalam mengambil keputusan. Belajarlah untuk lebih tegas.', 'Terima tanggungjawab hidup dan belajarlah untuk lebih dewasa. Jangan mengasihani diri sendiri.', 'Hadapi kritik dan konflik, jangan lari.']),
                'profesi'          => 'Perencana Keuangan, Perawat, Guru, Bidang anak-anak, Konselor, Administratif, Hospitality',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'type_kepribadian' => 'ENFJ',
                'deskripsi'        =>  json_encode(['Kreatif, imajinatif, peka, sensitive, loyal.', 'Pada umumnya peduli pada apa kata orang atau apa yang orang lain inginkan dan cenderung melakukan sesuatu dengan memperhatikan perasaan orang lain.', 'Pandai bergaul, meyakinkan, ramah, fun, populer, simpatik. Responsif pada kritik dan pujian.', 'Menyukai variasi dan tantangan baru.', 'Butuh apresiasi dan penerimaan.']),
                'saran'            =>  json_encode(['Jangan mengorbankan diri hanya untuk menyenangkan orang lain.', 'Jangan mengukur harga diri Anda dari perlakuan orang lain. Jangan mudah kecewa jika mereka tidak seperti yang Anda inginkan.', 'Belajarlah untuk tegas dan mengambil keputusan. Menghadapi kritik dan konflik.', 'Jangan terlalu bersikap keras terhadap diri sendiri.']),
                'profesi'          => 'Konsultan, Psikolog, Konselor, Pengajar, Marketing, HRD, Event Coordinator, Entertainer, Penulis, Motivator',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'type_kepribadian' => 'ENTJ',
                'deskripsi'        =>  json_encode(['Tegas, asertif, to the point, jujur terus terang, obyektif, kritis, & punya standard tinggi.', 'Dominan, kuat kemauannya, perfeksionis dan kompetitif.', 'Tangguh, disiplin, dan sangat menghargai komitmen.', 'Cenderung menutupi perasaan dan menyembunyikan kelemahan.', 'Berkarisma, komunikasi baik, mampu menggerakkan orang.', 'Berbakat pemimpin.']),
                'saran'            =>  json_encode(['Belajarlah untuk relaks. Tidak perlu perfeksionis dan selalu kompetitif dengan semua orang.', 'Ungkapkan perasaan Anda. Menyatakan perasaan bukanlah kelemahan.', 'Belajarlah mengelola emosi Anda. Jangan mudah marah.', 'Belajarlah untuk menghargai dan mengapresiasi orang lain.', 'Jangan terlalu arogan dan menganggap remeh orang lain. Lihat sisi positifnya. Jangan hanya melihat benar dan salah saja.']),
                'profesi'          => 'Manager, Pemimpin Perusahaan, Kepala Sekolah, Pemimpin Yayasan',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ];
        DB::table('mbti_analisis')->insert($dataAnalisis);

        ## MASTER NILAI
        $dtSkor   = [1, 35, 40, 41, 44, 45, 46, 49, 50, 53, 54, 55, 58, 59, 60, 64];
        $dtTarget = [5, 15, 14, 11, 2, 7, 10, 16, 4, 6, 13, 12, 1, 8, 9, 3];

        $dtMaster = [];
        foreach ($dtSkor as $index => $value) {
            $nomor = $index + 1;
            $dtMaster[] = [
                'id'          => $nomor,
                'total_skor'  => $value,
                'analisis_id' => $dtTarget[$index],
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        MbtiMaster::insert($dtMaster);
    }
}
