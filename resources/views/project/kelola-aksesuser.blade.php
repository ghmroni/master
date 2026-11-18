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
    <form id="formAkses">
        @csrf

        <div class="card-header sticky-element bg-light p-3 shadow">

            <div class="row col-md-12 ">
                <div class="input-group">
                    <input type="hidden" id="userid" name="userid" class="form-control" readonly
                        value="{{ $userId ?? null }}">
                    <input type="text" id="name" name="name" class="form-control text-uppercase"
                        placeholder="Nama Pengguna" required autocomplete="off" value="{{ $users->name ?? null }}" readonly>
                    <button class="btn btn-primary" type="submit"><i class="ti ti-lock me-1"></i>Simpan</button>
                </div>


            </div>

        </div>

        <div class="card-body">

            <div id="resultPermission" class="mt-3">
                @include('project._listproject', ['projects' => $projects])
            </div>
        </div>

    </form>
@endsection

@push('extra_script')
    <script src="{{ asset('assets/customjs/project/kelola-aksesproject.js') }}"></script>
@endpush
