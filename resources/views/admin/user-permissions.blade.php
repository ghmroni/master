@extends('template.core')

@push('extra_style')
    <link rel="stylesheet" href="{{ asset('assets/css/ribbon-page.css') }}">
    <style>
        .sticky-element {
            position: sticky;
            top: 80px;
            z-index: 8;

            .window-scrolled & {
                inset-block-start: 0;
            }

            .layout-navbar-fixed & {
                inset-block-start: calc($navbar-height - .4375rem);
            }

            .layout-menu-fixed .layout-horizontal & {
                inset-block-start: calc($navbar-height * 2 - .25rem);
            }

            .layout-menu-fixed.layout-navbar-fixed .layout-horizontal & {
                inset-block-start: $navbar-height;
            }
        }
    </style>
@endpush

@section('content')
    <form id="formPerm">
        @csrf

        <div class="card-header sticky-element bg-light p-3 shadow">

            <div class="row col-md-12 ">
                <div class="input-group">
                    <input type="hidden" id="iduser" name="iduser" class="form-control" readonly
                        value="{{ $user->id }}">
                    <input type="text" id="name" name="name" class="form-control text-uppercase"
                        placeholder="Nama Role / Nama Level" required autocomplete="off" value="{{ $user->name }}"
                        readonly>
                    <button class="btn btn-info" type="submit"><i class="fas fa-save me-1"></i>Simpan</button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                @foreach ($permissions as $group => $items)
                    <div class="col-md-12 mb-4">
                        <div class="ribbon-wrapper card shadow border card-border-shadow-warning">
                            <div class="ribbon ribbon-bookmark ribbon-primary">
                                <i class="fas fa-key me-1"></i>
                                {{ strtoupper($group) }}
                            </div>

                            <div class="col-md-4">
                                <label class="form-check-label text-warning mb-2">
                                    <input class="form-check-input selectAll"
                                        type="checkbox"data-group="{{ $group }}">
                                    pilih semua
                                </label>
                            </div>

                            <div class="row">
                                @foreach ($items as $key => $perm)
                                    {{-- @dd($directPermissions[$key] . ' - ' . $perm->id) --}}
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check-warning me-3 me-lg-3">
                                            <label class="form-check-label">
                                                <input class="form-check-input perm-checkbox" type="checkbox"
                                                    name="permissions[]" value="{{ $perm->id }}"
                                                    id="perm_{{ $perm->id }}" data-group="{{ $group }}"
                                                    @if (in_array($perm->id, $rolePermissions)) checked @endif
                                                    @if (in_array($perm->id, $directPermissions)) checked @endif>
                                                {{ ucwords($perm->name) }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>

    </form>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/users/user-perms.js') }}"></script>
@endpush
