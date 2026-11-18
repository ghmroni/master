<?php

namespace Database\Seeders;

use App\Models\Project;

use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Project::truncate();
        ## ================================================================================================ GHM
        $ghm = Project::create([
            'name' => 'PT GRHA HUMANINDO MANAJEMEN',
            'code' => 100,
            'parent_id' => null,
            'inisial' => 'GHM Pusat',
            'logo' => null,
        ]);
        # PUSAT
        $ghm1 = $ghm->children()->create([
            'name' => 'KANTOR PUSAT',
        ]);
        $departments = ['MANAJEMEN', 'DIVISI HRGA', 'DIVISI OPERASIONAL', 'DIVISI KEUANGAN', 'DIVISI IT'];
        foreach ($departments as $dept) {
            $ghm1->children()->create([
                'name'      => $dept,
                'parent_id' => $ghm1->id,
            ]);
        }
        # PERWAKILAN
        $ghm2 = $ghm->children()->create([
            'name' => 'KANTOR PERWAKILAN',
        ]);
        $perwakilan = [
            'PERWAKILAN BALI',
            'PERWAKILAN JATIM',
            'PERWAKILAN SUMATERA',
            'PERWAKILAN KALIMANTAN',
            'PERWAKILAN CENGKARENG',
        ];
        foreach ($perwakilan as $wakil) {
            $ghm2->children()->create([
                'name'      => $wakil,
                'parent_id' => $ghm2->id,
            ]);
        }
        ## ================================================================================================ AUTO
        $auto = Project::create([
            'name' => 'ASTRA INTERNATIONAL PT',
            'code' => 101,
            'parent_id' => null,
            'inisial' => 'AUTO 2000',
            'logo' => null,
        ]);
        $auto1 = $auto->children()->create([
            'name' => 'BALI',
        ]);
        $autobali = ['DENPASAR - COKRO', 'GIANYAR', 'SANUR', 'SINGARAJA', 'TABANAN VSP'];
        foreach ($autobali as $row) {
            $auto1->children()->create([
                'name'      => $row,
                'parent_id' => $auto1->id,
            ]);
        }
        $auto2 = $auto->children()->create([
            'name' => 'BANDUNG',
        ]);
        $autobdg = ['ASIA AFRIKA', 'CIBIRU BP', 'CIBIRU VSP', 'CIREBON', 'INDRAMAYU', 'KUNINGAN', 'MAJALENGKA', 'PASTEUR', 'RANCAEKEK VSP', 'SETIABUDI', 'SOEKARNO HATTA VSP', 'SOREANG', 'SUCI'];
        foreach ($autobdg as $row) {
            $auto2->children()->create([
                'name'      => $row,
                'parent_id' => $auto2->id,
            ]);
        }
        $auto3 = $auto->children()->create([
            'name' => 'DKI 2',
        ]);
        $autodki = ['BOGOR - SILIWANGI', 'BOGOR - YASMIN', 'BOGOR - YASMIN BP', 'BOGOR - DRAMAGA', 'CIBINONG', 'CIBINONG PDC', 'CIKAMPEK', 'GRAND DEPOK CITY', 'KARAWANG BP', 'KARAWANG VSP', 'PURWAKARTA', 'SUKABUMI'];
        foreach ($autodki as $row) {
            $auto3->children()->create([
                'name'      => $row,
                'parent_id' => $auto3->id,
            ]);
        }
        $auto4 = $auto->children()->create([
            'name' => 'JAWA TIMUR',
        ]);
        $autojatim = ['A. YANI', 'BANYUWANGI', 'BASUKI RAHMAT', 'JEMBER', 'JEMUR SARI', 'JOMBANG', 'KEDIRI HASANUDIN', 'KEDIRI SUHARMAJI', 'KENJERAN', 'KENJERAN BP', 'KERTAJAYA', 'MADIUN', 'MALANG SINGOSARI', 'MALANG SUKUN', 'MALANG SUTOYO', 'MANYAR', 'PASURUAN', 'PECINDILAN', 'PROBOLINGGO', 'SUNGKONO', 'TUBAN', 'TULUNGAGUNG', 'WARU', 'WIYUNG'];
        foreach ($autojatim as $row) {
            $auto4->children()->create([
                'name'      => $row,
                'parent_id' => $auto4->id,
            ]);
        }
        $auto5 = $auto->children()->create([
            'name' => 'KALIMANTAN',
        ]);
        $autokalimantan = ['BONTANG', 'BPP - SUDIRMAN VSP', 'M.YAMIN', 'MT. HARYONO', 'P. HIDAYATULLAH', 'PONTIANAK', 'SANGATTA', 'TENGGARONG'];
        foreach ($autokalimantan as $row) {
            $auto5->children()->create([
                'name'      => $row,
                'parent_id' => $auto5->id,
            ]);
        }
        $auto6 = $auto->children()->create([
            'name' => 'LAMPUNG',
        ]);
        $autolampung = ['KALIANDA', 'KOTABUMI', 'RADEN INTAN', 'RAJABASA', 'TULANG BAWANG', 'WAY HALIM'];
        foreach ($autolampung as $row) {
            $auto6->children()->create([
                'name'      => $row,
                'parent_id' => $auto6->id,
            ]);
        }
        $auto7 = $auto->children()->create([
            'name' => 'MEDAN',
        ]);
        $automedan = ['AMPLAS BP', 'AMPLAS VSP', 'BINJAI', 'GATOT SUBROTO', 'KISARAN', 'KRAKATAU', 'MARELAN', 'PADANG BUKIT TINGGI', 'PADANG BY PASS VSP', 'PADANG KHATIB SULAIMAN', 'PANCING', 'RANTAUPRAPAT', 'SISINGAMANGARAJA', 'TANJUNG MORAWA'];
        foreach ($automedan as $row) {
            $auto7->children()->create([
                'name'      => $row,
                'parent_id' => $auto7->id,
            ]);
        }
        $auto8 = $auto->children()->create([
            'name' => 'PALEMBANG',
        ]);
        $autoplm = ['A. YANI', 'BATURAJA', 'MUSI BANYUASIN', 'TJ. API API VSP', 'VETERAN'];
        foreach ($autoplm as $row) {
            $auto8->children()->create([
                'name'      => $row,
                'parent_id' => $auto8->id,
            ]);
        }
        ## ================================================================================================ ASTRAGRAPHIA
        $astragrapia = Project::create([
            'name' => 'ASTRAGRAPHIA',
            'code' => 102,
            'parent_id' => null,
            'inisial' => 'AGIT',
            'logo' => null,
        ]);
        $astragrapia1 = $astragrapia->children()->create([
            'name' => 'BALI',
        ]);
        $astragrapia1->Parent()->create(['name' => 'DENPASAR', 'parent_id' => $astragrapia1->id]);
        ## ================================================================================================ BASARNAS
        $bas = Project::create([
            'name' => 'BASARNAS',
            'code' => 103,
            'parent_id' => null,
            'inisial' => 'BAS',
            'logo' => null,
        ]);
        $bas1 = $bas->children()->create([
            'name' => 'BALI',
        ]);
        $basbali = ['BULELENG', 'DENPASAR', 'JEMBRANA', 'KARANGASEM', 'NUSA PENIDA'];
        foreach ($basbali as $row) {
            $bas1->children()->create([
                'name'      => $row,
                'parent_id' => $bas1->id,
            ]);
        }
        $bas2 = $bas->children()->create([
            'name' => 'MALUKU UTARA',
        ]);
        $basmalut = ['BACAN', 'MOROTAI', 'SANANA', 'TERNATE', 'TOBELO'];
        foreach ($basmalut as $row) {
            $bas2->children()->create([
                'name'      => $row,
                'parent_id' => $bas2->id,
            ]);
        }
        $bas3 = $bas->children()->create([
            'name' => 'NUSA TENGGARA TIMUR',
        ]);
        $basntt = ['ALOR', 'ENDE', 'LABOAN BAJO', 'MAUMERE'];
        foreach ($basntt as $row) {
            $bas3->children()->create([
                'name'      => $row,
                'parent_id' => $bas3->id,
            ]);
        }
        ## ================================================================================================ GPR PADANG
        $padang = Project::create([
            'name' => 'GAPURA ANGKASA - MINANGKABAU',
            'code' => 104,
            'parent_id' => null,
            'inisial' => 'PADANG',
            'logo' => null,
        ]);
        $padang1 = $padang->children()->create([
            'name' => 'PADANG',
        ]);
        $padangpdg = ['AIC', 'DAILY WORKER', 'LUL', 'SDM', 'TAD'];
        foreach ($padangpdg as $row) {
            $padang1->children()->create([
                'name'      => $row,
                'parent_id' => $padang1->id,
            ]);
        }
        ## ================================================================================================ GPR BALI
        $bali = Project::create([
            'name' => 'GAPURA ANGKASA - NGURAH RAI',
            'code' => 105,
            'parent_id' => null,
            'inisial' => 'BALI',
            'logo' => null,
        ]);
        $bali1 = $bali->children()->create([
            'name' => 'BALI',
        ]);
        $bali1->Parent()->create(['name' => 'DENPASAR', 'parent_id' => $bali1->id]);
        ## ================================================================================================ GPR BPN
        $bpn = Project::create([
            'name' => 'GAPURA ANGKASA - SEPINGGAN',
            'code' => 106,
            'parent_id' => null,
            'inisial' => 'BPN',
            'logo' => null,
        ]);
        $bpn1 = $bpn->children()->create([
            'name' => 'BALIKPAPAN',
        ]);
        $bpnbp = ['AIRCRAFT CLEANING', 'LOADING UNLOADING'];
        foreach ($bpnbp as $row) {
            $bpn1->children()->create([
                'name'      => $row,
                'parent_id' => $bpn1->id,
            ]);
        }
        $bpn2 = $bpn->children()->create([
            'name' => 'HAJI',
        ]);
        $bpnhaji = ['AVSEC', 'LOADING UNLOADING', 'PAX & BAGGAGE'];
        foreach ($bpnhaji as $row) {
            $bpn2->children()->create([
                'name'      => $row,
                'parent_id' => $bpn2->id,
            ]);
        }
        ## ================================================================================================ GPR CGK
        $cgk = Project::create([
            'name' => 'GAPURA ANGKASA - SOEKARNO HATTA',
            'code' => 107,
            'parent_id' => null,
            'inisial' => 'CGK',
            'logo' => null,
        ]);
        $cgk1 = $cgk->children()->create([
            'name' => 'CLEANING SERVICE',
        ]);
        $cgk1->Parent()->create(['name' => 'ALL AREA', 'parent_id' => $cgk1->id]);
        $cgk2 = $cgk->children()->create([
            'name' => 'MB',
        ]);
        $cgkmb = ['ALL MPA', 'AVIO', 'JOUMPA'];
        foreach ($cgkmb as $row) {
            $cgk2->children()->create([
                'name'      => $row,
                'parent_id' => $cgk2->id,
            ]);
        }
        $cgk3 = $cgk->children()->create([
            'name' => 'MC',
        ]);
        $cgkmc = ['CITILINK DOM', 'CITILINK INTER'];
        foreach ($cgkmc as $row) {
            $cgk3->children()->create([
                'name'      => $row,
                'parent_id' => $cgk3->id,
            ]);
        }
        $cgk4 = $cgk->children()->create([
            'name' => 'MF',
        ]);
        $cgkmf = ['ALL MPA', 'CA', 'CI', 'CZ', 'ET HY MS', 'GJ 3U', 'HU', 'JAL', 'KE', 'KLM & SRILANKA', 'MF', 'RBA', 'SC NX', 'SQOOT / VIET JET / INDIGO', 'TG', 'VN'];
        foreach ($cgkmf as $row) {
            $cgk4->children()->create([
                'name'      => $row,
                'parent_id' => $cgk4->id,
            ]);
        }
        $cgk5 = $cgk->children()->create([
            'name' => 'ML',
        ]);
        $cgkml = ['ALL AREA', 'PELITA AIR SERVICE'];
        foreach ($cgkml as $row) {
            $cgk5->children()->create([
                'name'      => $row,
                'parent_id' => $cgk5->id,
            ]);
        }
        $cgk6 = $cgk->children()->create([
            'name' => 'MR',
        ]);
        $cgkmr = ['GARUDA DOMESTIK', 'GARUDA INTERNATIONAL', 'MPA'];
        foreach ($cgkmr as $row) {
            $cgk6->children()->create([
                'name'      => $row,
                'parent_id' => $cgk6->id,
            ]);
        }
        $cgk7 = $cgk->children()->create([
            'name' => 'MT',
        ]);
        $cgk7->Parent()->create(['name' => 'GARUDA DOMESTIK', 'parent_id' => $cgk7->id]);
        $cgk8 = $cgk->children()->create([
            'name' => 'WCHR',
        ]);
        $cgkwchr = ['CITILINK / PELITA / MPA', 'GARUDA'];
        foreach ($cgkwchr as $row) {
            $cgk8->children()->create([
                'name'      => $row,
                'parent_id' => $cgk8->id,
            ]);
        }
        $cgk9 = $cgk->children()->create([
            'name' => 'WHU',
        ]);
        $cgk9->Parent()->create(['name' => 'ALL AREA', 'parent_id' => $cgk9->id]);
        $cgk10 = $cgk->children()->create([
            'name' => 'HAJI',
        ]);
        $cgk10->Parent()->create(['name' => 'GARUDA', 'parent_id' => $cgk10->id]);
        ## ================================================================================================ GPR PLM
        $plm = Project::create([
            'name' => 'GAPURA ANGKASA - SULTAN MAHMUD BADARUDDIN II',
            'code' => 108,
            'parent_id' => null,
            'inisial' => 'PLM',
            'logo' => null,
        ]);
        $plm1 = $plm->children()->create([
            'name' => 'PALEMBANG',
        ]);
        $plmb = ['KEMITRAAN (HAJI)', 'LOADING UNLOADING', 'SDM'];
        foreach ($plmb as $row) {
            $plm1->children()->create([
                'name'      => $row,
                'parent_id' => $plm1->id,
            ]);
        }
        ## ================================================================================================ GPR PKU
        $pku = Project::create([
            'name' => 'GAPURA ANGKASA - SULTAN SYARIF KASIM II',
            'code' => 109,
            'parent_id' => null,
            'inisial' => 'PKU',
            'logo' => null,
        ]);
        $pku1 = $pku->children()->create([
            'name' => 'PEKANBARU',
        ]);
        $dtpku = ['AIRCRAFT CLEANING', 'DAILY WORKER', 'LOADING UNLOADING', 'SDM', 'SDM TAD'];
        foreach ($dtpku as $row) {
            $pku1->children()->create([
                'name'      => $row,
                'parent_id' => $pku1->id,
            ]);
        }
        ## ================================================================================================ GPR BANJAR
        $bdj = Project::create([
            'name' => 'GAPURA ANGKASA - SYAMSUDIN NOOR',
            'code' => 110,
            'parent_id' => null,
            'inisial' => 'BDJ',
            'logo' => null,
        ]);
        $bdj1 = $bdj->children()->create([
            'name' => 'GROUND HANDLING',
        ]);
        $dtbdj = ['ADM MEKANIK', 'ADMINISTRASI', 'AIRCRAFT CLEANING', 'AVSEC', 'CARGO / GPL', 'DEPCO', 'FLIGHT DISPATCH', 'GSE MEKANIK', 'GSE OPERATOR', 'KEUANGAN', 'LOAD MASTER', 'LOADING UNLOADING', 'PAX & BAGGAGE OFFICER', 'RAMP'];
        foreach ($dtbdj as $row) {
            $bdj1->children()->create([
                'name'      => $row,
                'parent_id' => $bdj1->id,
            ]);
        }
        $bdj2 = $bdj->children()->create([
            'name' => 'KEMITRAAN',
        ]);
        $dtmitra = ['AIC', 'LUL', 'PB'];
        foreach ($dtmitra as $row) {
            $bdj2->children()->create([
                'name'      => $row,
                'parent_id' => $bdj2->id,
            ]);
        }
        ## ================================================================================================ IMCP
        $imcp = Project::create([
            'name' => 'IMCP',
            'code' => 111,
            'parent_id' => null,
            'inisial' => 'IMCP',
            'logo' => null,
        ]);
        $imcp1 = $imcp->children()->create([
            'name' => 'IMCP',
        ]);
        $imcp1->Parent()->create(['name' => 'KEDIAMAN BPK. SOETJIPTO', 'parent_id' => $imcp1->id]);
        ## ================================================================================================ KARYA MAKMUR
        $ciputra = Project::create([
            'name' => 'KARYA MAKMUR',
            'code' => 112,
            'parent_id' => null,
            'inisial' => 'CIPUTRA',
            'logo' => null,
        ]);
        $ciputra1 = $ciputra->children()->create([
            'name' => 'DENPASAR',
        ]);
        $ciputra1->Parent()->create(['name' => 'CITRALAND RESIDENCE', 'parent_id' => $ciputra1->id]);
        ## ================================================================================================ PLN
        $pln = Project::create([
            'name' => 'PLN NUSANTARA POWER',
            'code' => 113,
            'parent_id' => null,
            'inisial' => 'PLN NP',
            'logo' => null,
        ]);
        $pln1 = $pln->children()->create([
            'name' => 'JAWA TIMUR',
        ]);
        $pln1->Parent()->create(['name' => 'TANJUNG AWAR-AWAR', 'parent_id' => $pln1->id]);
        ## ================================================================================================ POLTEKPAR
        $poltekpar = Project::create([
            'name' => 'POLTEKPAR',
            'code' => 114,
            'parent_id' => null,
            'inisial' => 'POLTEKPAR',
            'logo' => null,
        ]);
        $poltekpar1 = $poltekpar->children()->create([
            'name' => 'POLTEKPAR LOMBOK',
        ]);
        $poltekpar1->Parent()->create(['name' => 'PRAYA', 'parent_id' => $poltekpar1->id]);
        ## ================================================================================================ SITC
        $sitc = Project::create([
            'name' => 'SITC INDONESIA',
            'code' => 115,
            'parent_id' => null,
            'inisial' => 'SITC',
            'logo' => null,
        ]);
        $sitc1 = $sitc->children()->create([
            'name' => 'JAKARTA',
        ]);
        $sitc1->Parent()->create(['name' => 'GEDUNG GAMA TOWER', 'parent_id' => $sitc1->id]);
        ## ================================================================================================ TAM
        $tam = Project::create([
            'name' => 'TOYOTA ASTRA MOTOR',
            'code' => 116,
            'parent_id' => null,
            'inisial' => 'TAM',
            'logo' => null,
        ]);
        $tam1 = $tam->children()->create([
            'name' => 'JAWA BARAT',
        ]);
        $tamjabar = ['PDC CIBITUNG', 'PDC CIBITUNG BACKUP', 'PDC KARAWANG', 'PDC KARAWANG BACKUP'];
        foreach ($tamjabar as $row) {
            $tam1->children()->create([
                'name'      => $row,
                'parent_id' => $tam1->id,
            ]);
        }
        $tam2 = $tam->children()->create([
            'name' => 'JAWA TIMUR',
        ]);
        $tam2->Parent()->create(['name' => 'PDC NGORO', 'parent_id' => $tam2->id]);
        ## ================================================================================================ TAM DOC
        $tamdoc = Project::create([
            'name' => 'TOYOTA ASTRA MOTOR (DOC HANDLING)',
            'code' => 117,
            'parent_id' => null,
            'inisial' => 'TAM DOCHAND',
            'logo' => null,
        ]);
        $tamdoc1 = $tamdoc->children()->create([
            'name' => 'JAWA TIMUR',
        ]);
        $tamdoc1->Parent()->create(['name' => 'PDC NGORO', 'parent_id' => $tamdoc1->id]);
        ## ================================================================================================ TAM DOC
        $tmmin = Project::create([
            'name' => 'TMMIN',
            'code' => 118,
            'parent_id' => null,
            'inisial' => 'TMMIN',
            'logo' => null,
        ]);
        $tmmin1 = $tmmin->children()->create([
            'name' => 'JAWA BARAT',
        ]);
        $tmmin1->Parent()->create(['name' => 'CIBITUNG COMMON YARD', 'parent_id' => $tmmin1->id]);
        ## ================================================================================================ GPR BANDUNG
        $gprBandung = Project::create([
            'name' => 'GAPURA ANGKASA - HUSEIN SASTRANEGARA',
            'code' => 119,
            'parent_id' => null,
            'inisial' => 'BDO',
            'logo' => null,
        ]);
        $bdo = $gprBandung->children()->create([
            'name' => 'BDO',
        ]);
        $bdo->Parent()->create(['name' => 'ALL AREA', 'parent_id' => $bdo->id]);
        // $bdg = ['CITILINK', 'GARUDA', 'MPA'];
        // foreach ($bdg as $row) {
        //     $child = $gprBandung->children()->create([
        //         'name'      => $row,
        //         'parent_id' => $gprBandung->id,
        //     ]);

        //     $child->children()->create([
        //         'name'      => 'ALL AREA',
        //         'parent_id' => $child->id,
        //     ]);
        // }
    }
}
