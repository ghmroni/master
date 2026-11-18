 @php
     use Carbon\Carbon;
     //  Carbon::setLocale('id');

     function badgeClass($status)
     {
         return match ($status) {
             'PENDING' => 'bg-label-secondary',
             'PROSES' => 'bg-label-warning',
             'USER' => 'bg-label-info',
             'HRD' => 'bg-primary',
             'DITERIMA' => 'bg-label-success',
             'GAGAL' => 'bg-label-danger',
             default => 'bg-label-secondary',
         };
     }

 @endphp

 @forelse ($pemanggilans as $item)
     @php
         $isMine = $item->user_id == auth()->user()->id;
         $isExpiredPending = $item->status === 'PENDING' && Carbon::parse($item->tgl_panggil)->lt(Carbon::today());
         $badgeClass = $isExpiredPending ? 'bg-label-danger' : ($isMine ? 'bg-label-secondary' : 'bg-label-warning');

     @endphp

     <li class="mb-4 pb-1 tanggalPemanggilan border-bottom border-black rounded-3 p-2" style="cursor: pointer"
         data-tanggal="{{ $item->tgl_panggil }}" data-user="{{ $item->user_id }}" data-status="{{ $item->status }}">
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
                     <div class="ms-3 badge {{ badgeClass($item->status) }}">{{ $item->status }}</div>
                 </div>
             </div>
         </div>
     </li>

 @empty
     <li class="mb-4 pb-1">
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
                 {{-- <div class="d-flex align-items-center">
                     <div class="ms-3 badge bg-label-secondary">Empty
                     </div>
                 </div> --}}
             </div>
         </div>
     </li>
 @endforelse
