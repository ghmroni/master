<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>HASIL INTERVIEW</title>
    <style>
        body {
            /* font-family: Arial, sans-serif; */
            font-size: 12px;
            margin-top: 75px;
            margin-bottom: 20px;
        }

        .ql-align-justify {
            text-align: justify;
        }

        p {
            text-indent: 20px;
        }

        p.line-height {
            line-height: 0.2;
        }

        tr,
        td {
            padding: 2px;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 100px;
        }

        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
        }

        /* .header {
            width: 100%;
            padding-bottom: 10px;
        } */

        /* .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            text-align: center;
            font-family: Arial, Helvetica, sans-serif;
            border-top: 2px solid #000;
            padding-top: 5px;
        } */

        .clear {
            clear: both;
        }

        @page {
            margin: 15mm;
        }
    </style>

</head>

@php
    use Carbon\Carbon;
@endphp

<body>

    <header>
        <div style="float: left; width: 10%;height:55%;">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('assets/img/favicon/logo.png'))) }}"
                style="width:60px;height:100%;text-align:left" />
        </div>

        <div style="float: left;width: 90%;height: 6%;">
            <div
                style="width: 100%;text-align: center;margin-top:10px;font-weight: bold;font-size:24px;font-family: Arial, Helvetica, sans-serif;letter-spacing: 4.6px;">
                PT. GRHA HUMANINDO MANAJEMEN
            </div>
            <div
                style="width: 100%;margin-top:7px;text-align: center; font-weight: bold;font-size:11px;font-family: Segoe MDL2 Assets;font-style: italic;letter-spacing: 20px;margin-top:1;">
                safety - solve - secure
            </div>
        </div>

    </header>

    <div class="content" style="margin-bottom: 10px">

        {{-- ======================================================================================================================================== CFIT --}}
        @if ($data)
            <div
                style="background:#e9e9eb;border:1px solid #000;height:25px;text-align:center;padding-top:7px;font-size:12px;font-weight:bold;">
                <center>
                    FORMULIR PENILAIAN HASIL WAWANCARA
                </center>
            </div>
            <div class="card-body" style="border:1px solid #000;border-radius:0px 0px 5px 5px ;padding:5px;">
                <table border="0" class="table" style="width:100%;font-size:12px;border-collapse: collapse;">
                    <tr>
                        <th width="15%" style="text-align: left">Nama Lengkap</th>
                        <th width="1%" class="text-center">:</th>
                        <td width="35%" class="text-muted">{{ $data->namalengkap ?? null }}</td>
                        <th width="15%" style="text-align: left">Pendidikan</th>
                        <th width="1%" class="text-center">:</th>
                        <td class="text-muted">{{ $data->pendidikan_terakhir ?? null }}</td>
                    </tr>
                    <tr>
                        <th style="text-align: left">Tanggal Lahir</th>
                        <th class="text-center">:</th>
                        <td class="text-muted">
                            {{ strtoupper(Carbon::parse($data->tgllahir)->translatedFormat('d F Y')) }}
                        </td>
                        <th style="text-align: left">Kategori Pelamar</th>
                        <th class="text-center">:</th>
                        <td class="text-muted">{{ $data->kategori }}</td>
                    </tr>
                    <tr>
                        <th style="text-align: left">Berat Badan</th>
                        <th class="text-center">:</th>
                        <td class="text-muted">
                            {{ $data->bb ?? 0 }} cm
                        </td>
                        <th style="text-align: left">Tinggi Badan</th>
                        <th class="text-center">:</th>
                        <td class="text-muted">{{ $data->tb ?? 0 }} cm
                        </td>
                    </tr>

                </table>
                <hr />

                <!-- Table-->
                <table border="1" class="table"
                    style="width:100%;font-size:12px;border: 1px solid black;border-collapse: collapse;">
                    <thead class="text-center" style="background:#efefef;">
                        <tr>
                            <th style="vertical-align: middle;" rowspan="2"><b>NO</b></th>
                            <th style="vertical-align: middle;" rowspan="2"><b>DIMENSI</b></th>
                            <th style="vertical-align: middle;" rowspan="2" width="57%"><b>URAIAN</b></th>
                            <th colspan="4"><b>WAWANCARA</b></th>
                        </tr>
                        <tr>
                            <th width="4%"><b>1</b></th>
                            <th width="4%"><b>2</b></th>
                            <th width="4%"><b>3</b></th>
                            <th width="4%"><b>4</b></th>
                        </tr>

                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align:center;" width="4%">1</td>
                            <td>Pendidikan Formal</td>
                            <td style="text-align: justify">Tingkat pendidikan (formal) yang dapat menunjang posisi yang
                                dilamar</td>
                            @for ($i = 1; $i <= 4; $i++)
                                <td style="text-align:center;font-family: DejaVu Sans;">
                                    @if ($nilai[0] == $i)
                                        &#10004;
                                    @endif
                                </td>
                            @endfor
                        </tr>
                        <tr>
                            <td style="text-align:center;">2</td>
                            <td>Pengalaman Kerja</td>
                            <td style="text-align: justify">Kesesuaian antara berbagai hal yang pernah ditangani dalam
                                pekerjaan sebelumnya dengan
                                pekerjaan yang dilamar</td>
                            @for ($i = 1; $i <= 4; $i++)
                                <td style="text-align:center;font-family: DejaVu Sans;">
                                    @if ($nilai[1] == $i)
                                        &#10004;
                                    @endif
                                </td>
                            @endfor
                        </tr>
                        <tr>
                            <td style="text-align:center;">3</td>
                            <td>Pengetahuan Teknis</td>
                            <td style="text-align: justify">Pengetahuan praktis atau teoritis yang dikuasai berkenaan
                                dengan posisi yang dilamar
                            </td>
                            @for ($i = 1; $i <= 4; $i++)
                                <td style="text-align:center;font-family: DejaVu Sans;">
                                    @if ($nilai[2] == $i)
                                        &#10004;
                                    @endif
                                </td>
                            @endfor
                        </tr>
                        <tr>
                            <td style="text-align:center;">4</td>
                            <td>Keterampilan Teknis</td>
                            <td style="text-align: justify">Kemampuan mengatasi permasalahan yang mungkin terjadi pada
                                posisi yang dilamar</td>
                            @for ($i = 1; $i <= 4; $i++)
                                <td style="text-align:center;font-family: DejaVu Sans;">
                                    @if ($nilai[3] == $i)
                                        &#10004;
                                    @endif
                                </td>
                            @endfor
                        </tr>
                        <tr>
                            <td style="text-align:center;">5</td>
                            <td>Motivasi</td>
                            <td style="text-align: justify">Menunjukan besarnya semangat serta minat terhadap kondisi
                                pekerjaan yang dilamar</td>
                            @for ($i = 1; $i <= 4; $i++)
                                <td style="text-align:center;font-family: DejaVu Sans;">
                                    @if ($nilai[4] == $i)
                                        &#10004;
                                    @endif
                                </td>
                            @endfor
                        </tr>
                        <tr>
                            <td style="text-align:center;">6</td>
                            <td>Kerjasama (Team Work)</td>
                            <td style="text-align: justify">Kemampuan beradaptasi dan bekerjasama dengan rekan kerja
                            </td>
                            @for ($i = 1; $i <= 4; $i++)
                                <td style="text-align:center;font-family: DejaVu Sans;">
                                    @if ($nilai[5] == $i)
                                        &#10004;
                                    @endif
                                </td>
                            @endfor
                        </tr>
                        <tr>
                            <td style="text-align:center;">7</td>
                            <td>Etika (Sopan Santun)</td>
                            <td style="text-align: justify">Menunjukan sikap sopan santun dalam bertutur kata maupun
                                bertingkah laku</td>
                            @for ($i = 1; $i <= 4; $i++)
                                <td style="text-align:center;font-family: DejaVu Sans;">
                                    @if ($nilai[6] == $i)
                                        &#10004;
                                    @endif
                                </td>
                            @endfor
                        </tr>
                        <tr>
                            <td style="text-align:center;">8</td>
                            <td>Kemampuan Komunikasi</td>
                            <td style="text-align: justify">Kemampuan mengungkapkan ide secara sistematis dan jelas
                                sehingga dapat dipahami dengan
                                benar oleh orang lain</td>
                            @for ($i = 1; $i <= 4; $i++)
                                <td style="text-align:center;font-family: DejaVu Sans;">
                                    @if ($nilai[7] == $i)
                                        &#10004;
                                    @endif
                                </td>
                            @endfor
                        </tr>
                        <tr>
                            <td style="text-align:center;">9</td>
                            <td>Kemampuan Bahasa Asing *<br>
                                <strong>{{ $data->bahasa_asing ?? null }}</strong>
                            </td>
                            <td style="text-align: justify">Kemampuan dalam berbahasa asing yang dapat dipahami dengan
                                benar oleh orang lain</td>
                            @for ($i = 1; $i <= 4; $i++)
                                <td style="text-align:center;font-family: DejaVu Sans;">
                                    @if ($nilai[8] == $i)
                                        &#10004;
                                    @endif
                                </td>
                            @endfor
                        </tr>
                        <tr>
                            <td style="text-align:center;">10</td>
                            <td>Penampilan Diri</td>
                            <td style="text-align: justify">Cara berpakaian, kerapihan, serta keadaan fisik secara
                                umum, ada tidaknya cacat fisik
                            </td>
                            @for ($i = 1; $i <= 4; $i++)
                                <td style="text-align:center;font-family: DejaVu Sans;">
                                    @if ($nilai[9] == $i)
                                        &#10004;
                                    @endif
                                </td>
                            @endfor
                        </tr>
                        <tr>
                            <td style="text-align:center;">11</td>
                            <td>Kemampuan Memimpin **</td>
                            <td style="text-align: justify">Kemampuan untuk mengorganisif orang lain dan bawahan yang
                                berada di bawah pengawasannya
                            </td>
                            @for ($i = 1; $i <= 4; $i++)
                                <td style="text-align:center;font-family: DejaVu Sans;">
                                    @if ($nilai[10] == $i)
                                        &#10004;
                                    @endif
                                </td>
                            @endfor
                        </tr>
                        <tr>
                            <td style="text-align:center;">12</td>
                            <td>Pemeriksaan Referensi ***</td>
                            <td style="text-align: justify">Riwayat pekerjaan kandidiat di perusahaan sebelumnya</td>
                            @for ($i = 1; $i <= 4; $i++)
                                <td style="text-align:center;font-family: DejaVu Sans;">
                                    @if ($nilai[11] == $i)
                                        &#10004;
                                    @endif
                                </td>
                            @endfor
                        </tr>

                    </tbody>

                </table>

                <!--keterangan-->
                <table border="0"
                    style="width:100%;font-size:10px;margin-top:5px;border-collapse: collapse;margin-top:10px">
                    <tr>
                        <th colspan="4" width="50%" style="text-align:left;"><b>Keterangan Nilai</b></th>
                        <th colspan="2" style="text-align:left;"><b>Petunjuk Penilaian : (Isi dengan Menggunakan
                                tanda <i class="fa fa-check-circle"></i>)</b></th>
                    </tr>
                    <tr>
                        <td width="3%" class="text-center">1</td>
                        <td width="8%">Kurang</td>
                        <td width="3%" class="text-center">3</td>
                        <td>Baik</td>
                        <td width="3%" class="text-center">1</td>
                        <td>(*) Lingkari yang sesuai / sebutkan yang lainnya</td>
                    </tr>
                    <tr>
                        <td class="text-center">2</td>
                        <td>Cukup</td>
                        <td class="text-center">4</td>
                        <td>Sangat Baik</td>
                        <td class="text-center">2</td>
                        <td>(**) Hanya untuk pangkat Supervisor atau setara ke atas</td>
                    </tr>
                    <tr>
                        <td colspan="4">&nbsp;</td>
                        <td class="text-center">3</td>
                        <td>(***) Hanya untuk pangkat Manager atau setara ke atas</td>
                    </tr>
                </table>

                <small style="font-weight:bold;">
                    Kesimpulan dan Saran :
                </small>
                <!-- Catatan -->
                <table border="0" class="table"
                    style="width:100%;font-size:12px;border-collapse: collapse;padding:5px;">
                    <tr>
                        <td width="17%">&nbsp;</td>
                        <td width="2%" class="text-center" style="font-family:DejaVu Sans;">
                            @if ($data->hasil_wawancara == 'disarankan')
                                &#9745;
                            @else
                                &#9744;
                            @endif
                        </td>
                        <td width="20%" style="padding-left:5px;">Dapat Disarankan</td>
                        <td width="2%" class="text-center" style="font-family:DejaVu Sans;">
                            @if ($data->hasil_wawancara == 'dipertimbangkan')
                                &#9745; {{-- ☑ kotak dengan ceklis --}}
                            @else
                                &#9744; {{-- ☐ kotak kosong --}}
                            @endif
                        </td>
                        <td width="20%" style="padding-left:5px;">Dipertimbangkan</td>
                        <td width="2%" class="text-center" style="font-family:DejaVu Sans;">
                            @if ($data->hasil_wawancara == 'tidak disarankan')
                                &#9745;
                            @else
                                &#9744;
                            @endif
                        </td>
                        <td width="20%" style="padding-left:5px;">Tidak Disarankan</td>
                        <td width="17%">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="8">
                            <div style="padding: 10px; border:1px solid #000;border-radius:9px;text-align: justify">
                                {{ $data->catatan_wawancara ?? null }}
                            </div>
                        </td>
                    </tr>

                </table>

            </div>

            <hr style="height:2px;border:none;color:#333;background-color:#333;">
            <small style="font-size:9px;font-style:italic;">
                <b>Catatan</b> : Hasil penilaian wawancara ini adalah sah dan benar dikeluarkan oleh PT Grha
                Humanindo Manajemen dan diujikan oleh {{ ucwords(strtolower($data->fullname)) }} pada
                {{ Carbon::parse($data->created_wawancara)->translatedFormat('l, d F Y') }}
            </small>
        @else
            <h4>Data tidak ditemukan.</h4>
        @endif
    </div>

    <footer>
        <hr style="height:2px;border:none;color:#333;background-color:#333;">
        <table width="100%"
            style="table-layout:fixed;vertical-align: bottom; font-family: serif;font-size: 7pt; color: #000000;line-height:1;">
            <tr>
                <td width="13%" style="font-weight:bold;vertical-align:top;">Kantor Pusat</td>
                <td width="1%" style="text-align:center;vertical-align:top;">:</td>
                <td style="text-align:justify; word-break:break-word; padding:2px;vertical-align:top;">
                    Komplek Perkantoran Bekasi Mas, Blok B No. 16 Jl. Jend A. Yani - Bekasi 17141 Ph. 021-8846075,
                    8896075, 88961207, 88961208, Fx.021-88957886
                </td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
                <td>Email : ghm-pusat@humanindo.co.id Website : www.humanindo.co.id</td>
            </tr>
            <tr>
                <td style="font-weight:bold;">Kantor Perwakilan</td>
                <td style="text-align:center;">:</td>
                <td>Denpasar - Bali, Jl. Majapahit No. 12 C Pelasa Kuta Badung - Kuta Bali, Telp. 0361 - 750224</td>
            </tr>
            <tr>
                <td style="font-weight:bold;">Kantor Perwakilan</td>
                <td style="text-align:center;">:</td>
                <td>Perum Delta Sari Indah Blok AT-21 Desa Kureksari - Waru - Sidoarjo Jawa Timur - 61257, Telp. 031
                    - 8548315</td>
            </tr>
            <tr>
                <td style="font-weight:bold;">Kantor Perwakilan</td>
                <td style="text-align:center;">:</td>
                <td>Bandara Soekarno Hatta Terminal 2D Lantai 2 Cengkareng - Tangerang - Banten, Telp. 021-5590633
                </td>
            </tr>
        </table>
    </footer>
</body>

</html>
