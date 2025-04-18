<div class="row">
    <div class="col-md-12 col-lg-12">
        <div class="box box-success">
            <!-- /.box-header -->
            <div class="box-body table-responsive">
                <table id="table-datas" class="table table-bordered table-striped table-responsive">
                    <thead>
                        <tr>
                            <th colspan="9">TABEL 1. JUMLAH SASARAN 1.000 HPK (IBU
                                HAMIL DAN ANAK 0-23 BULAN)</th>
                        </tr>
                        <tr>
                            <th width="15%" rowspan="2" colspan="3" class="text-center">Sasaran</th>
                            <th width="45%" rowspan="2" colspan="2" class="text-center">JML TOTAL RUMAH TANGGA
                                1.000 HPK </th>
                            <th width="20%" colspan="2" class="text-center">IBU HAMIL
                            </th>
                            <th width="20%" colspan="2" class="text-center">ANAK 0 –
                                23 BULAN</th>
                        </tr>
                        <tr>
                            <th width="10%" class="text-center">TOTAL</th>
                            <th width="10%" class="text-center">KEK/RESTI</th>
                            <th width="10%" class="text-center">TOTAL</th>
                            <th width="10%" class="text-center">GIZI KURANG/ GIZI
                                BURUK/STUNTING</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-center">Jumlah</th>
                            <td colspan="2" class="text-center">{{ $data['JTRT'] }}
                            </td>
                            <td class="text-center">
                                {{ $data['ibu_hamil']['dataFilter'] == null ? '0' : sizeof($data['ibu_hamil']['dataFilter']) }}
                            </td>
                            <td class="text-center">{{ $data['jumlahKekRisti'] }}</td>
                            <td class="text-center">
                                {{ $data['bulanan_anak']['dataFilter'] == null ? '0' : sizeof($data['bulanan_anak']['dataFilter']) }}
                            </td>
                            <td class="text-center">{{ $data['jumlahGiziBukanNormal'] }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="9">TABEL 2. HASIL PENGUKURAN TIKAR
                                PERTUMBUHAN (DETEKSI DINI STUNTING) </th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-center">Sasaran</th>
                            <th colspan="1" width="22%" class="text-center">JUMLAH
                                TOTAL ANAK USIA 0 – 23 BULAN </th>
                            <th colspan="1" width="23%" class="text-center">HIJAU
                                (NORMAL)</th>
                            <th colspan="2" class="text-center">Kuning (Resiko
                                Stunting)</th>
                            <th colspan="2" class="text-center">Merah Terindikasi
                                Stunting</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-center">Jumlah</th>
                            <td colspan="1" class="text-center">
                                {{ $data['bulanan_anak']['dataFilter'] == null ? '0' : sizeof($data['bulanan_anak']['dataFilter']) }}
                            </td>
                            <td colspan="1" class="text-center">{{ $data['tikar']['H'] }}
                            </td>
                            <td colspan="2" class="text-center">{{ $data['tikar']['K'] }}
                            </td>
                            <td colspan="2" class="text-center">{{ $data['tikar']['M'] }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="9">TABEL 3. KELENGKAPAN KONVERGENSI PAKET
                                LAYANAN PENCEGAHAN STUNTING BAGI 1.000 HPK </th>
                        </tr>
                        <tr>
                            <th colspan="2" class="text-center">Sasaran</th>
                            <th colspan="1" class="text-center">No</th>
                            <th colspan="3" class="text-center">Indikator</th>
                            <th colspan="2" class="text-center">Jumlah</th>
                            <th colspan="1" class="text-center">%</th>
                        </tr>
                        <tr>
                            <th colspan="2" rowspan="8" class="text-center">Ibu Hamil
                            </th>
                            <th colspan="1" class="text-center">1</th>
                            <td colspan="3">Ibu hamil periksa kehamilan paling sedikit 4
                                kali selama kehamilan kehamilan.</td>
                            <td colspan="2" class="text-center">
                                {{ $data['ibu_hamil']['capaianKonvergensi'] == null ? '0' : $data['ibu_hamil']['capaianKonvergensi']['periksa_kehamilan']['Y'] }}
                            </td>
                            <td colspan="1" class="text-center">
                                {{ $data['ibu_hamil']['capaianKonvergensi'] == null ? '0' : $data['ibu_hamil']['capaianKonvergensi']['periksa_kehamilan']['persen'] }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="1" class="text-center">2</th>
                            <td colspan="3">Ibu hamil mendapatkan dan minum 1 tablet
                                tambah darah (pil FE) setiap hari minimal selama 90 hari </td>
                            <td colspan="2" class="text-center">
                                {{ $data['ibu_hamil']['capaianKonvergensi'] == null ? '0' : $data['ibu_hamil']['capaianKonvergensi']['pil_fe']['Y'] }}
                            </td>
                            <td colspan="1" class="text-center">
                                {{ $data['ibu_hamil']['capaianKonvergensi'] == null ? '0' : $data['ibu_hamil']['capaianKonvergensi']['pil_fe']['persen'] }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="1" class="text-center">3</th>
                            <td colspan="3">Ibu bersalin mendapatkan layanan nifas oleh
                                nakes dilaksanakan minimal 3 kali </td>
                            <td colspan="2" class="text-center">
                                {{ $data['ibu_hamil']['capaianKonvergensi'] == null ? '0' : $data['ibu_hamil']['capaianKonvergensi']['pemeriksaan_nifas']['Y'] }}
                            </td>
                            <td colspan="1" class="text-center">
                                {{ $data['ibu_hamil']['capaianKonvergensi'] == null ? '0' : $data['ibu_hamil']['capaianKonvergensi']['pemeriksaan_nifas']['persen'] }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="1" class="text-center">4</th>
                            <td colspan="3">Ibu hamil mengikuti kegiatan konseling gizi
                                atau kelas ibu hamil minimal 4 kali selama kehamilan </td>
                            <td colspan="2" class="text-center">
                                {{ $data['ibu_hamil']['capaianKonvergensi'] == null ? '0' : $data['ibu_hamil']['capaianKonvergensi']['konseling_gizi']['Y'] }}
                            </td>
                            <td colspan="1" class="text-center">
                                {{ $data['ibu_hamil']['capaianKonvergensi'] == null ? '0' : $data['ibu_hamil']['capaianKonvergensi']['konseling_gizi']['persen'] }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="1" class="text-center">5</th>
                            <td colspan="3">Ibu hamil dengan kondisi resiko tinggi
                                dan/atau Kekurangan Energi Kronis (KEK) mendapat kunjungan ke rumah oleh bidan Desa
                                secara terpadu minimal 1 bulan sekali </td>
                            <td colspan="2" class="text-center">
                                {{ $data['ibu_hamil']['capaianKonvergensi'] == null ? '0' : $data['ibu_hamil']['capaianKonvergensi']['kunjungan_rumah']['Y'] }}
                            </td>
                            <td colspan="1" class="text-center">
                                {{ $data['ibu_hamil']['capaianKonvergensi'] == null ? '0' : $data['ibu_hamil']['capaianKonvergensi']['kunjungan_rumah']['persen'] }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="1" class="text-center">6</th>
                            <td colspan="3">Rumah Tangga Ibu hamil memiliki sarana akses
                                air minum yang aman</td>
                            <td colspan="2" class="text-center">
                                {{ $data['ibu_hamil']['capaianKonvergensi'] == null ? '0' : $data['ibu_hamil']['capaianKonvergensi']['akses_air_bersih']['Y'] }}
                            </td>
                            <td colspan="1" class="text-center">
                                {{ $data['ibu_hamil']['capaianKonvergensi'] == null ? '0' : $data['ibu_hamil']['capaianKonvergensi']['akses_air_bersih']['persen'] }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="1" class="text-center">7</th>
                            <td colspan="3">Rumah Tangga Ibu hamil memiliki sarana
                                jamban keluarga yang layak</td>
                            <td colspan="2" class="text-center">
                                {{ $data['ibu_hamil']['capaianKonvergensi'] == null ? '0' : $data['ibu_hamil']['capaianKonvergensi']['kepemilikan_jamban']['Y'] }}
                            </td>
                            <td colspan="1" class="text-center">
                                {{ $data['ibu_hamil']['capaianKonvergensi'] == null ? '0' : $data['ibu_hamil']['capaianKonvergensi']['kepemilikan_jamban']['persen'] }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="1" class="text-center">8</th>
                            <td colspan="3">Ibu hamil memiliki jaminan layanan kesehatan
                            </td>
                            <td colspan="2" class="text-center">
                                {{ $data['ibu_hamil']['capaianKonvergensi'] == null ? '0' : $data['ibu_hamil']['capaianKonvergensi']['jaminan_kesehatan']['Y'] }}
                            </td>
                            <td colspan="1" class="text-center">
                                {{ $data['ibu_hamil']['capaianKonvergensi'] == null ? '0' : $data['ibu_hamil']['capaianKonvergensi']['jaminan_kesehatan']['persen'] }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="2" rowspan="11" class="text-center">Anak 0
                                sd 23 Bulan (0 sd 2 Tahun)</th>
                            <th colspan="1" class="text-center">1</th>
                            <td colspan="3">Bayi usia 12 bulan ke bawah mendapatkan
                                imunisasi dasar lengkap</td>
                            <td colspan="2" class="text-center">
                                {{ $data['bulanan_anak']['capaianKonvergensi'] == null ? '0' : $data['bulanan_anak']['capaianKonvergensi']['imunisasi']['Y'] }}
                            </td>
                            <td colspan="1" class="text-center">
                                {{ $data['bulanan_anak']['capaianKonvergensi'] == null ? '0' : $data['bulanan_anak']['capaianKonvergensi']['imunisasi']['persen'] }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="1" class="text-center">2</th>
                            <td colspan="3">Anak usia 0-23 bulan diukur berat badannya
                                di posyandu secara rutin setiap bulan </td>
                            <td colspan="2" class="text-center">
                                {{ $data['bulanan_anak']['capaianKonvergensi'] == null ? '0' : $data['bulanan_anak']['capaianKonvergensi']['pengukuran_berat_badan']['Y'] }}
                            </td>
                            <td colspan="1" class="text-center">
                                {{ $data['bulanan_anak']['capaianKonvergensi'] == null ? '0' : $data['bulanan_anak']['capaianKonvergensi']['pengukuran_berat_badan']['persen'] }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="1" class="text-center">3</th>
                            <td colspan="3">Anak usia 0-23 bulan diukur panjang/tinggi
                                badannya oleh tenaga kesehatan terlatih minimal 2 kali dalam setahun </td>
                            <td colspan="2" class="text-center">
                                {{ $data['bulanan_anak']['capaianKonvergensi'] == null ? '0' : $data['bulanan_anak']['capaianKonvergensi']['pengukuran_tinggi_badan']['Y'] }}
                            </td>
                            <td colspan="1" class="text-center">
                                {{ $data['bulanan_anak']['capaianKonvergensi'] == null ? '0' : $data['bulanan_anak']['capaianKonvergensi']['pengukuran_tinggi_badan']['persen'] }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="1" rowspan="2" class="text-center">4</th>
                            <td colspan="3" rowspan="2">Orang tua/pengasuh yang memiliki
                                anak usia 0-23 bulan mengikuti kegiatan konseling gizi secara rutin minimal sebulan
                                sekali. </td>
                            <th colspan="1" class="text-center">Laki</th>
                            <th colspan="1" class="text-center">Jumlah</th>
                            <td colspan="1" class="text-center"></td>
                        </tr>
                        <tr>
                            <td colspan="1" class="text-center">0</td>
                            <td colspan="1" class="text-center">0</td>
                            <td colspan="1" class="text-center">0.00</td>
                        </tr>
                        <tr>
                            <th colspan="1" class="text-center">5</th>
                            <td colspan="3">Anak usia 0-23 bulan dengan status gizi
                                buruk, gizi kurang, dan stunting mendapat kunjungan ke rumah secara terpadu minimal 1
                                bulan sekali </td>
                            <td colspan="2" class="text-center">
                                {{ $data['bulanan_anak']['capaianKonvergensi'] == null ? '0' : $data['bulanan_anak']['capaianKonvergensi']['kunjungan_rumah']['Y'] }}
                            </td>
                            <td colspan="1" class="text-center">
                                {{ $data['bulanan_anak']['capaianKonvergensi'] == null ? '0' : $data['bulanan_anak']['capaianKonvergensi']['kunjungan_rumah']['persen'] }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="1" class="text-center">6</th>
                            <td colspan="3">Rumah Tangga anak usia 0-23 bulan memiliki
                                sarana akses air minum yang aman</td>
                            <td colspan="2" class="text-center">
                                {{ $data['bulanan_anak']['capaianKonvergensi'] == null ? '0' : $data['bulanan_anak']['capaianKonvergensi']['air_bersih']['Y'] }}
                            </td>
                            <td colspan="1" class="text-center">
                                {{ $data['bulanan_anak']['capaianKonvergensi'] == null ? '0' : $data['bulanan_anak']['capaianKonvergensi']['air_bersih']['persen'] }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="1" class="text-center">7</th>
                            <td colspan="3">Rumah Tangga anak usia 0-23 bulan memiliki
                                sarana jamban yang layak</td>
                            <td colspan="2" class="text-center">
                                {{ $data['bulanan_anak']['capaianKonvergensi'] == null ? '0' : $data['bulanan_anak']['capaianKonvergensi']['jamban_sehat']['Y'] }}
                            </td>
                            <td colspan="1" class="text-center">
                                {{ $data['bulanan_anak']['capaianKonvergensi'] == null ? '0' : $data['bulanan_anak']['capaianKonvergensi']['jamban_sehat']['persen'] }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="1" class="text-center">8</th>
                            <td colspan="3">Anak usia 0-23 bulan memiliki akte kelahiran
                            </td>
                            <td colspan="2" class="text-center">
                                {{ $data['bulanan_anak']['capaianKonvergensi'] == null ? '0' : $data['bulanan_anak']['capaianKonvergensi']['akta_lahir']['Y'] }}
                            </td>
                            <td colspan="1" class="text-center">
                                {{ $data['bulanan_anak']['capaianKonvergensi'] == null ? '0' : $data['bulanan_anak']['capaianKonvergensi']['akta_lahir']['persen'] }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="1" class="text-center">9</th>
                            <td colspan="3">Anak usia 0-23 bulan memiliki jaminan
                                layanan kesehatan</td>
                            <td colspan="2" class="text-center">
                                {{ $data['bulanan_anak']['capaianKonvergensi'] == null ? '0' : $data['bulanan_anak']['capaianKonvergensi']['jaminan_kesehatan']['Y'] }}
                            </td>
                            <td colspan="1" class="text-center">
                                {{ $data['bulanan_anak']['capaianKonvergensi'] == null ? '0' : $data['bulanan_anak']['capaianKonvergensi']['jaminan_kesehatan']['persen'] }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="1" class="text-center">10</th>
                            <td colspan="3">Orang tua/pengasuh yang memiliki anak usia
                                0-23 bulan mengikuti Kelas Pengasuhan minimal sebulan sekali </td>
                            <td colspan="2" class="text-center">
                                {{ $data['bulanan_anak']['capaianKonvergensi'] == null ? '0' : $data['bulanan_anak']['capaianKonvergensi']['pengasuhan_paud']['Y'] }}
                            </td>
                            <td colspan="1" class="text-center">
                                {{ $data['bulanan_anak']['capaianKonvergensi'] == null ? '0' : $data['bulanan_anak']['capaianKonvergensi']['pengasuhan_paud']['persen'] }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="2" class="text-center">Anak 2 sd 6 Tahun
                            </th>
                            <th width="5%" colspan="1" class="text-center">1</th>
                            <td colspan="3">Anak usia 2-6 tahun terdaftar dan aktif
                                mengikuti kegiatan layanan PAUD</td>
                            <td colspan="2" class="text-center">
                                <?= $data['dataAnak0sd2Tahun']['jumlah'] ?></td>
                            <td colspan="1" class="text-center">
                                <?= $data['dataAnak0sd2Tahun']['persen'] ?></td>
                        </tr>
                        <tr>
                            <th colspan="9">TABEL 4. TINGKAT KONVERGENSI DESA
                            </th>
                        </tr>
                        <tr>
                            <th width="5%" colspan="1" rowspan="2" class="text-center">No</th>
                            <th colspan="3" rowspan="2" class="text-center">SASARAN
                            </th>
                            <th colspan="3" rowspan="1" class="text-center">JUMLAH
                                INDIKATOR</th>
                            <th colspan="2" rowspan="2" class="text-center">TINGKAT
                                KONVERGENSI (%)</th>
                        </tr>
                        <tr>
                            <th colspan="1" rowspan="1" class="text-center">YANG
                                DITERIMA</th>
                            <th colspan="2" rowspan="1" class="text-center">
                                SEHARUSNYA DITERIMA</th>
                        </tr>
                        @php
                            $JLD_IbuHamil =
                                $data['ibu_hamil']['tingkatKonvergensiDesa'] == null
                                    ? '0'
                                    : $data['ibu_hamil']['tingkatKonvergensiDesa']['jumlah_diterima'];
                            $JLD_Anak =
                                $data['bulanan_anak']['tingkatKonvergensiDesa'] == null
                                    ? '0'
                                    : $data['bulanan_anak']['tingkatKonvergensiDesa']['jumlah_diterima'];

                            $JYSD_IbuHamil =
                                $data['ibu_hamil']['tingkatKonvergensiDesa'] == null
                                    ? '0'
                                    : $data['ibu_hamil']['tingkatKonvergensiDesa']['jumlah_seharusnya'];
                            $JYSD_Anak =
                                $data['bulanan_anak']['tingkatKonvergensiDesa'] == null
                                    ? '0'
                                    : $data['bulanan_anak']['tingkatKonvergensiDesa']['jumlah_seharusnya'];

                            $PERSEN_IbuHamil =
                                $data['ibu_hamil']['tingkatKonvergensiDesa'] == null
                                    ? '0'
                                    : $data['ibu_hamil']['tingkatKonvergensiDesa']['persen'];
                            $PERSEN_Anak =
                                $data['bulanan_anak']['tingkatKonvergensiDesa'] == null
                                    ? '0'
                                    : $data['bulanan_anak']['tingkatKonvergensiDesa']['persen'];

                            $JLD_TOTAL = (int) $JLD_IbuHamil + (int) $JLD_Anak;
                            $JYSD_TOTAL = (int) $JYSD_IbuHamil + (int) $JYSD_Anak;

                            if ($JYSD_TOTAL != 0) {
                                $KONV_TOTAL = number_format(($JLD_TOTAL / $JYSD_TOTAL) * 100, 2);
                            } else {
                                $KONV_TOTAL = number_format(0, 2);
                            }

                        @endphp
                        <tr>
                            <th colspan="1" class="text-center">1</th>
                            <td colspan="3">Ibu Hamil</td>
                            <td colspan="1" class="text-center">
                                {{ $JLD_IbuHamil }}</td>
                            <td colspan="2" class="text-center">
                                {{ $JYSD_IbuHamil }}</td>
                            <td colspan="2" class="text-center">
                                {{ $PERSEN_IbuHamil }}</td>
                        </tr>
                        <tr>
                            <th colspan="1" class="text-center">2</th>
                            <td colspan="3">Anak 0 - 23 Bulan</td>
                            <td colspan="1" class="text-center">{{ $JLD_Anak }}
                            </td>
                            <td colspan="2" class="text-center">{{ $JYSD_Anak }}
                            </td>
                            <td colspan="2" class="text-center">
                                {{ $PERSEN_Anak }}</td>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-center">TOTAL TINGKAT
                                KONVERGENSI DESA</th>
                            <td colspan="1" class="text-center">{{ $JLD_TOTAL }}
                            </td>
                            <td colspan="2" class="text-center">{{ $JYSD_TOTAL }}
                            </td>
                            <td colspan="2" class="text-center">{{ $KONV_TOTAL }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="9">TABEL 5. PENGGUNAAN DANA DESA DALAM
                                PENCEGAHAN STUNTING</th>
                        </tr>
                        <tr>
                            <th width="5%" colspan="1" rowspan="2" class="text-center">No</th>
                            <th colspan="3" rowspan="2" class="text-center">
                                BIDANG/KEGIATAN </th>
                            <th colspan="1" rowspan="2" class="text-center">TOTAL
                                ALOKASI DANA</th>
                            <th colspan="4" rowspan="1" class="text-center">KEGIATAN
                                KHUSUS PENCEGAHAN STUNTING</th>
                        </tr>
                        <tr>
                            <th colspan="2" rowspan="1" class="text-center">ALOKASI
                                DANA</th>
                            <th colspan="2" rowspan="1" class="text-center">%
                                (PERSEN) </th>
                        </tr>
                        <tr>
                            <th colspan="1" class="text-center">1</th>
                            <td colspan="3">Bidang Pembangunan Desa</td>
                            <td colspan="1" class="text-center"></td>
                            <td colspan="2" class="text-center"></td>
                            <td colspan="2" class="text-center">%</td>
                        </tr>
                        <tr>
                            <th colspan="1" class="text-center">2</th>
                            <td colspan="3">Bidang Pemberdayaan Masyarakat Desa</td>
                            <td colspan="1" class="text-center"></td>
                            <td colspan="2" class="text-center"></td>
                            <td colspan="2" class="text-center">%</td>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

</div>
