 @php
     use Carbon\Carbon;
     //  Carbon::setLocale('id');

     function badgeClass($status)
     {
         return match ($status) {
             'ON PROGRESS' => 'bg-label-secondary',
             'ON RECRUITMENT' => 'bg-label-warning',
             'ON SEND' => 'bg-label-info',
             'ON USERS' => 'bg-primary',
             'ON HRD' => 'bg-label-success',
             'GAGAL' => 'bg-label-danger',
             default => 'bg-label-secondary',
         };
     }

     function namaStatus($status)
     {
         return match ($status) {
             'ON PROGRESS' => 'WAITING',
             'ON RECRUITMENT' => 'READY',
             'PROCESS' => 'ON PROGRESS',
             'ON SEND' => 'READY TO SEND',
             'ON USERS' => 'ON USER',
             default => '-',
         };
     }

 @endphp

 @forelse ($pemanggilans as $item)
     @php
         $isMine = $item->id_reqsdm == 126;
         $isExpiredPending = $item->status === 'ON PROGRESS' && Carbon::parse($item->tgl_panggil)->lt(Carbon::today());
         $badgeClass = $isExpiredPending ? 'bg-label-danger' : ($isMine ? 'bg-label-secondary' : 'bg-label-warning');

     @endphp

     <div class="col-lg-6 mb-4 tanggalPemanggilan" data-token="{{ $item->token }}" style="cursor: pointer">
         <div
             class="card shadow border card-border-shadow-{{ $isExpiredPending ? 'danger' : (Carbon::parse($item->tgl_panggil)->lessThanOrEqualTo(Carbon::today()) ? 'warning' : 'primary') }}">
             <div class="card-body">
                 <div class="d-flex align-items-center mb-2 pb-1">
                     <div class="avatar me-2">
                         <span
                             class="avatar-initial rounded bg-label-{{ $isExpiredPending ? 'danger' : (Carbon::parse($item->tgl_panggil)->lessThanOrEqualTo(Carbon::today()) ? 'warning' : 'primary') }}">
                             <i
                                 class="{{ $isExpiredPending
                                     ? 'far fa-folder-open text-danger'
                                     : (Carbon::parse($item->tgl_panggil)->lessThanOrEqualTo(Carbon::today()) // Jika tgl_panggil <= tgl sekarang
                                         ? 'far fa-folder-open text-warning'
                                         : 'fas fa-dna') }} ti-sm"></i>
                         </span>
                     </div>
                     <h4
                         class="ms-1 mb-0 {{ $isExpiredPending ? 'text-danger' : (Carbon::parse($item->tgl_panggil)->lessThanOrEqualTo(Carbon::today()) ? 'text-warning' : 'text-primary') }}">
                         {{ $isExpiredPending ? 'EXPIRED' : (Carbon::parse($item->tgl_panggil)->lessThanOrEqualTo(Carbon::today()) ? strtoupper(namaStatus($item->status)) : 'PENDING') }}
                     </h4>
                 </div>
                 <p class="mb-1 text-success">{{ $item->nm_bagian }}</p>
                 <p class="mb-1 fw-bold {{ $isExpiredPending ? 'text-danger' : '' }}">
                     {{ strtoupper(Carbon::parse($item->tgl_panggil)->translatedFormat('l, d F Y')) }}
                 </p>
                 <p class="mb-0">
                     <span class="fw-medium text-muted">Total {{ $item->total }}
                         <small class="text-muted">Pelamar</small>
                     </span>
                 </p>
             </div>
         </div>
     </div>

     {{-- <li class="mb-4 pb-1 tanggalPemanggilan border-bottom border-black rounded-3 p-2" style="cursor: pointer"
         data-token="{{ $item->token }}">
         <div class="d-flex align-items-start ">
             <div class="badge {{ $badgeClass }} p-2 me-3 rounded">
                 <i
                     class="{{ $isExpiredPending
                         ? 'far fa-folder-open text-danger'
                         : (Carbon::parse($item->tgl_panggil)->lessThanOrEqualTo(Carbon::today()) // Jika tgl_panggil <= tgl sekarang
                             ? 'far fa-folder-open text-warning'
                             : 'ti ti-shadow') }} ti-sm"></i>
             </div>

             <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                 <div class="me-2">
                     <small class="mb-0 {{ $isExpiredPending ? 'text-danger' : ($isMine ? '' : 'text-warning') }}">
                         {{ Carbon::parse($item->tgl_panggil)->translatedFormat('l, d-M-Y') }}
                     </small>
                     <br>
                     <small class="text-muted">{{ $item->total }} pelamar</small>
                     <small class="{{ $isExpiredPending ? 'text-danger' : ($isMine ? 'text-muted' : 'text-warning') }}">
                         {{ $isMine ? '' : ': by ' . ucwords(strtolower($item->user->name)) . '' }}
                     </small>
                 </div>
                 <div class="d-flex align-items-center">
                     <div class="ms-3 badge {{ badgeClass($item->status) }}">
                         {{ strtoupper(namaStatus($item->status)) }}</div>
                 </div>
             </div>
         </div>
     </li> --}}

 @empty
     <div class="col-lg-12 mb-4">
         <div class="card card-border-shadow-primary">
             <div class="card-body">
                 <div class="d-flex align-items-center mb-2 pb-1">
                     <div class="avatar me-2">
                         <span class="avatar-initial rounded bg-label-primary"><i class="ti ti-search ti-md"></i></span>
                     </div>
                     <h4 class="ms-1 mb-0">Oops! Data tidak ditemukan</h4>
                 </div>
                 {{-- <p class="mb-1">empty</p>
                 <p class="mb-0">
                     <span class="fw-medium me-1">0</span>
                     <small class="text-muted">pelamar</small>
                 </p> --}}
             </div>
         </div>

         {{-- <li class="mb-4 pb-1">
             <div class="d-flex align-items-start ">
                 <div class="badge bg-label-danger p-2 me-3 rounded">
                     <i class="fas fa-ban ti-sm"></i>
                 </div>
                 <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                     <div class="me-2">
                         <h6 class="mb-0">
                             Oops! Data tidak ditemukan</h6>
                         <small class="text-muted">Total 0 pelamar</small>
                     </div>
                 </div>
             </div>
         </li> --}}
 @endforelse
