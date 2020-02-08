@extends('layouts.app')
@section('title')
Detail User - {{ config('app.name') }}
@endsection
@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-5 mb-3">
                <div class="card shadow h-100">
                    <div class="card-header">
                        <h5 class="m-0 pt-1 font-weight-bold float-left">Detail User</h5>
                        <a href="{{ route('users.index') }}" class="btn btn-sm btn-secondary float-right">Kembali</a>
                    </div>
                    <div class="card-body">
                        <img src="{{ Storage::url($user->foto) }}" class="card-img mb-3" alt="{{ $user->foto }}">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tbody>
                                    <tr><td>NRP</td><td>: {{ $user->nrp }}</td></tr>
                                    <tr><td>Nama</td><td>: {{ $user->nama }}</td></tr>
                                    <tr><td>Sebagai</td><td>: {{ $user->role->role }}</td></tr>
                                </tbody>
                            </table>
                            <div class="float-right">
                                <a href="{{ route('users.edit',$user) }}" class="btn btn-warning">Ubah</a>
                                @if ($user->id != auth()->user()->id)
                                    <form class="d-inline-block" action="{{ route('users.destroy',$user) }}" method="post">
                                        @csrf @method('delete')
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah anda yakin ingin menghapus user ini ???')">Hapus</button>
                                    </form>
                                @endif
                                <form class="d-inline-block" action="{{ route('users.password',$user) }}" method="post">
                                    @csrf @method('patch')
                                    <button type="submit" class="btn btn-dark" onclick="return confirm('Apakah anda yakin ingin mereset password user ini ???')">Reset Password</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-7 mb-3">
                <div class="card shadow h-100">
                    <div class="card-header">
                        <h5 class="m-0 pt-1 font-weight-bold float-left">Kehadiran</h5>
                        <button type="button" class="btn btn-sm btn-success float-right" data-toggle="modal" data-target="#kehadiran">
                            Tambah Kehadiran
                        </button>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('kehadiran.cari', ['user' => $user]) }}" class="mb-3" method="get">
                            <div class="input-group mb-3">
                                <input type="month" class="form-control" name="bulan" id="bulan" value="{{ request('bulan') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="submit">Cari</button>
                                </div>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Keterangan</th>
                                        <th>Jam Masuk</th>
                                        <th>Jam Keluar</th>
                                        <th>Total Jam</th>
                                        <th>Opsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($presents as $present)
                                        <tr>
                                            <td>{{ date('d/m/Y', strtotime($present->tanggal)) }}</td>
                                            <td>{{ $present->keterangan }}</td>
                                            @if ($present->jam_masuk)
                                                <td>{{ date('H:i', strtotime($present->jam_masuk)) }}</td>
                                            @else
                                                <td>-</td>
                                            @endif
                                            @if($present->jam_keluar)
                                                <td>{{ date('H:i', strtotime($present->jam_keluar)) }}</td>
                                                <td>
                                                    @if (strtotime($present->jam_keluar) <= strtotime($present->jam_masuk))
                                                        {{ 24 - (\Carbon\Carbon::parse($present->jam_masuk)->diffInHours(\Carbon\Carbon::parse($present->jam_keluar))) }}
                                                    @else 
                                                        {{ \Carbon\Carbon::parse($present->jam_masuk)->diffInHours(\Carbon\Carbon::parse($present->jam_keluar)) }}
                                                    @endif
                                                </td>
                                            @else
                                                <td>-</td>
                                                <td>-</td>
                                            @endif
                                            <td>
                                                <button id="btnUbahKehadiran" data-id="{{ $present->id }}" type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#ubahKehadiran">
                                                    <i class="far fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="float-right">
                                {{ $presents->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>    

    <!-- Modal -->
    <div class="modal fade" id="kehadiran" tabindex="-1" role="dialog" aria-labelledby="kehadiranLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="kehadiranLabel">Tambah Kehadiran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('kehadiran.store') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <h5 class="mb-3">{{ date('l, d F Y') }}</h5>
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                        <div class="form-group row">
                            <label for="keterangan" class="col-form-label col-sm-3">Keterangan</label>
                            <div class="col-sm-9">
                                <select class="form-control @error('keterangan') is-invalid @enderror" name="keterangan" id="keterangan">
                                    <option value="Alpha" {{ old('keterangan') == 'Alpha' ? 'selected':'' }}>Alpha</option>
                                    <option value="Masuk" {{ old('keterangan') == 'Masuk' ? 'selected':'' }}>Masuk</option>
                                    <option value="Telat" {{ old('keterangan') == 'Telat' ? 'selected':'' }}>Telat</option>
                                    <option value="Sakit" {{ old('keterangan') == 'Sakit' ? 'selected':'' }}>Sakit</option>
                                    <option value="Cuti" {{ old('keterangan') == 'Cuti' ? 'selected':'' }}>Cuti</option>
                                </select>
                                @error('keterangan') <span class="invalid-feedback" role="alert">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="form-group row" id="jamMasuk">
                            <label for="jam_masuk" class="col-form-label col-sm-3">Jam Masuk</label>
                            <div class="col-sm-9">
                                <input type="time" name="jam_masuk" id="jam_masuk" class="form-control @error('jam_masuk') is-invalid @enderror">
                                @error('jam_masuk') <span class="invalid-feedback" role="alert">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="ubahKehadiran" tabindex="-1" role="dialog" aria-labelledby="ubahKehadiranLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ubahKehadiranLabel">Ubah Kehadiran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formUbahKehadiran" action="" method="post">
                    @csrf @method('patch')
                    <div class="modal-body">
                        <h5 class="mb-3" id="tanggal"></h5>
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                        <div class="form-group row">
                            <label for="ubah_keterangan" class="col-form-label col-sm-3">Keterangan</label>
                            <div class="col-sm-9">
                                <select class="form-control @error('keterangan') is-invalid @enderror" name="keterangan" id="ubah_keterangan">
                                    <option value="Alpha" {{ old('keterangan') == 'Alpha' ? 'selected':'' }}>Alpha</option>
                                    <option value="Masuk" {{ old('keterangan') == 'Masuk' ? 'selected':'' }}>Masuk</option>
                                    <option value="Telat" {{ old('keterangan') == 'Telat' ? 'selected':'' }}>Telat</option>
                                    <option value="Sakit" {{ old('keterangan') == 'Sakit' ? 'selected':'' }}>Sakit</option>
                                    <option value="Cuti" {{ old('keterangan') == 'Cuti' ? 'selected':'' }}>Cuti</option>
                                </select>
                                @error('keterangan') <span class="invalid-feedback" role="alert">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="form-group row" id="jamMasuk">
                            <label for="ubah_jam_masuk" class="col-form-label col-sm-3">Jam Masuk</label>
                            <div class="col-sm-9">
                                <input type="time" name="jam_masuk" id="ubah_jam_masuk" class="form-control @error('jam_masuk') is-invalid @enderror">
                                @error('jam_masuk') <span class="invalid-feedback" role="alert">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="form-group row" id="jamKeluar">
                            <label for="ubah_jam_keluar" class="col-form-label col-sm-3">Jam Keluar</label>
                            <div class="col-sm-9">
                                <input type="time" name="jam_keluar" id="ubah_jam_keluar" class="form-control @error('jam_keluar') is-invalid @enderror">
                                @error('jam_keluar') <span class="invalid-feedback" role="alert">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function(){
            $('#jamMasuk').hide();
            $('#keterangan').on('change',function(){
                if ($(this).val() == 'Masuk' || $(this).val() == 'Telat') {
                    $('#jamMasuk').show();
                } else {
                    $('#jamMasuk').hide();
                }
            });
            $('#btnUbahKehadiran').on('click',function(){
                const CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                const id = $(this).data('id');
                $('#formUbahKehadiran').attr('action', "{{ url('kehadiran') }}/" + id);
                $.ajax({
                    url: "{{ route('ajax.get.kehadiran') }}",
                    method: 'post',
                    dataType: 'json',
                    data: {
                        _token: CSRF_TOKEN,
                        id: id
                    },
                    success: function (data) {
                        var date = new Date(data.tanggal);
                        var tahun = date.getFullYear();
                        var bulan = date.getMonth();
                        var tanggal = date.getDate();
                        var hari = date.getDay();
                        var jam = date.getHours();
                        var menit = date.getMinutes();
                        var detik = date.getSeconds();
                        switch(hari) {
                            case 0: hari = "Minggu"; break;
                            case 1: hari = "Senin"; break;
                            case 2: hari = "Selasa"; break;
                            case 3: hari = "Rabu"; break;
                            case 4: hari = "Kamis"; break;
                            case 5: hari = "Jum'at"; break;
                            case 6: hari = "Sabtu"; break;
                        }
                        switch(bulan) {
                            case 0: bulan = "Januari"; break;
                            case 1: bulan = "Februari"; break;
                            case 2: bulan = "Maret"; break;
                            case 3: bulan = "April"; break;
                            case 4: bulan = "Mei"; break;
                            case 5: bulan = "Juni"; break;
                            case 6: bulan = "Juli"; break;
                            case 7: bulan = "Agustus"; break;
                            case 8: bulan = "September"; break;
                            case 9: bulan = "Oktober"; break;
                            case 10: bulan = "November"; break;
                            case 11: bulan = "Desember"; break;
                        }
                        $('#tanggal').html(hari +", "+ tanggal +" "+ bulan +" "+ tahun);
                        $('#ubah_keterangan').val(data.keterangan);
                        $('#ubah_jam_masuk').val(data.jam_masuk);
                        $('#ubah_jam_keluar').val(data.jam_keluar);
                    }
                });
            });
        });
    </script>
@endpush